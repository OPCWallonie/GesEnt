<?php

namespace App\Http\Middleware;

use App\Models\ParametresEntreprise;
use Closure;
use Illuminate\Http\Request;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // 1. 2FA activée : vérifier si l'utilisateur a déjà passé la vérification
        if ($user->two_factor_enabled && !session('2fa_verified')
            && !$request->routeIs('2fa.*', 'logout')
        ) {
            return redirect()->route('2fa.verify');
        }

        // 2. 2FA obligatoire (paramètre entreprise) : forcer la configuration si pas encore activée
        if (!$user->two_factor_enabled
            && !$request->routeIs('2fa.*', 'logout', 'profile.*')
            && ParametresEntreprise::instance()->deux_facteurs_obligatoires
        ) {
            return redirect()->route('2fa.setup')
                ->with('warning', 'La double authentification est obligatoire. Veuillez la configurer avant de continuer.');
        }

        return $next($request);
    }
}
