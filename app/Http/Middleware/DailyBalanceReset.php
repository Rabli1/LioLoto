<?php

namespace App\Http\Middleware;

use App\Http\Controllers\BalanceResetController;
use Closure;
use Throwable;

class DailyBalanceReset
{
    public function handle($request, Closure $next)
    {
        try {
            BalanceResetController::resetPoint();
        } catch (Throwable $exception) {
            report($exception);
        }

        return $next($request);
    }
}
