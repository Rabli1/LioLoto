<?php

namespace App\Providers;

use App\Services\UserServices;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
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
        if ($this->app->environment('production')) {
            // Email links use the operator's canonical URL, never an untrusted Host header.
            URL::forceRootUrl(config('app.url'));
            URL::forceScheme(parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https');
        }

        View::composer('*', function ($view) use ($userServices) {
            $users = [];

            try {
                $users = User::where('banned', false)->orderByDesc('points')->limit(10)
                    ->get()->map(fn (User $user) => $user->publicProfile())->toArray();
            } catch (\Throwable $exception) {
                Log::warning('Unable to share users with views.', [
                    'exception' => $exception,
                ]);
            }

            if (!array_key_exists('users', $view->getData())) {
                $view->with('users', $users);
            }
        });
    }
}
