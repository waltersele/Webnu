<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileWizardController extends Controller
{
    public function dismiss(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->profile_wizard_dismissed_at = now();
            $user->save();
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('admin.dashboard');
    }
}
