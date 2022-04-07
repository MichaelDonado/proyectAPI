<?php
header('Content-Type: application/json');

$time = time();
echo "Time: $time".PHP_EOL."Hash: ".sha1($argv[1].$time.'sh!! no se lo cuentes a nadie!').PHP_EOL;