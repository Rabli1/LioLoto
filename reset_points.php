<?php
$log = __DIR__ . "/reset_log.txt";
file_put_contents($log, "[" . date("Y-m-d H:i:s") . "] Reset script started\n", FILE_APPEND);

$path = "C:/xampp/htdocs/LioLoto/database/json/users.json";

$users = json_decode(@file_get_contents($path), true) ?? [];

foreach ($users as &$entry) {
    $entry['points'] = 1000;
}
unset($entry);

file_put_contents($path, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

file_put_contents($log, "[" . date("Y-m-d H:i:s") . "] Reset done\n", FILE_APPEND);
