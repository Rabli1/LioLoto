<?php

declare(strict_types=1);

// Shared implementation for PowerShell, POSIX shell, and container entrypoints.
// Child commands receive an argument array; configuration values never enter a shell.
chdir(dirname(__DIR__));

function fail(string $message): never
{
    fwrite(STDERR, $message.PHP_EOL);
    exit(1);
}

function run(array $command): void
{
    $process = proc_open($command, [STDIN, STDOUT, STDERR], $pipes, getcwd());
    if (!is_resource($process) || proc_close($process) !== 0) {
        fail('Command failed: '.implode(' ', array_map('strval', $command)));
    }
}

function composerCommand(): array
{
    $binary = getenv('COMPOSER_BINARY');
    if (!$binary && PHP_OS_FAMILY === 'Windows') {
        // ComposerSetup exposes composer.bat on PATH; invoke its adjacent PHAR
        // directly so the configured executable never needs shell interpolation.
        foreach (explode(PATH_SEPARATOR, (string) getenv('PATH')) as $directory) {
            $candidate = rtrim(trim($directory, '"'), '\\/').DIRECTORY_SEPARATOR.'composer.phar';
            if (is_file($candidate)) {
                return [PHP_BINARY, $candidate];
            }
        }
    }
    $binary = $binary ?: 'composer';
    if (PHP_OS_FAMILY === 'Windows' && in_array(strtolower(pathinfo($binary, PATHINFO_EXTENSION)), ['bat', 'cmd'], true)) {
        $candidate = dirname($binary).DIRECTORY_SEPARATOR.'composer.phar';
        if (!is_file($candidate)) {
            fail('Set COMPOSER_BINARY to the absolute path of composer.phar.');
        }
        return [PHP_BINARY, $candidate];
    }
    return str_ends_with(strtolower($binary), '.phar') ? [PHP_BINARY, $binary] : [$binary];
}

function npmCommand(): array
{
    return PHP_OS_FAMILY === 'Windows' ? ['cmd.exe', '/d', '/c', 'npm.cmd'] : ['npm'];
}

function databaseCommand(Illuminate\Contracts\Console\Kernel $kernel, string $command, string $failure): void
{
    try {
        $exit = $kernel->call($command, ['--force' => true, '--no-interaction' => true]);
    } catch (Throwable) {
        // The bootstrap exception handler can otherwise render an error and exit 0.
        // Connection exceptions may include credentials, so keep failure output neutral.
        fail($failure);
    }
    if ($exit !== 0) {
        fail($failure);
    }
    echo $kernel->output();
}

