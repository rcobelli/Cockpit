<?php

include '../init.php';

if ('POST' !== $_SERVER['REQUEST_METHOD']) {
    http_response_code(405);
    die;
}


$config['type'] = Rybel\backbone\LogStream::api;

$pushHelper = new PushHelper($config);
$dbHelper = new DBHelper($config);
$subscriptionHelper = new SubscriptionHelper($config);

$message = $pushHelper->parse();
if ($message !== false) {
    $dbHelper->save($message);
    $subscriptions = $subscriptionHelper->getSubscriptions();
    foreach ($subscriptions as $sub) {
        $pushHelper->send($message, $sub);
    }
}
