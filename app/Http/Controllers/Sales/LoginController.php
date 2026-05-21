<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Services\Sales\SalesLeadService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/comercial';

    public function __construct()
    {
        $this->middleware('sales.guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('sales.login');
    }

    protected function authenticated(Request $request, $user)
    {
        if (! $user->isSalesRep()) {
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('sales.login')
                ->withErrors(['email' => 'Esta cuenta no tiene acceso comercial.']);
        }

        $leads = app(SalesLeadService::class);

        $intended = $request->session()->pull('url.intended');
        if ($intended && $intended !== url('/comercial') && $intended !== route('sales.dashboard')) {
            return redirect()->to($intended);
        }

        return redirect()->to($leads->importEntryUrl($user));
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('sales.login');
    }
}
