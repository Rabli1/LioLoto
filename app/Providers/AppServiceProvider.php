<?php

namespace App\Providers;

use App\Services\UserServices;
use Illuminate\Support\Facades\Log;
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
    public function boot(UserServices $userServices): void
    {
        View::composer('*', function ($view) use ($userServices) {
            $users = [];

            try {
                $users = $userServices->all()->toArray();
            } catch (\Throwable $exception) {
                Log::warning('Unable to share users with views.', [
                    'exception' => $exception,
                ]);
            }

            $view->with('users', $users);
        });
    }
}