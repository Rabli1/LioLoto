<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\UserServices;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RefreshSessionUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('user_id') && Auth::user()) {
            app(UserServices::class)->rememberInSession(Auth::user());
        }
        if ($request->session()->has('user_id')) {
            $user = User::find($request->session()->get('user_id'));
            $version = $request->session()->get('credential_version');
            if (!$user || $user->banned || !$user->confirmed || !is_string($version)
                || !hash_equals(hash('sha256', $user->password), $version)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            } else {
                app(UserServices::class)->rememberInSession($user);
                $request->attributes->set('player', $user);
            }
        } else {
            $request->session()->forget('user');
        }

        return $next($request);
    }
}
