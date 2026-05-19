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

        if ($user->isSuperAdmin() || $user->hasActiveSubscription()) {
            return $next($request);
        }

        return redirect()
            ->route('admin.billing')
            ->with('flash_warning', 'Tu suscripción no está activa. Actualiza el pago para continuar.');
    }
}
