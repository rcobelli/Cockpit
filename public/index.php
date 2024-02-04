<?php

include '../init.php';

$samlHelper->processSamlInput();

if (!$samlHelper->isLoggedIn()) {
    header("Location: ?sso");
    die();
}

$config['type'] = Rybel\backbone\LogStream::console;
?>

<html>
<body>
    <h1>Cockpit Management</h1>
    <button id="push-subscription-button">Push notifications !</button>
    <button id="send-push-button">Send a test notification</button>
    <script type="text/javascript" src="app.js"></script>
</body>
</html>