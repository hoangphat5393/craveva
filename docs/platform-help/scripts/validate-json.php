<?php

$path = dirname(__DIR__) . '/_route_dump.json';
$j = file_get_contents($path);
echo 'len=' . strlen($j) . PHP_EOL;
echo 'head=' . substr($j, 0, 100) . PHP_EOL;
$d = json_decode($j, true);
echo json_last_error_msg() . PHP_EOL;
echo is_array($d) ? 'count=' . count($d) : 'fail';
