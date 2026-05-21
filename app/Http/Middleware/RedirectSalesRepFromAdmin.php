<?php

namespace App\Http\Middleware;

use Closure;

class RedirectSalesRepFromAdmin
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isSalesRep() && ! $user->isSuperAdmin()) {
            return redirect()->route('sales.dashboard');
        }

        return $next($request);
    }
}
