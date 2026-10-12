<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->attributes->get('player')) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Connecte-toi pour continuer.'], 401)
                : redirect('/user/connection');
        }

        return $next($request);
    }
}
