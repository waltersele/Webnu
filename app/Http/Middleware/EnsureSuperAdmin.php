<?php

namespace App\Http\Middleware;

use App\User;
use Closure;

class EnsureSuperAdmin
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        $impersonatorId = (int) $request->session()->get('impersonator_id', 0);
        if ($impersonatorId > 0) {
            $impersonator = User::find($impersonatorId);
            if ($impersonator && $impersonator->isSuperAdmin()) {
                return $next($request);
            }
        }

        abort(403, 'No tienes acceso a la plataforma.');
    }
}
