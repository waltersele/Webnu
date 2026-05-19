<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;

class AdminController extends Controller
{
    public function index()
    {
        if (auth()->user() && auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.platform.dashboard');
        }

        $dashboardCompany = null;

        $selectedId = Cookie::get('selected_company');
        if ($selectedId && auth()->check()) {
            $dashboardCompany = Company::where('user_id', auth()->id())
                ->where('id', (int) $selectedId)
                ->first();
        }

        return view('admin.dashboard', compact('dashboardCompany'));
    }
}
