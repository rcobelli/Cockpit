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

// Validate the message and log errors if invalid.
try {
   $validator->validate($message);
} catch (InvalidSnsMessageException $e) {
   // Pretend we're not here if the message is invalid.
   http_response_code(404);
   error_log('SNS Message Validation Error: ' . $e->getMessage());
   die();
}

error_log(print_r($message), 1, "ryan.cobelli@gmail.com");

// Check the type of the message and handle the subscription.
if ($message['Type'] === 'SubscriptionConfirmation') {
   // Confirm the subscription by sending a GET request to the SubscribeURL
   file_get_contents($message['SubscribeURL']);
}

if ($message['Type'] === 'Notification') {

   // Do whatever you want with the message body and data.
   $keyfile = 'apns.p8';               # <- Your AuthKey file
   $keyid = '2HCVYDF3YZ';                            # <- Your Key ID
   $teamid = 'AL6H9GEC6N';                           # <- Your Team ID (see Developer Portal)
   $bundleid = 'com.rybel-llc.cockpit';                # <- Your Bundle ID
   $url = 'https://api.development.push.apple.com';  # <- development url, or use http://api.push.apple.com for production environment
   $token = '08a59369c68c75cf70454faba416326e8f764da47d29b83e3d6649158f760de3';              # <- Device Token

   $message = '{"aps":{"alert":"' . $message['Message'] . '","sound":"default"}}';

   $key = openssl_pkey_get_private('file://'.$keyfile);

   $header = ['alg'=>'ES256','kid'=>$keyid];
   $claims = ['iss'=>$teamid,'iat'=>time()];

   $header_encoded = base64($header);
   $claims_encoded = base64($claims);

   $signature = '';
   openssl_sign($header_encoded . '.' . $claims_encoded, $signature, $key, 'sha256');
   $jwt = $header_encoded . '.' . $claims_encoded . '.' . base64_encode($signature);

   // only needed for PHP prior to 5.5.24
   if (!defined('CURL_HTTP_VERSION_2_0')) {
       define('CURL_HTTP_VERSION_2_0', 3);
   }

   $http2ch = curl_init();
   curl_setopt_array($http2ch, array(
     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
     CURLOPT_URL => "$url/3/device/$token",
     CURLOPT_PORT => 443,
     CURLOPT_HTTPHEADER => array(
       "apns-topic: {$bundleid}",
       "authorization: bearer $jwt"
     ),
     CURLOPT_POST => TRUE,
     CURLOPT_POSTFIELDS => $message,
     CURLOPT_RETURNTRANSFER => TRUE,
     CURLOPT_TIMEOUT => 30,
     CURLOPT_HEADER => 1
   ));

   $result = curl_exec($http2ch);
   if ($result === FALSE) {
     throw new Exception("Curl failed: ".curl_error($http2ch));
   }

   $status = curl_getinfo($http2ch, CURLINFO_HTTP_CODE);
   echo $status;

   function base64($data) {
     return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
   }
}
