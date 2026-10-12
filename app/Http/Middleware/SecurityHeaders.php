<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $scripts = "'self'";
        $connections = "'self'";
        // Vite's development server stays loopback-only. Production never needs it.
        if (app()->environment('local') && is_file(public_path('hot'))) {
            $scripts .= ' http://localhost:5173 http://127.0.0.1:5173';
            $connections .= ' http://localhost:5173 http://127.0.0.1:5173 ws://localhost:5173 ws://127.0.0.1:5173';
        }

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'", "script-src {$scripts}",
            "style-src 'self' 'unsafe-inline'", "img-src 'self' data:",
            "font-src 'self'", "connect-src {$connections}", "object-src 'none'",
            "base-uri 'self'", "form-action 'self'", "frame-ancestors 'none'",
        ]));
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        // Round state and private account pages must never enter a shared cache.
        $response->headers->set('Cache-Control', 'private, no-store');
        // Clear the retired cookie that older releases used to store a raw password.
        if ($request->cookies->has('remember_user')) {
            $response->headers->setCookie(cookie()->forget('remember_user'));
        }
        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}
