<?php

namespace App\Http\Controllers;

use App\Company;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PagesController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function landingPreview()
    {
        return view('landing-preview', [
            'landingPayload' => [
                'csrfToken' => csrf_token(),
                'routes' => [
                    'home' => route('home'),
                    'login' => route('login'),
                    'subscribe' => route('process_subscription'),
                    'teLlamamos' => route('te_llamamos'),
                ],
                'assets' => [
                    'logo' => asset('img/front/logo.png'),
                    'clients' => asset('img/front/actual-clients.jpg'),
                    'mockVideo' => asset('img/front/mockup-xd.mp4'),
                    'mock1' => asset('img/front/mock-1.png'),
                    'mockMenu' => asset('img/front/mock-menu-front.png'),
                    'tvpikBar' => asset('img/front/tvpik-bar.jpg'),
                    'tvpikDish' => asset('img/front/tvpik-dish.jpg'),
                    'screenshots' => [
                        asset('img/front/screenshot/screenshot1.jpg'),
                        asset('img/front/screenshot/screenshot2.jpg'),
                        asset('img/front/screenshot/screenshot3.jpg'),
                        asset('img/front/screenshot/screenshot4.jpg'),
                        asset('img/front/screenshot/screenshot5.jpg'),
                    ],
                ],
                'demos' => [
                    ['label' => 'Ver carta digital', 'url' => 'https://webnu.es/carta/webnu-test'],
                    ['label' => 'Ver carta digital PDF', 'url' => 'https://webnu.es/carta/la-ibense'],
                ],
            ],
        ]);
    }

    public function see_menu($companySlug, MenuService $menuService)
    {
        $company = Company::where('slug', $companySlug)->first();

        if (!$company) {
            abort(404);
        }

        $this->recordMenuView($company, request());

        if ($company->menu_type == 1) {
            $company = $menuService->applyStudioPreview($company, request());
            $sections = $menuService->sectionsForCompany($company);
            $viewName = $menuService->themeViewName($company);

            return view($viewName, compact('company', 'sections'));
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
                ->to(url('/#contact'))
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        try {
            Mail::send('emails.message', $data, function ($message) use ($data) {
                $message->from('info@webnu.es', 'Webnu')
                    ->to('info@webnu.es', 'Webnu')
                    ->replyTo($data['email'], $data['name'])
                    ->subject('Webnu - Te llamamos');
            });
        } catch (\Exception $e) {
            report($e);

            return redirect()
                ->to(url('/#contact'))
                ->with('te-llamamos-failure', 'Se ha producido un error al enviar el mensaje');
        }

        return redirect()
            ->to(url('/#contact'))
            ->with('te-llamamos-ok', 'El mensaje ha sido enviado correctamente. Te llamaremos en breve.');
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
                $message->from('info@webnu.es', 'Webnu')
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
