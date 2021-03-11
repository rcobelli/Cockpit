<?php

$file = fopen("token.txt", "w");
fwrite($file, $_GET['token']);
fclose($file);
