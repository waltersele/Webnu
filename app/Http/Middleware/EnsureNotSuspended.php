<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureNotSuspended
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || ! $user->isSuspended()) {
            return $next($request);
        }

        if ($request->session()->has('impersonator_id')) {
            return $next($request);
        }

        if ($request->routeIs(
            'admin.settings.account',
            'account.suspended',
            'logout',
            'admin.billing',
            'admin.billing.portal'
        )) {
            return $next($request);
        }

        return redirect()->route('account.suspended');
    }
}
