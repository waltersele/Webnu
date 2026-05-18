<?php

namespace App\Http\Controllers;

use App\Company;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PagesController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function see_menu($companySlug, MenuService $menuService)
    {
        $company = Company::where('slug', $companySlug)->first();

        if (!$company) {
            abort(404);
        }

        if ($company->menu_type == 1) {
            $sections = $menuService->sectionsForCompany($company);
            $viewName = $menuService->themeViewName($company);

            return view($viewName, compact('company', 'sections'));
        }

        return view('menu_pdf', compact('company'));
    }

    public function te_llamamos(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
        ]);

        try {
            Mail::send('emails.message', $data, function ($message) use ($data) {
                $message->from('info@webnu.es', 'Webnu')
                    ->to('info@webnu.es', 'Webnu')
                    ->replyTo($data['email'], $data['name'])
                    ->subject('Webnu - Te llamamos');
            });
        } catch (\Exception $e) {
            report($e);

            return back()->with('te-llamamos-failure', 'Se ha producido un error al enviar el mensaje');
        }

        return back()->with('te-llamamos-ok', 'El mensaje ha sido enviado correctamente.');
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
