<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\SalesHandoff;
use App\Company;
use App\Services\Platform\PlatformSettingsService;
use App\Services\Sales\SalesRepProvisioningService;
use App\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class PlatformSalesController extends Controller
{
    public function index(Request $request, PlatformSettingsService $settings)
    {
        $this->authorize('platform.access');

        $handoffsQuery = SalesHandoff::query()
            ->with(['salesRep:id,name,email', 'company:id,name,slug', 'restaurantUser:id,email'])
            ->orderByDesc('sent_at');

        if ($request->filled('rep')) {
            $handoffsQuery->where('sales_rep_user_id', (int) $request->get('rep'));
        }

        if ($request->filled('from')) {
            $handoffsQuery->whereDate('sent_at', '>=', $request->get('from'));
        }

        if ($request->filled('to')) {
            $handoffsQuery->whereDate('sent_at', '<=', $request->get('to'));
        }

        $handoffs = $handoffsQuery->paginate(30)->appends($request->query());

        $reps = User::role('sales-rep')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'created_at'])
            ->map(function (User $rep) {
                $rep->active_visits_count = Company::query()
                    ->where('sales_rep_user_id', $rep->id)
                    ->whereNull('sales_converted_at')
                    ->count();
                $rep->handoffs_count = SalesHandoff::where('sales_rep_user_id', $rep->id)->count();

                return $rep;
            });

        $metrics = [
            'total' => SalesHandoff::count(),
            'month' => SalesHandoff::where('sent_at', '>=', now()->startOfMonth())->count(),
            'by_rep' => SalesHandoff::query()
                ->selectRaw('sales_rep_user_id, count(*) as total')
                ->groupBy('sales_rep_user_id')
                ->orderByDesc('total')
                ->get()
                ->map(function ($row) {
                    $row->salesRep = User::find($row->sales_rep_user_id);

                    return $row;
                }),
        ];

        return view('admin.platform.sales.index', [
            'handoffs' => $handoffs,
            'reps' => $reps,
            'metrics' => $metrics,
            'salesSettings' => $settings->salesSettingsForForm(),
            'planTiers' => config('plans.tiers', []),
            'availablePlanKeys' => $settings->availablePlanKeys(),
        ]);
    }

    public function update(Request $request, PlatformSettingsService $settings)
    {
        $this->authorize('platform.access');

        $request->validate([
            'sales_handoff_plan_key' => 'required|string|in:' . implode(',', $settings->availablePlanKeys()),
            'sales_handoff_trial_days' => 'required|integer|min:1|max:365',
            'sales_demo_max_photo_products' => 'required|integer|min:1|max:10',
        ]);

        $settings->updateSales($request->only([
            'sales_handoff_plan_key',
            'sales_handoff_trial_days',
            'sales_demo_max_photo_products',
        ]));

        return redirect()
            ->route('admin.platform.sales.index', ['tab' => 'config'])
            ->with('flash', 'Configuración comercial guardada.');
    }

    public function export(Request $request)
    {
        $this->authorize('platform.access');

        $query = SalesHandoff::query()
            ->with(['salesRep:id,name,email', 'company:id,name'])
            ->orderByDesc('sent_at');

        if ($request->filled('rep')) {
            $query->where('sales_rep_user_id', (int) $request->get('rep'));
        }

        if ($request->filled('from')) {
            $query->whereDate('sent_at', '>=', $request->get('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('sent_at', '<=', $request->get('to'));
        }

        $rows = $query->get();

        $filename = 'sales-handoffs-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['fecha', 'comercial', 'restaurante', 'email', 'plan', 'dias_trial', 'estado']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    optional($row->sent_at)->format('Y-m-d H:i'),
                    optional($row->salesRep)->name,
                    optional($row->company)->name,
                    $row->prospect_email,
                    $row->plan_key,
                    $row->trial_days,
                    $row->status,
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function storeRep(Request $request, SalesRepProvisioningService $provisioning)
    {
        $this->authorize('platform.access');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'send_access_email' => 'nullable|boolean',
        ]);

        $sendEmail = $request->boolean('send_access_email', true);

        $email = strtolower(trim($data['email']));
        $alreadyRegistered = User::where('email', $email)->exists();

        try {
            $user = $provisioning->createOrInvite(
                $data['name'],
                $data['email'],
                $sendEmail
            );
        } catch (\Throwable $e) {
            if (! $e instanceof \Illuminate\Validation\ValidationException) {
                return redirect()
                    ->route('admin.platform.sales.index', ['tab' => 'comerciales'])
                    ->withInput()
                    ->withErrors([
                        'email' => 'No se pudo enviar el email. Configura remitente y SMTP en Plataforma → Configuración.',
                    ]);
            }

            throw $e;
        }

        if ($alreadyRegistered) {
            $message = $sendEmail
                ? 'Rol comercial asignado a ' . $user->email . '. Se ha enviado el email para entrar en /comercial.'
                : 'Rol comercial asignado a ' . $user->email . '.';
        } else {
            $message = $sendEmail
                ? 'Comercial creado. Email enviado a ' . $user->email . ' para establecer contraseña (/comercial).'
                : 'Comercial creado (' . $user->email . '). Enlace: ' . route('sales.login');
        }

        return redirect()
            ->route('admin.platform.sales.index', ['tab' => 'comerciales'])
            ->with('flash', $message);
    }

    public function resendRepAccess(User $user, SalesRepProvisioningService $provisioning)
    {
        $this->authorize('platform.access');

        if (! $user->isSalesRep()) {
            return back()->withErrors(['rep' => 'Este usuario no es comercial.']);
        }

        try {
            $provisioning->sendAccessEmail($user);
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.platform.sales.index', ['tab' => 'comerciales'])
                ->withErrors([
                    'rep' => 'No se pudo enviar el email. Revisa el remitente SMTP en Plataforma → Configuración (info@webnu.es). Detalle: ' . $e->getMessage(),
                ]);
        }

        return redirect()
            ->route('admin.platform.sales.index', ['tab' => 'comerciales'])
            ->with('flash', 'Email de acceso enviado a ' . $user->email);
    }

    public function grantSalesRep(User $user, SalesRepProvisioningService $provisioning)
    {
        $this->authorize('platform.access');

        $provisioning->grantSalesRepRole($user);

        return back()->with('flash', 'Rol comercial asignado a ' . $user->email);
    }

    public function revokeSalesRep(User $user)
    {
        $this->authorize('platform.access');

        if ($user->hasRole('sales-rep')) {
            $user->removeRole('sales-rep');
        }

        return redirect()
            ->route('admin.platform.sales.index', ['tab' => 'comerciales'])
            ->with('flash', 'Acceso comercial retirado.');
    }
}
