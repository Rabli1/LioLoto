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

$rankMedals = [
    1 => 'gold',
    2 => 'silver',
    3 => 'bronze',
];
$awarded = [];

$userIndex = [];
foreach ($users as $idx => $entry) {
    if (isset($entry['id'])) {
        $userIndex[$entry['id']] = $idx;
    }
}
unset($entry);

$previousPoints = null;
$currentRank = 0;

foreach ($sortedUsers as $entry) {
    $points = isset($entry['points']) ? (int) $entry['points'] : 0;

    if ($previousPoints === null) {
        $currentRank = 1;
    } elseif ($points !== $previousPoints) {
        $currentRank += 1;
    }

    if (!isset($rankMedals[$currentRank])) {
        break;
    }

    $winnerId = $entry['id'] ?? null;
    if ($winnerId === null || !isset($userIndex[$winnerId])) {
        $previousPoints = $points;
        continue;
    }

    $medalField = $rankMedals[$currentRank];
    $userIdx = $userIndex[$winnerId];
    $users[$userIdx][$medalField] = ($users[$userIdx][$medalField] ?? 0) + 1;

    $awarded[] = sprintf(
        '%d. %s (ID %s, %d pts) -> %s',
        $currentRank,
        $users[$userIdx]['name'] ?? 'Unknown',
        $winnerId,
        $points,
        strtoupper($medalField)
    );

    $previousPoints = $points;
}

foreach ($users as &$entry) {
    $entry['points'] = 1000;
    $entry['daily'] = true;
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
