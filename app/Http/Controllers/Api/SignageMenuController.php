<?php

namespace App\Http\Controllers\Api;

use App\Company;
use App\Http\Controllers\Controller;
use App\Services\MenuSyncService;
use Illuminate\Http\Request;

class SignageMenuController extends Controller
{
    protected $menuSync;

    public function __construct(MenuSyncService $menuSync)
    {
        $this->menuSync = $menuSync;
    }

    public function index(Request $request)
    {
        return response()->json([
            'api_version' => config('digital_signage.api_version'),
            'menus' => $this->menuSync->companiesPayload($request->user()->id),
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $company = $this->findOwnedCompany($request, $slug);

        if (!$company->enabled) {
            return response()->json([
                'message' => 'Este negocio está desactivado.',
            ], 403);
        }

        $payload = $this->menuSync->menuPayload($company);
        $clientVersion = $request->header('X-Sync-Version');

        if ($clientVersion && $clientVersion === $payload['sync_version']) {
            return response()->json(null, 304);
        }

        return response()
            ->json($payload)
            ->header('X-Sync-Version', $payload['sync_version']);
    }

    public function version(Request $request, string $slug)
    {
        $company = $this->findOwnedCompany($request, $slug);

        $syncVersion = $this->menuSync->syncVersion($company);

        return response()->json([
            'slug' => $company->slug,
            'sync_version' => $syncVersion,
            'menu_type' => (int) $company->menu_type,
            'enabled' => (bool) $company->enabled,
        ])->header('X-Sync-Version', $syncVersion);
    }

    protected function findOwnedCompany(Request $request, string $slug): Company
    {
        return Company::where('user_id', $request->user()->id)
            ->where('slug', $slug)
            ->firstOrFail();
    }
}
