# Cockpit 🚁
##### An app that alerts you to the status of all your AWS Pipelines

![Screenshot](screenshot.jpg)

## How it works
You'll receive a push notification on your favorite iDevice every time an AWS Pipeline starts, succeeds or fails

## Installation
1. Go to https://developer.apple.com/account/resources/authkeys/list (or https://developer.apple.com > Account > Certificates, Identifiers & Profiles > Keys)
2. Create a new Key with APNS enabled
3. Save this file to the `Backend` directory as `apns.p8`
4. Update the URL where `Backend` will be hosted in `AppDelegate.swift` in the `iOS` directory
5. Build and install the iOS app in the `iOS` directory to your device
6. Update the config variables at the top of `apns.php`
  - `keyfile` shouldn't change
  - `keyid` is the Key ID from the Apple Developer console
  - `teamid` can be found in the top right corner of the Apple Developer console
  - `bundleid` is whatever you change the iOS app bundle ID to
  - `url` shouldn't change
7. Deploy the `Backend` directory to a PHP capable server
8. In the AWS Management Console, go to Simple Notification Service
9. Create a new Topic (call it `PipelineNotifications` for example)
10. Create a new Subscription (type: HTTPS, URL: https://your-backend-deployment/push.php)
11. It should auto-confirm the subscription, if it doesn't then you'll want to retry the subscription with `Request Confirmation`
12. Go to EventBridge
13. Create a new rule
14. Specify the event pattern using `pattern.json`
15. Specify the target as your SNS topic from step 10

Now any pipeline on this AWS account will trigger a push notification to your device whenever the pipeline starts, succeeds or fails.
