<?php

namespace App\Http\Middleware;

use Closure;

class EnsureSubscribed
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Freemium: el plan Gratis puede usar el panel con límites (ver UserPlanService).
        // Freemium: usuarios sin suscripción de pago usan el plan Gratis con límites.
        return $next($request);
    }
}
