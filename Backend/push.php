<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(-1);

if ('POST' !== $_SERVER['REQUEST_METHOD']) {
    http_response_code(405);
    die;
}

require 'vendor/autoload.php';

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Aws\Sns\Exception\InvalidSnsMessageException;

// Instantiate the Message and Validator
$message = Message::fromRawPostData();
$validator = new MessageValidator();

error_log("Alpha",0);

// Validate the message and log errors if invalid.
try {
   $validator->validate($message);
} catch (InvalidSnsMessageException $e) {
   // Pretend we're not here if the message is invalid.
   http_response_code(404);
   error_log('SNS Message Validation Error: ' . $e->getMessage(), 0);
   die();
}
error_log("Bravo",0);


// Check the type of the message and handle the subscription.
if ($message['Type'] === 'SubscriptionConfirmation') {
   // Confirm the subscription by sending a GET request to the SubscribeURL
   file_get_contents($message['SubscribeURL']);
}

error_log("Charlie",0);


error_log($message,0);
error_log($message['Message'],0);

if ($message['Type'] === 'Notification') {
    $messageBody = $message['Message'];

    include 'apns.php';
}
