<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user
            && $user->two_factor_enabled
            && !session('2fa_verified')
            && !$request->routeIs('2fa.*', 'logout')
        ) {
            return redirect()->route('2fa.verify');
        }

        return $next($request);
    }
}
