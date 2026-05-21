<?php

namespace App\Http\Middleware;

use Closure;

class EnsureSalesRep
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $user->isSalesRep()) {
            if ($request->expectsJson()) {
                abort(403, 'Acceso solo para equipo comercial.');
            }

            return redirect()->route('sales.login');
        }

        return $next($request);
    }
}
