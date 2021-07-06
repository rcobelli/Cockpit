<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(-1);

include 'apns.php';

if ('POST' !== $_SERVER['REQUEST_METHOD']) {
    http_response_code(405);
    die;
}

require 'vendor/autoload.php';

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;

// Validate the message and log errors if invalid.
try {
    // Instantiate the Message and Validator
    $message = Message::fromRawPostData();
    $validator = new MessageValidator();

   $validator->validate($message);

    // Check the type of the message and handle the subscription.
    if ($message['Type'] === 'SubscriptionConfirmation') {
        // Confirm the subscription by sending a GET request to the SubscribeURL
        file_get_contents($message['SubscribeURL']);
    } else if ($message['Type'] === 'Notification') {
        $messageBody = json_decode($message['Message']);

        if ($messageBody['detail-type'] === 'CodePipeline Pipeline Execution State Change') {
            $message = $messageBody->detail->pipeline . " " . $messageBody->detail->state;

            if ($messageBody->detail->state == "FAILED") {
                $message = "🚨🚨🚨 " . $message . " 🚨🚨🚨";
            }

            sendPush($message);
        } else if ($messageBody['detail-type'] === 'CloudWatch Alarm State Change') {
            $message = $messageBody->detail->alarmName . " is now " . $messageBody->detail->state->value;

            sendPush($message);
        } else {
            error_log("Found message-detail type " . $messageBody['detail-type']);
            sendPush($message['Message']);
        }
    } else {
        error_log("Found message type " . $message['Type']);
        sendPush($message['Message']);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    sendPush($_POST['message']);
}


