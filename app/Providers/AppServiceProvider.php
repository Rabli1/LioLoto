<?php

namespace App\Providers;

use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(DiagnosingHealth::class, function (): void {
            // Laravel excludes /up from its maintenance middleware by default.
            if ($this->app->isDownForMaintenance()) {
                throw new \RuntimeException('Application is in maintenance mode.');
            }

            try {
                $connection = DB::connection();
                $connection->getPdo();
                $schema = Schema::connection($connection->getName());
                foreach (['users', 'game_rounds', 'sessions', 'cache', 'cache_locks'] as $table) {
                    if (!$schema->hasTable($table)) {
                        throw new \RuntimeException('Required database schema is missing.');
                    }
                }
            } catch (\Throwable) {
                // /up and its logs must not reveal SQL connection credentials.
                throw new \RuntimeException('Database is unavailable.');
            }
        });

        if ($this->app->environment('production')) {
            // Email links use the operator's canonical URL, never an untrusted Host header.
            URL::forceRootUrl(config('app.url'));
            URL::forceScheme(parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https');
        }
    }
}
