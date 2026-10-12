<?php

namespace Tests\Feature;

use Illuminate\Contracts\Foundation\MaintenanceMode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HealthReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.debug' => false]);
    }

    public function test_health_is_ok_when_the_database_schema_is_ready(): void
    {
        $this->get('/up')->assertOk()->assertSee('Application up');
    }

    #[DataProvider('requiredTables')]
    public function test_health_fails_when_a_required_table_is_missing(string $table): void
    {
        Schema::drop($table);
        $this->get('/up')->assertStatus(500)->assertSee('Application experiencing problems');
    }

    public static function requiredTables(): array
    {
        return array_map(fn (string $table) => [$table], ['users', 'game_rounds', 'sessions', 'cache', 'cache_locks']);
    }

    public function test_health_fails_for_an_unavailable_database_without_exposing_connection_details(): void
    {
        $original = config('database.default');
        $missing = sys_get_temp_dir().'/private-health-db-'.bin2hex(random_bytes(8)).'.sqlite';
        config([
            'database.default' => 'unavailable_health',
            'database.connections.unavailable_health' => [
                'driver' => 'sqlite', 'database' => $missing, 'prefix' => '',
            ],
        ]);
        try {
            $this->get('/up')->assertStatus(500)
                ->assertDontSee($missing, false)
                ->assertDontSee('SQLiteDatabaseDoesNotExistException');
            $this->assertFileDoesNotExist($missing);
        } finally {
            config(['database.default' => $original]);
            DB::purge('unavailable_health');
        }
    }

    public function test_health_fails_during_maintenance_even_though_the_framework_exempts_its_route(): void
    {
        $this->mock(MaintenanceMode::class, fn ($mock) => $mock->shouldReceive('active')->andReturn(true));
        $this->get('/up')->assertStatus(500)->assertSee('Application experiencing problems');
    }
}