try {
$mode = $argv[1] ?? '';
$flags = array_slice($argv, 2);
$allowed = ['--skip-install', '--skip-build', '--demo', '--seed', '--fresh'];
$confirmation = null;
foreach ($flags as $flag) {
    if (str_starts_with($flag, '--confirm=')) {
        $confirmation = substr($flag, strlen('--confirm='));
    } elseif (!in_array($flag, $allowed, true)) {
        fail('Unknown option: '.$flag);
    }
}

if (!in_array($mode, ['setup', 'deploy', 'database'], true)) {
    fail('Usage: php scripts/manage.php setup|deploy|database [--skip-install] [--skip-build] [--demo] [--seed] [--fresh --confirm=TARGET]');
}
if (in_array('--fresh', $flags, true) && $mode !== 'database') {
    fail('Database rebuilds are separate: use database --fresh --confirm=TARGET.');
}
if ($confirmation !== null && !in_array('--fresh', $flags, true)) {
    fail('--confirm requires --fresh.');
}

if ($mode === 'setup' && !file_exists('.env') && getenv('APP_ENV') !== 'production') {
    if (!copy('.env.example', '.env')) {
        fail('Unable to create .env.');
    }
    echo 'Created local .env; existing environment files are preserved.'.PHP_EOL;
}
if ($mode !== 'database' && !in_array('--skip-install', $flags, true)) {
    $composerOptions = ['install', '--prefer-dist', '--no-interaction'];
    if ($mode === 'deploy') {
        $composerOptions = [...$composerOptions, '--no-dev', '--optimize-autoloader'];
    }
    run([...composerCommand(), ...$composerOptions]);
}
if (!file_exists('vendor/autoload.php')) {
    fail('Install Composer dependencies first, or run setup without --skip-install.');
}

foreach (['bootstrap/cache', 'storage/app/private', 'storage/app/public', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs'] as $directory) {
    if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
        fail('Unable to create '.$directory);
    }
}

run([PHP_BINARY, 'artisan', 'config:clear', '--no-interaction']);
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$environment = (string) config('app.env');
$local = in_array($environment, ['local', 'testing'], true);
$fresh = in_array('--fresh', $flags, true);
if ($fresh && !$local) {
    fail('Fresh rebuild is blocked outside APP_ENV=local or testing. Production deployments only apply migrations.');
}
if (in_array('--demo', $flags, true) && !$local) {
    fail('Demo accounts are blocked outside local/testing environments.');
}
if ($mode === 'setup' && !$local) {
    fail('Setup is for APP_ENV=local or testing. Use deploy for a configured production release.');
}
if ($mode === 'deploy') {
    if ($environment !== 'production') {
        fail('Deployment requires APP_ENV=production. Configure .env or supply environment variables first.');
    }
    if (config('app.debug')) {
        fail('Production deployment requires APP_DEBUG=false.');
    }
    if (!str_starts_with((string) config('app.url'), 'https://')) {
        fail('Production deployment requires an https:// APP_URL and an HTTPS reverse proxy.');
    }
    if (!config('session.secure') || !config('session.http_only')) {
        fail('Production deployment requires SESSION_SECURE_COOKIE=true and SESSION_HTTP_ONLY=true.');
    }
    if (config('auth.require_email_confirmation', true)
        && (config('mail.default') !== 'smtp' || !config('mail.mailers.smtp.host') || !config('mail.from.address'))) {
        fail('Production deployment requires a configured SMTP mailer for account confirmation.');
    }
}
$applicationKey = (string) config('app.key');
if ($applicationKey !== '') {
    $decodedKey = str_starts_with($applicationKey, 'base64:') ? base64_decode(substr($applicationKey, 7), true) : $applicationKey;
    if (!is_string($decodedKey) || strlen($decodedKey) !== 32) {
        fail('APP_KEY must contain a valid 32-byte encryption key.');
    }
}
if ($applicationKey === '') {
    if (!$local || $mode !== 'setup') {
        fail('APP_KEY is required; preserve the same secret key across releases.');
    }
    run([PHP_BINARY, 'artisan', 'key:generate', '--no-interaction']);
}

$connection = (string) config('database.default');
$database = config('database.connections.'.$connection);
if (!is_array($database)) {
    fail('Configure a supported database connection: sqlite, mysql, or pgsql.');
}
if (isset($database['url']) && is_string($database['url']) && preg_match('/[\s\p{Z}]/u', $database['url']) !== 0) {
    fail('The database connection URL contains invalid whitespace. Copy the unwrapped connection string.');
}
try {
    $database = (new Illuminate\Database\ConfigurationUrlParser)->parseConfiguration($database);
} catch (Throwable) {
    // Never print a connection URI, which contains a database password.
    fail('The database connection URL is malformed.');
}
$driver = (string) ($database['driver'] ?? '');
if (!in_array($driver, ['sqlite', 'mysql', 'pgsql'], true)) {
    fail('Configure a supported database connection: sqlite, mysql, or pgsql.');
}
if (getenv('RENDER') === 'true') {
    if ($driver !== 'pgsql' || !getenv('DATABASE_URL')) {
        fail('Render requires a durable external PostgreSQL DATABASE_URL. Ephemeral SQLite is blocked.');
    }
    if (!in_array($database['sslmode'] ?? '', ['require', 'verify-ca', 'verify-full'], true)) {
        fail('Render PostgreSQL requires TLS: use sslmode=require in DATABASE_URL.');
    }
}
config(['database.connections.'.$connection => $database]);
Illuminate\Support\Facades\DB::purge($connection);

$databaseName = (string) ($database['database'] ?? '');
if ($databaseName === '' || $databaseName === ':memory:') {
    fail('Use a persistent, named database. Automated tests should use a disposable SQLite file.');
}
if ($driver === 'sqlite') {
    $absolute = str_starts_with($databaseName, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $databaseName) === 1;
    $databaseName = $absolute ? $databaseName : getcwd().DIRECTORY_SEPARATOR.$databaseName;
    $directory = dirname($databaseName);
    if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
        fail('Unable to create database directory.');
    }
    if (!file_exists($databaseName) && !touch($databaseName)) {
        fail('Unable to create SQLite database.');
    }
    if (!is_writable($databaseName) || !is_writable($directory)) {
        fail('SQLite database and its directory must be writable for WAL transactions.');
    }
    $databaseName = realpath($databaseName) ?: $databaseName;
    config(['database.connections.'.$connection.'.database' => $databaseName]);
    $target = $driver.':'.$databaseName;
} else {
    $port = $database['port'] ?? ($driver === 'pgsql' ? 5432 : 3306);
    $target = $driver.':'.($database['host'] ?? 'localhost').':'.$port.':'.$databaseName;
}
echo 'Database target: '.$target.PHP_EOL;

