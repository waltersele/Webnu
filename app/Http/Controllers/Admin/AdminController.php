<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function index()
    {
        if (auth()->user() && auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.platform.dashboard');
        }

        return redirect()->route('admin.tvpik.index');
    }
}
