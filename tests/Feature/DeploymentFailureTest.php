<?php

namespace Tests\Feature;

use Symfony\Component\Process\Process;
use Tests\TestCase;

class DeploymentFailureTest extends TestCase
{
    private string $database;
    private string $cacheRoot;
    private string $storage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = tempnam(sys_get_temp_dir(), 'lioloto-failure-');
        $this->cacheRoot = 'bootstrap/cache/failure-'.basename($this->database);
        $this->storage = sys_get_temp_dir().DIRECTORY_SEPARATOR.'storage-'.basename($this->database);
        mkdir($this->storage.DIRECTORY_SEPARATOR.'framework', 0770, true);
        mkdir($this->storage.DIRECTORY_SEPARATOR.'logs', 0770, true);
    }

    protected function tearDown(): void
    {
        $files = [$this->database, $this->database.'-wal', $this->database.'-shm',
            $this->storage.'/framework/down', $this->storage.'/framework/maintenance.php',
            $this->storage.'/logs/laravel.log'];
        foreach (['config', 'routes', 'events', 'services', 'packages'] as $kind) {
            $files[] = $this->cacheRoot.'.'.$kind.'.php';
        }
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($this->storage.'/framework');
        rmdir($this->storage.'/logs');
        rmdir($this->storage);
        parent::tearDown();
    }

    private function process(array $arguments): Process
    {
        $process = new Process([PHP_BINARY, ...$arguments], base_path(), [
            'APP_ENV' => 'production', 'APP_DEBUG' => 'false',
            'APP_URL' => 'https://deployment.example.test',
            'APP_KEY' => 'base64:'.base64_encode(str_repeat('f', 32)),
            'RENDER' => 'false', 'DATABASE_URL' => false, 'DB_URL' => false,
            'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $this->database,
            'AUTH_REQUIRE_EMAIL_CONFIRMATION' => 'false', 'MAIL_MAILER' => 'log',
            'SESSION_SECURE_COOKIE' => 'true', 'SESSION_HTTP_ONLY' => 'true',
            'SESSION_DRIVER' => 'array', 'CACHE_STORE' => 'array', 'QUEUE_CONNECTION' => 'sync',
            'APP_MAINTENANCE_DRIVER' => 'file', 'LARAVEL_STORAGE_PATH' => $this->storage,
            'APP_CONFIG_CACHE' => $this->cacheRoot.'.config.php',
            'APP_ROUTES_CACHE' => $this->cacheRoot.'.routes.php',
            'APP_EVENTS_CACHE' => $this->cacheRoot.'.events.php',
            'APP_SERVICES_CACHE' => $this->cacheRoot.'.services.php',
            'APP_PACKAGES_CACHE' => $this->cacheRoot.'.packages.php',
        ]);
        $process->setTimeout(30);
        $process->run();
        return $process;
    }

    public function test_failed_migration_returns_one_and_does_not_reopen_deployment(): void
    {
        file_put_contents($this->database, 'This is a corrupt disposable database.');
        $process = $this->process(['scripts/manage.php', 'deploy', '--skip-install', '--skip-build']);

        $this->assertSame(1, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
        $this->assertStringContainsString('Database migration failed', $process->getErrorOutput());
        $this->assertStringNotContainsString('Deploy complete', $process->getOutput());
        $this->assertFileExists($this->storage.'/framework/down');
        $this->assertFileDoesNotExist($this->cacheRoot.'.config.php');
    }

    public function test_throwing_seed_returns_one_and_does_not_print_exception_credentials(): void
    {
        // Replace only the subprocess's seeder, without changing application files.
        $program = <<<'PHP'
namespace { require 'vendor/autoload.php'; }
namespace Database\Seeders {
    class DatabaseSeeder extends \Illuminate\Database\Seeder {
        public function run(): void {
            throw new \RuntimeException('postgresql://test-role:seed-secret-sentinel@private.invalid/database');
        }
    }
}
namespace { require 'scripts/manage.php'; }
PHP;
        $process = $this->process(['-r', $program, 'database', '--seed']);

        $this->assertSame(1, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
        $this->assertStringContainsString('Database seeding failed', $process->getErrorOutput());
        $this->assertStringNotContainsString('seed-secret-sentinel', $process->getOutput().$process->getErrorOutput());
        $this->assertStringNotContainsString('postgresql://', $process->getOutput().$process->getErrorOutput());
    }
}
