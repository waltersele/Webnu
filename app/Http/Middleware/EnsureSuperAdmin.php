<?php

namespace App\Http\Middleware;

use Closure;

class EnsureSuperAdmin
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $user->isSuperAdmin()) {
            abort(403, 'No tienes acceso a la plataforma.');
        }

        return $next($request);
    }
}
