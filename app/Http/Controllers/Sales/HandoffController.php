<?php

namespace App\Http\Controllers\Sales;

use App\Company;
use App\Http\Controllers\Controller;
use App\PlatformSetting;
use App\Services\Platform\PlatformSettingsService;
use App\Services\Sales\SalesHandoffService;
use App\Services\Sales\SalesLeadService;
use Illuminate\Http\Request;

class HandoffController extends Controller
{
    public function show(Company $company, SalesLeadService $leads, PlatformSettingsService $settings)
    {
        $visit = $leads->findActiveLeadFor(auth()->user(), $company->id);
        $this->authorize('update', $visit);

        $productCount = $visit->sections()->withCount('products')->get()->sum('products_count');
        if ($productCount < 1) {
            return redirect()
                ->route('sales.visit.show', $visit->id)
                ->withErrors(['handoff' => 'Importa la carta antes de enviar el acceso.']);
        }

        return view('sales.handoff.show', [
            'visit' => $visit,
            'defaultPlanKey' => PlatformSetting::salesHandoffPlanKey(),
            'defaultTrialDays' => PlatformSetting::salesHandoffTrialDays(),
            'planTiers' => config('plans.tiers', []),
            'availablePlanKeys' => $settings->handoffPlanKeys(),
        ]);
    }

    public function store(Request $request, Company $company, SalesLeadService $leads, SalesHandoffService $handoff)
    {
        $visit = $leads->findActiveLeadFor(auth()->user(), $company->id);
        $this->authorize('update', $visit);

        $settings = app(PlatformSettingsService::class);
        $data = $request->validate([
            'prospect_name' => 'required|string|max:255',
            'prospect_email' => 'required|email|max:255',
            'plan_key' => 'nullable|string|in:' . implode(',', $settings->handoffPlanKeys()),
            'trial_days' => 'nullable|integer|min:1|max:365',
        ]);

        $planKey = ! empty($data['plan_key']) ? $data['plan_key'] : null;
        $trialDays = ! empty($data['trial_days']) ? (int) $data['trial_days'] : null;

        $handoff->sendAccess(
            $request->user(),
            $visit,
            $data['prospect_email'],
            $data['prospect_name'],
            $planKey,
            $trialDays
        );

        return redirect()
            ->route('sales.dashboard')
            ->with('flash', 'Acceso enviado por email. El restaurante puede crear su contraseña desde el enlace.');
    }
}
