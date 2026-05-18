<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cookie;
use App\Company;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $user;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            if($this->user)
            {
                if(Cookie::get('selected_company')){
                    $value = Cookie::get('selected_company');
                    View::share('selected_company', $value);

                    $userId = auth()->id();
                    $companies = Company::where('user_id', $userId)->orderBy('name')->get();
                    View::share('available_companies', $companies);
                }
            }

            return $next($request);
        });
    }

    public function user()
    {
        return Auth::user();
    }
}
