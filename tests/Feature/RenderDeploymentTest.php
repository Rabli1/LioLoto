<?php

namespace Tests\Feature;

use Symfony\Component\Process\Process;
use Tests\TestCase;

class RenderDeploymentTest extends TestCase
{
    private string $cacheRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheRoot = 'bootstrap/cache/render-test-'.bin2hex(random_bytes(8));
    }

    protected function tearDown(): void
    {
        foreach (['config', 'routes', 'events', 'services', 'packages'] as $kind) {
            $file = $this->cacheRoot.'.'.$kind.'.php';
            if (is_file($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
    }

    private function command(array $overrides = [], array $arguments = ['database']): Process
    {
        $environment = array_merge([
            'APP_ENV' => 'production', 'APP_DEBUG' => 'false',
            'APP_URL' => 'https://lioloto-test.onrender.com',
            'APP_KEY' => 'base64:'.base64_encode(str_repeat('r', 32)),
            'RENDER' => 'true',
            'DB_CONNECTION' => 'pgsql', 'DB_SSLMODE' => 'require',
            'DB_DATABASE' => 'ignored-default', 'DB_HOST' => 'ignored-host', 'DB_PORT' => '5432',
            'DATABASE_URL' => 'postgresql://test-role:secret-sentinel@database.example.invalid/neondb?sslmode=require',
            'DB_URL' => false,
            'AUTH_REQUIRE_EMAIL_CONFIRMATION' => 'false',
            'MAIL_MAILER' => 'log',
            'SESSION_SECURE_COOKIE' => 'true', 'SESSION_HTTP_ONLY' => 'true',
            'SESSION_DRIVER' => 'array', 'CACHE_STORE' => 'array', 'QUEUE_CONNECTION' => 'sync',
            'APP_CONFIG_CACHE' => $this->cacheRoot.'.config.php',
            'APP_ROUTES_CACHE' => $this->cacheRoot.'.routes.php',
            'APP_EVENTS_CACHE' => $this->cacheRoot.'.events.php',
            'APP_SERVICES_CACHE' => $this->cacheRoot.'.services.php',
            'APP_PACKAGES_CACHE' => $this->cacheRoot.'.packages.php',
        ], $overrides);
        $process = new Process([PHP_BINARY, 'scripts/manage.php', ...$arguments], base_path(), $environment);
        $process->setTimeout(30);
        $process->run();
        return $process;
    }

    private function assertSecretWasNotPrinted(Process $process, string $secret = 'secret-sentinel'): void
    {
        $output = $process->getOutput().$process->getErrorOutput();
        $this->assertStringNotContainsString($secret, $output);
        $this->assertStringNotContainsString('postgresql://', $output);
        $this->assertStringNotContainsString('test-role:', $output);
    }

    public function test_render_refuses_ephemeral_sqlite_even_with_a_postgresql_secret(): void
    {
        $process = $this->command(['DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:']);
        $this->assertFalse($process->isSuccessful());
        $this->assertStringContainsString('Ephemeral SQLite is blocked', $process->getErrorOutput());
        $this->assertSecretWasNotPrinted($process);
    }

    public function test_render_rejects_a_url_that_downgrades_tls(): void
    {
        $process = $this->command([
            'DATABASE_URL' => 'postgresql://test-role:secret-sentinel@database.example.invalid/neondb?sslmode=disable',
        ]);
        $this->assertFalse($process->isSuccessful());
        $this->assertStringContainsString('Render PostgreSQL requires TLS', $process->getErrorOutput());
        $this->assertSecretWasNotPrinted($process);
    }

    public function test_url_parsing_uses_the_real_target_and_never_prints_uri_credentials(): void
    {
        // The missing reset confirmation stops before any database connection.
        $process = $this->command([
            'APP_ENV' => 'testing',
            'DATABASE_URL' => 'postgres://test-role:encoded%40secret%3Asentinel@database.example.invalid:5440/game%2Ddata?sslmode=require',
        ], ['database', '--fresh']);
        $this->assertFalse($process->isSuccessful());
        $this->assertStringContainsString('Database target: pgsql:database.example.invalid:5440:game-data', $process->getOutput());
        $this->assertStringContainsString('--confirm=pgsql:database.example.invalid:5440:game-data', $process->getErrorOutput());
        $this->assertSecretWasNotPrinted($process, 'encoded@secret:sentinel');
        $this->assertStringNotContainsString('encoded%40secret%3Asentinel', $process->getOutput().$process->getErrorOutput());
        $this->assertStringNotContainsString('postgres://', $process->getOutput().$process->getErrorOutput());
    }

    public function test_malformed_url_errors_do_not_expose_the_secret(): void
    {
        $process = $this->command([
            'DATABASE_URL' => 'postgresql://test-role:secret-sentinel@database.example.invalid:not-a-port/neondb',
        ]);
        $this->assertFalse($process->isSuccessful());
        $this->assertStringContainsString('database connection URL is malformed', $process->getErrorOutput());
        $this->assertSecretWasNotPrinted($process);
    }

    public function test_free_profile_allows_log_mail_but_normal_confirmation_keeps_the_smtp_guard(): void
    {
        $free = $this->command(['DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:'], ['deploy', '--skip-install', '--skip-build']);
        $this->assertFalse($free->isSuccessful());
        // Getting as far as the database guard proves log mail was accepted.
        $this->assertStringContainsString('Ephemeral SQLite is blocked', $free->getErrorOutput());
        $this->assertSecretWasNotPrinted($free);

        $normal = $this->command(['AUTH_REQUIRE_EMAIL_CONFIRMATION' => 'true'], ['deploy', '--skip-install', '--skip-build']);
        $this->assertFalse($normal->isSuccessful());
        $this->assertStringContainsString('configured SMTP mailer for account confirmation', $normal->getErrorOutput());
        $this->assertSecretWasNotPrinted($normal);
    }
}
