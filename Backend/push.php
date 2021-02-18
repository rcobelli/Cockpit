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
use Aws\Sns\Exception\InvalidSnsMessageException;


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
    }

    if ($message['Type'] === 'Notification') {
        $messageBody = json_decode($message['Message']);

        if (empty($messageBody->detail->pipeline)) {
            die();
        }
        sendPush($messageBody->detail->pipeline . " " . $messageBody->detail->state);
    }
} catch (Exception $e) {
    sendPush($_POST['message']);
}


