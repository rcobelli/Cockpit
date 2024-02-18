<?php

include '../init.php';

$samlHelper->processSamlInput();

if (!$samlHelper->isLoggedIn()) {
    header("Location: ?sso");
    die();
}

$config['type'] = Rybel\backbone\LogStream::console;

$dbHelper = new DBHelper($config);
?>

<html>

<head>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Open+Sans&display=swap');

        body {
            font-family: "Open Sans", sans-serif;
        }

        table td+td {
            padding-left: 5px;
        }
    </style>
    <link rel="manifest" href="manifest.json" />
</head>

<body>
    <h1>Cockpit</h1>
    <h2>Notifications Management</h2>
    <button id="push-subscription-button">Push notifications !</button>
    <button id="send-push-button">Send a test notification</button>
    <script type="text/javascript" src="app.js"></script>
    <hr />
    <h2>Recent Notifications</h2>
    <table>
        <tr>
            <th>Timestamp</th>
            <th>Message</th>
        </tr>
        <?php
        $messages = $dbHelper->getMessages();
        foreach ($messages as $message) {
            echo "<tr><td>" . $message['timestamp'] . "</td><td>" . $message['message'] . "</td></tr>";
        }
        ?>
    </table>
</body>

</html>