<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(-1);

if ('POST' !== $_SERVER['REQUEST_METHOD']) {
    http_response_code(405);
    die;
}

require '../vendor/autoload.php';

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

        if ($messageBody->{'detail-type'} === 'CodePipeline Pipeline Execution State Change') {
            $message = $messageBody->detail->pipeline . " " . $messageBody->detail->state;

            if ($messageBody->detail->state == "FAILED") {
                $message = "🚨🚨🚨 " . $message . " 🚨🚨🚨";
            }

            sendPush($message);
        } else if ($messageBody->{'detail-type'} === 'CloudWatch Alarm State Change') {
            $message = $messageBody->detail->alarmName . " is now " . $messageBody->detail->state->value;

            if ($messageBody->detail->state == "ALARM") {
                $message = "🚨🚨🚨 " . $message . " 🚨🚨🚨";
            }

            sendPush($message);
        } else {
            error_log("Found message-detail type " . $messageBody->{'detail-type'});
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

function sendPush($message) {
    $title = "Cockpit";
	$url = "https://dev.rybel-llc.com";

	$apiKey = "";

	$curlUrl = "https://api.pushalert.co/rest/v1/send";

	//POST variables
	$post_vars = array(
		"title" => $title,
		"message" => $message,
		"url" => $url
	);

	$headers = Array();
	$headers[] = "Authorization: api_key=".$apiKey;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $curlUrl);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_vars));
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);

	$output = json_decode($result, true);
	if($output["success"]) {
		echo $output["id"]; //Sent Notification ID
	}
	else {
		echo $output; //Others like bad request
	}
}