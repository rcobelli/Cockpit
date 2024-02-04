<?php

use Rybel\backbone\Helper;
use Minishlink\WebPush\Subscription;

class SubscriptionHelper extends Helper
{
    public function getSubscriptions()
    {
        $rawData = $this->query("SELECT body FROM subscriptions");
        $output = array();
        foreach ($rawData as $sub) {
            $parsed = json_decode($sub['body'], true);
            $subscription = new Subscription($parsed['endpoint'], $parsed['publicKey'], $parsed['authToken'], $parsed['contentEncoding']);
            array_push($output, $subscription);
        }
        return $output;
    }

    public function addSubscription($input)
    {
        $subscription = json_decode($input, true);

        return $this->query("INSERT INTO subscriptions (endpoint, body) VALUES (?, ?)", $subscription['endpoint'], $input);
    }

    public function updateSubscription($input)
    {
        $subscription = json_decode($input, true);

        return $this->query("UPDATE subscriptions SET body = ? WHERE endpoint = ?", $input, $subscription['endpoint']);
    }

    public function deleteSubscription($input)
    {
        $subscription = json_decode($input, true);

        return $this->query("DELETE FROM subscriptions WHERE endpoint = ?", $subscription['endpoint']);
    }
}