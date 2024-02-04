<?php

include '../init.php';

if (!$samlHelper->isLoggedIn()) {
    header("Location: index.php");
    die();
}

$config['type'] = Rybel\backbone\LogStream::api;

$helper = new SubscriptionHelper($config);

$subscription = file_get_contents('php://input');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // create a new subscription entry in your database (endpoint is unique)
        $helper->addSubscription($subscription);
        break;
    case 'PUT':
        // update the key and token of subscription corresponding to the endpoint
        $helper->updateSubscription($subscription);
        break;
    case 'DELETE':
        // delete the subscription corresponding to the endpoint
        $helper->deleteSubscription($subscription);
        break;
    default:
        echo "Error: method not handled";
        return;
}