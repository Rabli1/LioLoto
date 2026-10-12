<?php

namespace Tests\Feature;

use PDO;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class DatabaseLifecycleTest extends TestCase
{
    private string $database;
    private string $cacheRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = tempnam(sys_get_temp_dir(), 'lioloto-db-');
        $this->cacheRoot = 'bootstrap/cache/test-'.basename($this->database);
    }

    protected function tearDown(): void
    {
        foreach ([$this->database, $this->database.'-wal', $this->database.'-shm',
            $this->cacheRoot.'.config.php', $this->cacheRoot.'.routes.php', $this->cacheRoot.'.events.php',
            $this->cacheRoot.'.services.php', $this->cacheRoot.'.packages.php'] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
    }

    private function command(array $arguments, string $environment = 'testing'): Process
    {
        $process = new Process([PHP_BINARY, ...$arguments], base_path(), [
            'APP_ENV' => $environment,
            'APP_KEY' => 'base64:'.base64_encode(str_repeat('t', 32)),
            'APP_DEBUG' => 'false',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => $this->database,
            'DB_URL' => false,
            'CACHE_STORE' => 'array',
            'SESSION_DRIVER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'MAIL_MAILER' => 'array',
            'APP_CONFIG_CACHE' => $this->cacheRoot.'.config.php',
            'APP_ROUTES_CACHE' => $this->cacheRoot.'.routes.php',
            'APP_EVENTS_CACHE' => $this->cacheRoot.'.events.php',
            'APP_SERVICES_CACHE' => $this->cacheRoot.'.services.php',
            'APP_PACKAGES_CACHE' => $this->cacheRoot.'.packages.php',
        ]);
        $process->setTimeout(60);
        $process->run();
        return $process;
    }

    private function sql(): PDO
    {
        return new PDO('sqlite:'.$this->database, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    public function test_setup_reruns_preserve_data_and_explicit_rebuild_resets_it(): void
    {
        $first = $this->command(['scripts/manage.php', 'setup', '--skip-install', '--skip-build', '--demo']);
        $this->assertTrue($first->isSuccessful(), $first->getOutput().$first->getErrorOutput());
        $pdo = $this->sql();
        $demo = $pdo->query('SELECT * FROM users')->fetch(PDO::FETCH_ASSOC);
        $this->assertSame('demo', $demo['name']);
        $this->assertSame(0, (int) $demo['admin']);
        $this->assertSame('bcrypt', password_get_info($demo['password'])['algoName']);
        $pdo->exec('UPDATE users SET points = 37');

        $again = $this->command(['scripts/manage.php', 'setup', '--skip-install', '--skip-build', '--demo']);
        $this->assertTrue($again->isSuccessful(), $again->getOutput().$again->getErrorOutput());
        $this->assertSame(37, (int) $pdo->query('SELECT points FROM users')->fetchColumn());
        $this->assertSame($demo['password'], $pdo->query('SELECT password FROM users')->fetchColumn());

        $missingConfirmation = $this->command(['scripts/manage.php', 'database', '--fresh']);
        $this->assertFalse($missingConfirmation->isSuccessful());
        $this->assertSame(37, (int) $pdo->query('SELECT points FROM users')->fetchColumn());

        $target = 'sqlite:'.realpath($this->database);
        $wrongConfirmation = $this->command(['scripts/manage.php', 'database', '--fresh', '--confirm=sqlite:wrong-file']);
        $this->assertFalse($wrongConfirmation->isSuccessful());
        $this->assertSame(37, (int) $pdo->query('SELECT points FROM users')->fetchColumn());

        $production = $this->command(['scripts/manage.php', 'database', '--fresh', '--confirm='.$target], 'production');
        $this->assertFalse($production->isSuccessful());
        $this->assertStringContainsString('Fresh rebuild is blocked', $production->getErrorOutput());
        $this->assertSame(37, (int) $pdo->query('SELECT points FROM users')->fetchColumn());

        // A rebuild replaces the schema; close existing connections before it.
        $pdo = null;
        $fresh = $this->command(['scripts/manage.php', 'database', '--fresh', '--confirm='.$target, '--seed']);
        $this->assertTrue($fresh->isSuccessful(), $fresh->getOutput().$fresh->getErrorOutput());
        $pdo = $this->sql();
        $this->assertSame('ok', $pdo->query('PRAGMA integrity_check')->fetchColumn());
        $this->assertSame(0, (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn());
        $this->assertNotFalse($pdo->query("SELECT name FROM sqlite_master WHERE name = 'game_rounds'")->fetchColumn());
    }

    public function test_forward_migration_preserves_legacy_sql_users(): void
    {
        $legacy = $this->command(['artisan', 'migrate', '--force', '--no-interaction', '--path=database/migrations/0001_01_01_000000_create_users_table.php']);
        $this->assertTrue($legacy->isSuccessful(), $legacy->getOutput().$legacy->getErrorOutput());
        $pdo = $this->sql();
        $password = password_hash('a-random-test-secret', PASSWORD_BCRYPT);
        $insert = $pdo->prepare('INSERT INTO users (nom, motDePasse, solde, pointPerdu, confirme) VALUES (?, ?, ?, ?, ?)');
        $insert->execute(['legacy-player', $password, 2450, 39, 1]);

        $upgrade = $this->command(['scripts/manage.php', 'database']);
        $this->assertTrue($upgrade->isSuccessful(), $upgrade->getOutput().$upgrade->getErrorOutput());
        $user = $pdo->query('SELECT * FROM users')->fetch(PDO::FETCH_ASSOC);
        $this->assertSame('legacy-player', $user['name']);
        $this->assertSame($password, $user['password']);
        $this->assertSame(2450, (int) $user['points']);
        $this->assertSame(39, (int) $user['pointsLost']);
        $this->assertSame(1, (int) $user['confirmed']);
        $this->assertNull($user['email']);
        $this->assertSame(0, (int) $user['admin']);
    }

    public function test_production_deployment_and_redeployment_preserve_data(): void
    {
        $setup = $this->command(['scripts/manage.php', 'setup', '--skip-install', '--skip-build', '--demo']);
        $this->assertTrue($setup->isSuccessful(), $setup->getOutput().$setup->getErrorOutput());
        $pdo = $this->sql();
        $pdo->exec('UPDATE users SET points = 83');
        $password = $pdo->query('SELECT password FROM users')->fetchColumn();

        $process = new Process([PHP_BINARY, 'scripts/manage.php', 'deploy', '--skip-install', '--skip-build'], base_path(), [
            'APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://games.example.test',
            'APP_KEY' => 'base64:'.base64_encode(str_repeat('t', 32)),
            'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $this->database, 'DB_URL' => false,
            'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'SESSION_SECURE_COOKIE' => 'true',
            'SESSION_HTTP_ONLY' => 'true', 'MAIL_MAILER' => 'smtp', 'MAIL_HOST' => 'smtp.example.test',
            'MAIL_FROM_ADDRESS' => 'hello@example.test', 'QUEUE_CONNECTION' => 'sync',
            'APP_CONFIG_CACHE' => $this->cacheRoot.'.config.php',
            'APP_ROUTES_CACHE' => $this->cacheRoot.'.routes.php',
            'APP_EVENTS_CACHE' => $this->cacheRoot.'.events.php',
            'APP_SERVICES_CACHE' => $this->cacheRoot.'.services.php',
            'APP_PACKAGES_CACHE' => $this->cacheRoot.'.packages.php',
        ]);
        $process->setTimeout(60);
        for ($release = 0; $release < 2; $release++) {
            $process->run();
            $this->assertTrue($process->isSuccessful(), $process->getOutput().$process->getErrorOutput());
            $this->assertSame(83, (int) $pdo->query('SELECT points FROM users')->fetchColumn());
            $this->assertSame($password, $pdo->query('SELECT password FROM users')->fetchColumn());
            $this->assertSame('ok', $pdo->query('PRAGMA integrity_check')->fetchColumn());
        }
    }

    public function test_legacy_import_is_explicit_and_reruns_preserve_current_data(): void
    {
        $setup = $this->command(['scripts/manage.php', 'database']);
        $this->assertTrue($setup->isSuccessful(), $setup->getOutput().$setup->getErrorOutput());
        $source = tempnam(sys_get_temp_dir(), 'lioloto-import-');
        try {
            file_put_contents($source, json_encode([[
                'id' => 8, 'name' => 'imported', 'email' => 'imported@example.test',
                'password' => password_hash('test-only-secret', PASSWORD_BCRYPT),
                'points' => 1530, 'confirmed' => true, 'confirmation_token' => 'legacy-raw-token',
            ]], JSON_THROW_ON_ERROR));
            $dryRun = $this->command(['scripts/import-users.php', $source, '--dry-run']);
            $this->assertTrue($dryRun->isSuccessful(), $dryRun->getOutput().$dryRun->getErrorOutput());
            $pdo = $this->sql();
            $this->assertSame(0, (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn());

            $import = $this->command(['scripts/import-users.php', $source]);
            $this->assertTrue($import->isSuccessful(), $import->getOutput().$import->getErrorOutput());
            $this->assertNull($pdo->query('SELECT confirmation_token FROM users')->fetchColumn());
            $pdo->exec('UPDATE users SET points = 22');

            $repeat = $this->command(['scripts/import-users.php', $source]);
            $this->assertTrue($repeat->isSuccessful(), $repeat->getOutput().$repeat->getErrorOutput());
            $this->assertSame(22, (int) $pdo->query('SELECT points FROM users')->fetchColumn());
            $this->assertFileExists($source);
        } finally {
            unlink($source);
        }
    }
}
