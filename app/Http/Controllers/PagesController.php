<?php

namespace App\Http\Controllers;

use App\Company;
use App\Http\Controllers\Concerns\PreparesLandingPage;
use App\PlatformSetting;
use App\Services\MenuService;
use App\Services\UserPlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PagesController extends Controller
{
    use PreparesLandingPage;

    public function index(Request $request)
    {
        $locale = $this->resolveLandingLocale($request);
        $data = array_merge(
            $this->landingViewData($request),
            ['contactPublicEmail' => PlatformSetting::contactPublicEmail()]
        );

        $response = response()->view('landing-preview', $data);
        $cookieName = config('landing.cookie_name', 'webnu_landing_lang');

        return $response->cookie($cookieName, $locale, 60 * 24 * 365, null, null, false, false);
    }

    public function landingPreview()
    {
        return redirect()->route('home', [], 301);
    }

    public function see_menu(MenuService $menuService, Request $request, $ownerSlug, $companySlug = null)
    {
        // Compatibilidad: si solo se recibe un argumento (uso legacy directo),
        // tratamos el primero como companySlug y dejamos ownerSlug a null.
        if ($companySlug === null) {
            $companySlug = $ownerSlug;
            $ownerSlug = null;
        }

        $query = Company::where('slug', $companySlug);
        if ($ownerSlug !== null && $companySlug !== 'demo') {
            $query->whereHas('user', function ($q) use ($ownerSlug) {
                $q->where('slug', $ownerSlug);
            });
        }
        $company = $query->with('user')->first();

        if (! $company) {
            abort(404);
        }

        $previewToken = $request->get('preview_token');
        $validPreviewToken = $previewToken && $company->isValidPreviewToken($previewToken);

        if (! $company->enabled && ! $request->boolean('studio_preview') && ! $request->boolean('sales_demo') && ! $validPreviewToken) {
            // Si la carta está despublicada pero tiene menús activos,
            // dirigimos al hub del owner para que el cliente pueda verlos.
            $hasActiveMenus = $company->menus()->where('enabled', true)->exists();
            $resolvedOwner = $ownerSlug ?: optional($company->user)->resolveSlug();
            if ($hasActiveMenus && $resolvedOwner) {
                return redirect()->route('public.hub', ['slug' => $resolvedOwner], 302);
            }
            abort(404);
        }

        // Si la carta tiene activado "combinar todos los menús en una sola página",
        // y existen menús activos, renderizamos la vista combinada (tabs por menú).
        if ($company->combine_menus && $company->menus()->where('enabled', true)->exists()
            && ! $request->boolean('studio_preview') && ! $request->boolean('sales_demo')
        ) {
            $menus = $company->menus()
                ->where('enabled', true)
                ->orderBy('position')
                ->with(['sections' => function ($q) {
                    $q->orderBy('position');
                }, 'sections.items' => function ($q) {
                    $q->orderBy('position');
                }, 'sections.items.product'])
                ->get();

            $showWebnuBadge = $company->user
                ? app(UserPlanService::class)->shouldShowWebnuBadge($company->user)
                : false;

            return view('themes.menus-combined', [
                'company' => $company,
                'menus' => $menus,
                'ownerSlug' => $ownerSlug ?: optional($company->user)->resolveSlug(),
                'showWebnuBadge' => $showWebnuBadge,
            ]);
        }

        if ($companySlug === 'demo' && $request->filled('tpl')) {
            $allowed = array_keys(config('company_templates.templates', []));
            $tpl = $request->get('tpl');
            if (in_array($tpl, $allowed, true)) {
                $company->template = $tpl;
            }
        }

        $this->recordMenuView($company, $request);

        if ($company->menu_type == 1) {
            $company = $menuService->applyStudioPreview($company, request());
            $menuLocale = $menuService->resolveMenuLocale($request, $company);
            $sections = $menuService->sectionsForCompany($company, $menuLocale);
            $viewName = $menuService->themeViewName($company);
            $menuLocaleService = app(\App\Services\MenuLocaleService::class);
            $dailyHighlights = $menuService->dailyHighlightsForCompany($company, $menuLocale);
            $showWebnuBadge = $company->user
                ? app(UserPlanService::class)->shouldShowWebnuBadge($company->user)
                : false;

            return view($viewName, compact('company', 'sections', 'menuLocale', 'menuLocaleService', 'dailyHighlights', 'showWebnuBadge'));
        }

        if ($company->menu_type != 2 || empty($company->menu_type_2_pdf)) {
            abort(404);
        }

        $showWebnuBadge = $company->user
            ? app(UserPlanService::class)->shouldShowWebnuBadge($company->user)
            : false;

        return view('menu_pdf', compact('company', 'showWebnuBadge'));
    }

    protected function recordMenuView(Company $company, Request $request): void
    {
        if ($request->boolean('studio_preview') || $request->boolean('sales_demo')) {
            return;
        }

        Company::where('id', $company->id)->increment('menu_views');
    }

    /**
     * Hub público de un negocio: lista todas las cartas activas y todos los menús
     * activos del owner. Si solo hay 1 carta y 0 menús, redirige directo a la carta.
     */
    public function ownerHub($ownerSlug)
    {
        $user = \App\User::where('slug', $ownerSlug)->first();
        if (! $user) {
            abort(404);
        }

        // Traemos TODAS las companies (enabled o no) con sus menús activos.
        // Las cartas no publicadas siguen siendo invisibles como "tarjeta"
        // en el hub, pero sus menús activos sí se listan (independencia entre
        // visibilidad de carta y visibilidad de menú).
        $companies = $user->companies()
            ->orderBy('name')
            ->with(['menus' => function ($q) {
                $q->where('enabled', true)->orderBy('position');
            }])
            ->get();

        $activeCompanies = $companies->where('enabled', true)->values();
        // Reasignamos la relación inversa: como ya tenemos la company padre del
        // menú en mano (viene de $c->menus), evitamos un lazy-load por cada
        // menú al pintar el hub o resolver el redirect a /carta/{owner}/{c}/{m}.
        $menus = $companies->flatMap(function ($c) {
            return $c->menus->map(function ($m) use ($c) {
                $m->setRelation('company', $c);
                return $m;
            });
        })->values();

        if ($activeCompanies->isEmpty() && $menus->isEmpty()) {
            abort(404);
        }

        // 1 carta activa, 0 menús: el hub es ruido, redirigir a la carta.
        if ($activeCompanies->count() === 1 && $menus->isEmpty()) {
            return redirect()->route('see_menu', [
                'ownerSlug' => $ownerSlug,
                'companySlug' => $activeCompanies->first()->slug,
            ], 302);
        }

        // 0 cartas activas, 1 menú activo: redirigir directo al menú.
        if ($activeCompanies->isEmpty() && $menus->count() === 1) {
            $only = $menus->first();
            return redirect()->route('public.menu', [
                'ownerSlug' => $ownerSlug,
                'companySlug' => $only->company->slug,
                'menuSlug' => $only->slug,
            ], 302);
        }

        // Empresa "marca" para heredar paleta/tipografía/logo en el hub.
        $brandCompany = $activeCompanies->first() ?: ($menus->first()->company ?? $companies->first());
        $showWebnuBadge = $brandCompany->user
            ? app(UserPlanService::class)->shouldShowWebnuBadge($brandCompany->user)
            : false;

        return view('themes.hub', [
            'user' => $user,
            'ownerSlug' => $ownerSlug,
            'companies' => $activeCompanies,
            'menus' => $menus,
            'brandCompany' => $brandCompany,
            'showWebnuBadge' => $showWebnuBadge,
        ]);
    }

    /**
     * Vista pública de un menú concreto del negocio.
     */
    public function seeMenu($ownerSlug, $companySlug, $menuSlug)
    {
        $company = Company::where('slug', $companySlug)
            ->whereHas('user', function ($q) use ($ownerSlug) {
                $q->where('slug', $ownerSlug);
            })
            ->with('user')
            ->first();

        if (! $company) {
            abort(404);
        }

        $menu = $company->menus()
            ->where('slug', $menuSlug)
            ->where('enabled', true)
            ->with(['sections' => function ($q) {
                $q->orderBy('position');
            }, 'sections.items' => function ($q) {
                $q->orderBy('position');
            }, 'sections.items.product'])
            ->first();

        if (! $menu) {
            abort(404);
        }

        $showWebnuBadge = $company->user
            ? app(UserPlanService::class)->shouldShowWebnuBadge($company->user)
            : false;

        return view('themes.menu', compact('company', 'menu', 'ownerSlug', 'showWebnuBadge'));
    }

    public function te_llamamos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->to(url('/#contacto'))
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        try {
            Mail::send('emails.message', $data, function ($message) use ($data) {
                $message->from(PlatformSetting::mailFromAddress(), PlatformSetting::mailFromName())
                    ->to(PlatformSetting::contactLeadsEmail(), PlatformSetting::mailFromName())
                    ->replyTo($data['email'], $data['name'])
                    ->subject('Webnu - Te llamamos');
            });
        } catch (\Exception $e) {
            report($e);

            return redirect()
                ->to(url('/#contacto'))
                ->with('te-llamamos-failure', 'Se ha producido un error al enviar el mensaje');
        }

        return redirect()
            ->to(url('/#contacto'))
            ->with('te-llamamos-ok', 'El mensaje ha sido enviado correctamente. Te llamaremos en breve.');
    }

    public function suggestion(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:3000',
        ]);

        try {
            Mail::send('emails.suggestion', $data, function ($message) use ($data) {
                $message->from(PlatformSetting::mailFromAddress(), PlatformSetting::mailFromName())
                    ->to(PlatformSetting::contactSuggestionsEmail(), PlatformSetting::mailFromName())
                    ->replyTo($data['email'], $data['name'])
                    ->subject('Sugerencia para Webnu — ' . $data['name']);
            });
        } catch (\Exception $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'No se pudo enviar la sugerencia. Inténtalo de nuevo.'], 500);
            }

            return redirect()
                ->to(url('/#funciones'))
                ->with('suggestion-failure', 'No se pudo enviar la sugerencia. Inténtalo de nuevo.');
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => '¡Gracias! Hemos recibido tu sugerencia.']);
        }

        return redirect()
            ->to(url('/#funciones'))
            ->with('suggestion-ok', '¡Gracias! Hemos recibido tu sugerencia.');
    }

    public function table_reservation(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'company_email' => 'nullable|email',
            'date' => 'nullable|date',
            'hour' => 'nullable|string|max:20',
        ]);

        if (empty($data['company_email'])) {
            return back()->with('table-reservation-failure', 'Este restaurante no tiene email de contacto configurado.');
        }

        try {
            Mail::send('emails.table-reservation-message', $data, function ($message) use ($data) {
                $message->from(PlatformSetting::mailFromAddress(), PlatformSetting::mailFromName())
                    ->to($data['company_email'])
                    ->replyTo($data['email'], $data['name'])
                    ->subject('Webnu - Reserva de mesa');
            });
        } catch (\Exception $e) {
            report($e);

            return back()->with('table-reservation-failure', 'Se ha producido un error al enviar la reserva');
        }

        return back()->with('table-reservation-ok', 'Reserva enviada correctamente, le llamaremos para confirmar su reserva.');
    }
}
