<?php

namespace App\Http\Controllers\Auth;

use App\Company;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\View;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->withCookie(Cookie::forget('selected_company'));
    }

    protected function authenticated(Request $request, $user)
    {
        $ownedCompanies = Company::where('user_id', $user->id)->orderBy('name')->get();
        $selectedId = Cookie::get('selected_company');

        if ($selectedId && $ownedCompanies->contains('id', (int) $selectedId)) {
            $value = $selectedId;
        } elseif ($ownedCompanies->isNotEmpty()) {
            $value = $ownedCompanies->first()->id;
            Cookie::queue(Cookie::forever('selected_company', $value));
        } else {
            $value = null;
            Cookie::queue(Cookie::forget('selected_company'));
        }

        View::share('selected_company', $value);
        View::share('available_companies', $ownedCompanies);
    }
}