if ($fresh) {
    if ($confirmation === null || !hash_equals($target, $confirmation)) {
        fail('Rebuilding deletes all database tables. Repeat with --fresh and --confirm='.$target);
    }
}
if ($mode !== 'database' && !in_array('--skip-build', $flags, true)) {
    run([...npmCommand(), 'ci', '--no-audit', '--no-fund']);
    run([...npmCommand(), 'run', 'build']);
}
if ($mode === 'deploy' && !file_exists('public/build/manifest.json')) {
    fail('Missing Vite build manifest; build the frontend before deployment.');
}

// Deployment failures keep the application in maintenance mode for inspection.
if ($mode === 'deploy') {
    run([PHP_BINARY, 'artisan', 'down', '--retry=60', '--no-interaction']);
}
databaseCommand($kernel, $fresh ? 'migrate:fresh' : 'migrate', 'Database migration failed. Verify database connectivity and migration permissions.');
if (in_array('--seed', $flags, true)) {
    databaseCommand($kernel, 'db:seed', 'Database seeding failed.');
}
if (in_array('--demo', $flags, true)) {
    if (Illuminate\Support\Facades\DB::table('users')->where('name', 'demo')->exists()) {
        echo 'Demo account already exists; its password and progress were preserved.'.PHP_EOL;
    } else {
        $password = bin2hex(random_bytes(12));
        Illuminate\Support\Facades\DB::table('users')->insert([
            'name' => 'demo', 'email' => 'demo@example.test',
            'password' => Illuminate\Support\Facades\Hash::make($password),
            'confirmed' => true, 'admin' => false,
            'profileImage' => 'fa-user', 'profileColor' => 'black',
            'last_update' => date('Y-m-d'), 'created_at' => now(), 'updated_at' => now(),
        ]);
        echo 'Local demo login: demo'.PHP_EOL.'Generated password: '.$password.PHP_EOL;
    }
}
if ($mode === 'deploy') {
    // optimize does not propagate every nested cache command's failure status.
    foreach (['config:cache', 'event:cache', 'route:cache', 'view:cache'] as $command) {
        run([PHP_BINARY, 'artisan', $command, '--no-interaction']);
    }
    run([PHP_BINARY, 'artisan', 'up', '--no-interaction']);
}
echo ucfirst($mode).' complete.'.PHP_EOL;
} catch (Throwable) {
    // Unexpected bootstrap/setup errors must also stop the container entrypoint.
    fail('Application setup failed. Verify deployment configuration.');
}
