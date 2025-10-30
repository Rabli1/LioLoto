<?php

$log = __DIR__ . "/reset_log.txt";
file_put_contents($log, "[" . date("Y-m-d H:i:s") . "] Reset script started\n", FILE_APPEND);

$path = __DIR__ . '/database/json/users.json';

$users = json_decode(@file_get_contents($path), true);

if (!is_array($users) || $users === []) {
    file_put_contents(
        $log,
        "[" . date("Y-m-d H:i:s") . "] No users to reset or invalid JSON\n",
        FILE_APPEND
    );
    return;
}

$sortedUsers = $users;
usort($sortedUsers, static function ($a, $b) {
    $pointComparison = ($b['points'] ?? 0) <=> ($a['points'] ?? 0);

    if ($pointComparison !== 0) {
        return $pointComparison;
    }

    return ($a['id'] ?? 0) <=> ($b['id'] ?? 0);
});

$medalFields = ['gold', 'silver', 'bronze'];
$awarded = [];

foreach ($medalFields as $rank => $field) {
    if (!isset($sortedUsers[$rank])) {
        break;
    }

    $winnerId = $sortedUsers[$rank]['id'] ?? null;

    if ($winnerId === null) {
        continue;
    }

    foreach ($users as &$entry) {
        if (($entry['id'] ?? null) === $winnerId) {
            $entry[$field] = ($entry[$field] ?? 0) + 1;
            $awarded[] = sprintf(
                '%d. %s (ID %s) -> %s',
                $rank + 1,
                $entry['name'] ?? 'Unknown',
                $winnerId,
                strtoupper($field)
            );
            break;
        }
    }
    unset($entry);
}

foreach ($users as &$entry) {
    $entry['points'] = 1000;
}
unset($entry);

file_put_contents(
    $path,
    json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
    LOCK_EX
);

if ($awarded !== []) {
    file_put_contents(
        $log,
        "[" . date("Y-m-d H:i:s") . "] Medals awarded: " . implode(' | ', $awarded) . "\n",
        FILE_APPEND
    );
}

file_put_contents($log, "[" . date("Y-m-d H:i:s") . "] Reset done\n", FILE_APPEND);
