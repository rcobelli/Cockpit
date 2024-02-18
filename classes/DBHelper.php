<?php

use Rybel\backbone\Helper;

class DBHelper extends Helper
{
    function save($message) {
        $this->query("INSERT INTO `messages` (message) VALUES (?)", $message);
    }

    function getMessages() {
        return $this->query("SELECT * FROM `messages` ORDER BY `timestamp` DESC LIMIT 9");
    }
}