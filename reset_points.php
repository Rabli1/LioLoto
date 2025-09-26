<?php
<<<<<<< HEAD
$path = __DIR__ . '/database/json/users.json';
$users = json_decode(@file_get_contents($path), true) ?? [];
$today = date("Y-m-d");
foreach ($users as &$entry) {
    if (!isset($entry['last_update']) || $entry['last_update'] !== $today) {
        $entry['last_update'] = $today;
        $entry['points'] = 1000;
    }
}
unset($entry);
file_put_contents($path, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
=======
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
>>>>>>> fb12690f0001508293fca83b9b9f123363c4998c
