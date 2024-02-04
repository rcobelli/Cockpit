<?php

use Rybel\backbone\Helper;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Minishlink\WebPush\WebPush;

class PushHelper extends Helper
{
    function send($message, $subscription) {
        $auth = array(
            'VAPID' => array(
                'subject' => $this->config['vapidMailTo'],
                'publicKey' => $this->config['vapidPublicKey'],
                'privateKey' => $this->config['vapidPrivateKey']
            ),
        );
    
        $webPush = new WebPush($auth);
    
        $report = $webPush->sendOneNotification(
            $subscription,
            '{"message":"' . $message . '"}',
        );
    
        // handle eventual errors here, and remove the subscription from your server if it is expired
        $endpoint = $report->getRequest()->getUri()->__toString();
    
        if ($report->isSuccess()) {
            $this->log->logMessage("{$message} sent successfully for subscription {$endpoint}.");
        } else {
            $this->log->logError("Message failed to sent for subscription {$endpoint}: {$report->getReason()}");
        }
    }

    public function parse(): string|false
    {
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
                $this->log->logMessage("Confirming subscription to AWS");
                return false;
            } else if ($message['Type'] === 'Notification') {
                $messageBody = json_decode($message['Message']);

                if ($messageBody->{'detail-type'} === 'CodePipeline Pipeline Execution State Change') {
                    $message = $messageBody->detail->pipeline . " " . $messageBody->detail->state;

                    if ($messageBody->detail->state == "FAILED") {
                        $message = "🚨🚨🚨 " . $message . " 🚨🚨🚨";
                    }

                    return $message;
                } else if ($messageBody->{'detail-type'} === 'CloudWatch Alarm State Change') {
                    $message = $messageBody->detail->alarmName . " is now " . $messageBody->detail->state->value;

                    if ($messageBody->detail->state == "ALARM") {
                        $message = "🚨🚨🚨 " . $message . " 🚨🚨🚨";
                    }

                    return $message;
                } else {
                    error_log("Found message-detail type " . $messageBody->{'detail-type'});
                    return $message['Message'];
                }
            } else {
                error_log("Found message type " . $message['Type']);
                return $message['Message'];
            }
        } catch (Exception $e) {
            // Likely it's not an SNS specific message
            $this->log->logMessage($e->getMessage());

            // Try standard POST forms
            if (isset($_POST['message'])) {
                return $_POST['message'];
            }

            // Try the POST body
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['message'])) {
                return $data['message'];
            }

            // Give up
            return false;
        }
    }
}