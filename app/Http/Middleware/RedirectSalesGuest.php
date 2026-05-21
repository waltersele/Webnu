<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectSalesGuest
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && $request->user()->isSalesRep()) {
            return redirect()->route('sales.dashboard');
        }

        return $next($request);
    }
}
