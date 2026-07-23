<?php
$log = __DIR__ . '/../storage/logs/laravel.log';
$lines = file($log);
// Get last 40 lines
$last = array_slice($lines, -40);
echo implode('', $last);
