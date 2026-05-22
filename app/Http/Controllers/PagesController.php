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

    public function see_menu($companySlug, MenuService $menuService, Request $request)
    {
        $company = Company::where('slug', $companySlug)->with('user')->first();

        if (! $company) {
            abort(404);
        }

        if (! $company->enabled && ! $request->boolean('studio_preview') && ! $request->boolean('sales_demo')) {
            abort(404);
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
            'company_email' => 'required|email',
        ]);

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
