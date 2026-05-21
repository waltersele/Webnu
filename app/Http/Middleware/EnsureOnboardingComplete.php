<?php

namespace App\Http\Middleware;

use Closure;

class EnsureOnboardingComplete
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        if ($user->onboarding_completed_at) {
            return $next($request);
        }

        if ($request->routeIs(
            'admin.onboarding',
            'admin.onboarding.*',
            'admin.menu-scan.*',
            'admin.companies.languages',
            'admin.companies.languages.*',
            'admin.billing',
            'admin.billing.*',
            'admin.settings',
            'admin.settings.*'
        )) {
            return $next($request);
        }

        return redirect()->route('admin.onboarding');
    }
}
