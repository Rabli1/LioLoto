<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
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
    public function boot()
    {
        View::composer('*', function ($view) {
            $usersPath = database_path('json/users.json');
            $users = [];

            if (is_file($usersPath) && is_readable($usersPath)) {
                $contents = file_get_contents($usersPath);

                if ($contents !== false) {
                    $decoded = json_decode($contents, true);

                    if (is_array($decoded)) {
                        $users = $decoded;
                    }
                }
            }

            $view->with('users', $users);
        });
    }
}