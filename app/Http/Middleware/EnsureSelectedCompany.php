<?php

namespace App\Http\Middleware;

use App\Company;
use Closure;
use Illuminate\Support\Facades\Cookie;

class EnsureSelectedCompany
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        $ownedCompanyIds = Company::where('user_id', $user->id)->pluck('id');
        $selectedId = Cookie::get('selected_company');

        if (!$selectedId || !$ownedCompanyIds->contains((int) $selectedId)) {
            $firstCompany = Company::where('user_id', $user->id)->orderBy('name')->first();

            if ($firstCompany) {
                Cookie::queue(Cookie::forever('selected_company', $firstCompany->id));
            } else {
                Cookie::queue(Cookie::forget('selected_company'));
            }
        }

        return $next($request);
    }
}
