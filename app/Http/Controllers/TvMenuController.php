<?php

namespace App\Http\Controllers;

use App\Company;
use App\Services\MenuService;
use App\Services\MenuSyncService;
use App\Services\Tv\TvTemplateRegistry;
use App\Services\TvMenuPresenter;
use Illuminate\Http\Request;

class TvMenuController extends Controller
{
    public function show(
        MenuService $menuService,
        TvMenuPresenter $presenter,
        Request $request,
        string $companySlug,
        ?string $layout = null
    ) {
        $company = Company::where('slug', $companySlug)->with('user')->first();

        if (! $company) {
            abort(404);
        }

        $isOwnerPreview = $request->boolean('preview')
            && auth()->check()
            && (int) optional($company->user)->id === (int) auth()->id();

        if (! $company->enabled && ! $isOwnerPreview) {
            abort(404);
        }

        if ((int) $company->menu_type === 2) {
            if ($company->menu_type_2_pdf) {
                return redirect(url('img/' . ltrim($company->menu_type_2_pdf, '/')));
            }

            abort(404);
        }

        $registry = app(TvTemplateRegistry::class);
        $layout = $registry->resolveLayout($layout);

        $locale = $menuService->resolveMenuLocale($request, $company);
        $data = $presenter->present($company, $layout, $menuService, $locale);

        $viewName = $registry->viewForLayout($layout);
        if (! view()->exists($viewName)) {
            abort(404);
        }

        return view($viewName, $data);
    }

    /**
     * Ping ligero para modo reproductor: recarga la TV cuando cambia la carta en Webnu.
     */
    public function sync(MenuSyncService $menuSync, Request $request, string $companySlug)
    {
        $company = Company::where('slug', $companySlug)->where('enabled', true)->first();

        if (! $company || (int) $company->menu_type !== 1) {
            return response()->json(['error' => 'not_found'], 404);
        }

        $version = $menuSync->syncVersion($company);
        $clientVersion = $request->header('X-Sync-Version') ?: $request->query('v');

        if ($clientVersion && $clientVersion === $version) {
            return response()->json(null, 304)->header('X-Sync-Version', $version);
        }

        return response()->json([
            'slug' => $company->slug,
            'sync_version' => $version,
            'layout' => $request->query('layout'),
        ])->header('X-Sync-Version', $version);
    }
}
