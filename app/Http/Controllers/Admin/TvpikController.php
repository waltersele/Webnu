<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Controller;
use App\Menu;
use App\Services\MenuSyncService;
use App\Services\Tvpik\TvpikApiClient;
use App\Services\Tvpik\TvpikPublishService;
use App\Services\UserPlanService;
use App\TvpikScreenLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TvpikController extends Controller
{
    public function index(
        MenuSyncService $menuSync,
        TvpikApiClient $tvpikApi,
        UserPlanService $plans,
        Request $request
    ) {
        $user = auth()->user();

        if (! $user->api_token) {
            $user->api_token = Str::random(80);
            $user->save();
        }

        $canTvpik = $plans->canUseTvpik($user);
        $company = $request->attributes->get('selected_company')
            ?? $user->companies()->orderBy('updated_at', 'desc')->first();

        $screens = [];
        $screensError = null;

        if ($canTvpik && $user->isTvpikConnected()) {
            try {
                $result = $tvpikApi->listScreens($user);
                $screens = $result['screens'];
            } catch (\Throwable $e) {
                $screensError = $e->getMessage();
            }
        }

        $links = TvpikScreenLink::where('user_id', $user->id)
            ->with('company')
            ->orderBy('tvpik_screen_name')
            ->get()
            ->keyBy('tvpik_screen_id');

        $templates = config('tvpik_templates.templates', []);
        $menus = $menuSync->companiesPayload($user->id);

        $companies = $user->companies()->orderBy('name')->get();
        $menusByCompany = Menu::where('enabled', true)
            ->whereIn('company_id', $companies->pluck('id'))
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->groupBy('company_id');

        return view('admin.tvpik.index', [
            'canTvpik' => $canTvpik,
            'apiToken' => $user->api_token,
            'appKeyConfigured' => ! empty(config('digital_signage.app_key')),
            'tvpikConnected' => $user->isTvpikConnected(),
            'tvpikWebUrl' => config('tvpik.web_app_url'),
            'tvpikApiConfigured' => $tvpikApi->isConfigured(),
            'screens' => $screens,
            'screensError' => $screensError,
            'links' => $links,
            'templates' => $templates,
            'menus' => $menus,
            'menusByCompany' => $menusByCompany,
            'company' => $company,
            'companies' => $companies,
            'planFeatures' => $plans->featureFlags($user),
        ]);
    }

    public function connect(Request $request, TvpikApiClient $tvpikApi)
    {
        $this->authorizeTvpik();

        $validated = $request->validate([
            'tvpik_token' => 'required|string|min:8|max:500',
        ]);

        $user = auth()->user();

        if (! $user->api_token) {
            $user->api_token = Str::random(80);
        }

        try {
            $result = $tvpikApi->connect($user, $validated['tvpik_token']);
        } catch (\Throwable $e) {
            return back()->withErrors(['tvpik_token' => $e->getMessage()]);
        }

        $user->tvpik_api_token = $validated['tvpik_token'];
        $user->tvpik_connected_at = now();
        $user->tvpik_org_id = $result['org_id'] ?? null;
        $user->save();

        return redirect()
            ->route('admin.tvpik.index')
            ->with('flash', 'Cuenta TVPik conectada correctamente.');
    }

    public function disconnect()
    {
        $this->authorizeTvpik();

        $user = auth()->user();
        $user->tvpik_api_token = null;
        $user->tvpik_connected_at = null;
        $user->tvpik_org_id = null;
        $user->save();

        return redirect()
            ->route('admin.tvpik.index')
            ->with('flash', 'Desconectado de TVPik.');
    }

    public function publish(Request $request, TvpikPublishService $publishService)
    {
        $this->authorizeTvpik();

        $validated = $request->validate([
            'screen_id' => 'required|string|max:64',
            'screen_name' => 'nullable|string|max:255',
            'company_id' => 'required|integer',
            'template_key' => ['required', 'string', Rule::in(array_keys(config('tvpik_templates.templates', [])))],
            'gallery_id' => 'nullable|string|max:64',
            'menu_id' => 'nullable|integer|exists:menus,id',
        ]);

        $user = auth()->user();
        $company = Company::where('user_id', $user->id)->findOrFail($validated['company_id']);

        if ((int) $company->menu_type !== 1) {
            return back()->withErrors(['company_id' => 'Solo las cartas digitales pueden publicarse en TV.']);
        }

        $menuId = null;
        if (! empty($validated['menu_id'])) {
            $menu = Menu::where('id', $validated['menu_id'])
                ->where('company_id', $company->id)
                ->first();
            if ($menu) {
                $menuId = $menu->id;
            }
        }

        try {
            $publishService->publishScreen(
                $user,
                $company,
                $validated['screen_id'],
                $validated['screen_name'] ?? $validated['screen_id'],
                $validated['template_key'],
                $validated['gallery_id'] ?? null,
                $menuId
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            return back()->withErrors(['publish' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.tvpik.index')
            ->with('flash', 'Carta publicada en la pantalla TVPik.');
    }

    public function publishAll(TvpikPublishService $publishService, Request $request)
    {
        $this->authorizeTvpik();

        $user = auth()->user();
        $company = $request->attributes->get('selected_company');

        if (! $company) {
            return back()->withErrors(['publish' => 'Selecciona un negocio.']);
        }

        $count = $publishService->publishAllForCompany($user, $company);

        return redirect()
            ->back()
            ->with('flash', $count > 0
                ? "Republicado en {$count} pantalla(s) TV."
                : 'No hay pantallas vinculadas a este negocio.');
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|integer',
            'template_key' => ['required', 'string', Rule::in(array_keys(config('tvpik_templates.templates', [])))],
            'menu_id' => 'nullable|integer',
        ]);

        $company = Company::where('user_id', auth()->id())->findOrFail($validated['company_id']);
        $template = config('tvpik_templates.templates.' . $validated['template_key']);
        $layout = $template['layout'] ?? 'menu';

        $params = [
            'companySlug' => $company->slug,
            'layout' => $layout,
            'preview' => 1,
        ];
        if (! empty($validated['menu_id'])) {
            $params['menu'] = (int) $validated['menu_id'];
        }

        return redirect()->route('tv.show.layout', $params);
    }

    /**
     * Modo reproductor: pantalla a pantalla completa para HDMI o Cast de pestaña.
     * El control sigue en Webnu (Mi carta / TVPik); la TV solo muestra esta URL.
     */
    public function player(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|integer',
            'template_key' => ['required', 'string', Rule::in(array_keys(config('tvpik_templates.templates', [])))],
            'menu_id' => 'nullable|integer',
        ]);

        $company = Company::where('user_id', auth()->id())->findOrFail($validated['company_id']);
        $template = config('tvpik_templates.templates.' . $validated['template_key']);
        $layout = $template['layout'] ?? 'menu';

        $params = [
            'companySlug' => $company->slug,
            'layout' => $layout,
            'player' => 1,
        ];
        if (! empty($validated['menu_id'])) {
            $params['menu'] = (int) $validated['menu_id'];
        }

        return redirect()->route('tv.show.layout', $params);
    }

    protected function authorizeTvpik(): void
    {
        $user = auth()->user();
        if (! app(UserPlanService::class)->canUseTvpik($user)) {
            abort(403, 'El plan Ilimitado incluye TVPik.');
        }
    }
}
