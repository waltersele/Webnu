<?php

namespace App\Http\Controllers;

use App\Company;
use App\PlatformSetting;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PagesController extends Controller
{
    public function index()
    {
        return view('landing-preview', [
            'contactPublicEmail' => PlatformSetting::contactPublicEmail(),
        ]);
    }

    public function landingPreview()
    {
        return redirect()->route('home', [], 301);
    }

    public function see_menu($companySlug, MenuService $menuService, Request $request)
    {
        $company = Company::where('slug', $companySlug)->with('user')->first();

        if (!$company) {
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

            return view($viewName, compact('company', 'sections', 'menuLocale', 'menuLocaleService'));
        }

        return view('menu_pdf', compact('company'));
    }

    protected function recordMenuView(Company $company, Request $request): void
    {
        if ($request->boolean('studio_preview')) {
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
