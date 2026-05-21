<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MenuSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SignageIntegrationController extends Controller
{
    public function index(MenuSyncService $menuSync)
    {
        $user = auth()->user();

        if (!$user->api_token) {
            $user->api_token = Str::random(80);
            $user->save();
        }

        $menus = $menuSync->companiesPayload($user->id);

        return view('admin.signage.index', [
            'apiToken' => $user->api_token,
            'menus' => $menus,
            'appKeyConfigured' => !empty(config('digital_signage.app_key')),
        ]);
    }

    public function regenerateToken(Request $request)
    {
        $user = auth()->user();
        $user->api_token = Str::random(80);
        $user->save();

        return redirect()
            ->route('admin.tvpik.index')
            ->with('flash', 'Token de API regenerado. Actualízalo en tus integraciones conectadas.');
    }
}
