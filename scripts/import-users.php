<?php

declare(strict_types=1);

// Import only an explicitly supplied, trusted legacy export. Never import at startup.
chdir(dirname(__DIR__));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$source = $argv[1] ?? '';
$dryRun = ($argv[2] ?? '') === '--dry-run';
if ($source === '' || count($argv) > 3 || (isset($argv[2]) && !$dryRun)) {
    fwrite(STDERR, 'Usage: php scripts/import-users.php /trusted/path/users.json [--dry-run]'.PHP_EOL);
    exit(1);
}

try {
    if (!is_file($source) || !is_readable($source) || filesize($source) > 20 * 1024 * 1024) {
        throw new RuntimeException('Provide a readable legacy JSON file smaller than 20 MiB.');
    }
    $records = json_decode(file_get_contents($source), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($records) || !array_is_list($records)) {
        throw new RuntimeException('Legacy export must be a JSON array of users.');
    }

    $validated = [];
    $ids = $names = $emails = [];
    foreach ($records as $index => $record) {
        if (!is_array($record)) {
            throw new RuntimeException('Invalid record at index '.$index.'.');
        }
        $data = Illuminate\Support\Facades\Validator::make($record, [
            'id' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'points' => ['sometimes', 'integer', 'min:0', 'max:2147483647'],
            'profileImage' => ['sometimes', 'string', 'max:255'],
            'profileColor' => ['sometimes', 'string', 'max:255'],
            'gold' => ['sometimes', 'integer', 'min:0', 'max:2147483647'],
            'silver' => ['sometimes', 'integer', 'min:0', 'max:2147483647'],
            'bronze' => ['sometimes', 'integer', 'min:0', 'max:2147483647'],
            'pointsLost' => ['sometimes', 'integer', 'min:0', 'max:2147483647'],
            'bio' => ['nullable', 'string', 'max:255'],
            'admin' => ['sometimes', 'boolean'],
            'confirmed' => ['sometimes', 'boolean'],
            'banned' => ['sometimes', 'boolean'],
            'lvl' => ['sometimes', 'integer', 'min:1', 'max:2147483647'],
            'exp' => ['sometimes', 'integer', 'min:0', 'max:2147483647'],
            'last_update' => ['nullable', 'date_format:Y-m-d'],
            'daily' => ['sometimes', 'boolean'],
        ])->validate();

        if (!in_array(password_get_info($data['password'])['algoName'], ['bcrypt', 'argon2i', 'argon2id'], true)) {
            throw new RuntimeException('User '.$data['id'].' has no supported password hash; plaintext passwords are rejected.');
        }
        $id = (int) $data['id'];
        $name = mb_strtolower($data['name']);
        $email = isset($data['email']) ? mb_strtolower($data['email']) : null;
        if (isset($ids[$id]) || isset($names[$name]) || ($email !== null && isset($emails[$email]))) {
            throw new RuntimeException('Duplicate identity in legacy export at user '.$id.'.');
        }
        $ids[$id] = $names[$name] = true;
        if ($email !== null) {
            $emails[$email] = true;
            $data['email'] = $email;
        }
        $data['id'] = $id;
        $data['confirmation_token'] = null;
        $data['remember_token'] = null;
        $data['created_at'] = now();
        $data['updated_at'] = now();
        $validated[] = $data;
    }

    $imported = $skipped = 0;
    Illuminate\Support\Facades\DB::transaction(function () use ($validated, $dryRun, &$imported, &$skipped) {
        foreach ($validated as $data) {
            $existing = Illuminate\Support\Facades\DB::table('users')->where('id', $data['id'])->first();
            if ($existing !== null) {
                if ($existing->name !== $data['name'] || mb_strtolower((string) $existing->email) !== mb_strtolower((string) ($data['email'] ?? ''))) {
                    throw new RuntimeException('Existing ID conflicts with legacy user '.$data['id'].'. Nothing was imported.');
                }
                // Reruns never overwrite current credentials, balances, or privileges.
                $skipped++;
                continue;
            }
            $conflict = Illuminate\Support\Facades\DB::table('users')->whereRaw('LOWER(name) = ?', [mb_strtolower($data['name'])]);
            if (!empty($data['email'])) {
                $conflict->orWhereRaw('LOWER(email) = ?', [$data['email']]);
            }
            if ($conflict->exists()) {
                throw new RuntimeException('Existing username/email conflicts with legacy user '.$data['id'].'. Nothing was imported.');
            }
            if (!$dryRun) {
                Illuminate\Support\Facades\DB::table('users')->insert($data);
            }
            $imported++;
        }
        if (!$dryRun && $imported > 0 && Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
            // Explicit legacy IDs must not collide with PostgreSQL's next generated ID.
            Illuminate\Support\Facades\DB::statement("SELECT setval(pg_get_serial_sequence('users', 'id'), (SELECT MAX(id) FROM users), true)");
        }
    });

    echo ($dryRun ? 'Dry run: ' : 'Import complete: ').$imported.' new users, '.$skipped.' existing users preserved.'.PHP_EOL;
    echo 'Legacy confirmation and remember tokens were invalidated; the source file was preserved.'.PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, 'Import failed: '.$exception->getMessage().PHP_EOL);
    exit(1);
}
