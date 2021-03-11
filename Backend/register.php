<?php

if ($_COOKIE['debug'] == 'true') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(-1);
} else {
    error_reporting(0);
}

$file = fopen("token.txt", "w");
fwrite($file, $_GET['token']);
fclose($file);
