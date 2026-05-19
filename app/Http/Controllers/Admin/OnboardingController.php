<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Controller;
use App\Services\UserPlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class OnboardingController extends Controller
{
    public function show(Request $request, UserPlanService $plans)
    {
        $user = $request->user();
        $company = $this->primaryCompany($user);

        if ($user->hasCompletedOnboarding()) {
            return redirect()->route('admin.companies.edit', $company);
        }

        $step = max(1, min(5, (int) $request->get('step', max(1, (int) $user->onboarding_step ?: 1))));

        $templates = collect(config('company_templates.templates', []))
            ->only(['lumiere', 'bistro', 'nocturne', 'temporada', 'catalogo']);

        $themePresets = config('company_templates.presets', []);
        $publicUrl = route('see_menu', $company->slug);
        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=12&data=' . urlencode($publicUrl);

        return view('admin.onboarding.show', [
            'user' => $user,
            'company' => $company,
            'step' => $step,
            'templates' => $templates,
            'themePresets' => $themePresets,
            'plan' => $plans->tier($user),
            'scansRemaining' => $plans->menuScansRemaining($user),
            'scansUsed' => $plans->menuScansUsed($user),
            'scanLimit' => $plans->menuScanLimit($user),
            'publicUrl' => $publicUrl,
            'qrImageUrl' => $qrImageUrl,
            'menuScanUrl' => route('admin.menu-scan.create'),
            'billingUrl' => route('admin.billing'),
        ]);
    }

    public function update(Request $request, UserPlanService $plans)
    {
        $user = $request->user();
        $company = $this->primaryCompany($user);
        $this->authorize('update', $company);

        $step = (int) $request->get('step');

        switch ($step) {
            case 2:
                $data = $request->validate([
                    'name' => 'required|string|max:255',
                ]);
                $company->name = $data['name'];
                $company->save();
                break;

            case 3:
                $allowed = array_keys(config('company_templates.templates', []));
                $data = $request->validate([
                    'template' => 'required|string|in:' . implode(',', $allowed),
                ]);
                $company->template = $data['template'];
                $preset = config('company_templates.presets.' . $data['template']);
                if (is_array($preset)) {
                    $company->theme_settings = array_merge(
                        $company->resolvedThemeSettings(),
                        $preset
                    );
                }
                $company->save();
                break;

            case 5:
                $company->enabled = true;
                $company->save();

                $user->onboarding_completed_at = now();
                $user->onboarding_step = 5;
                $user->save();

                return redirect()
                    ->route('admin.sections.index')
                    ->with('flash', '¡Tu carta está publicada! Ya puedes añadir platos y compartir el QR.');
        }

        $user->onboarding_step = max((int) $user->onboarding_step, $step);
        $user->save();

        $nextStep = min(5, $step + 1);

        return redirect()->route('admin.onboarding', ['step' => $nextStep]);
    }

    public function skip(Request $request)
    {
        $user = $request->user();
        $company = $this->primaryCompany($user);
        $this->authorize('update', $company);

        $company->enabled = true;
        $company->save();

        $user->onboarding_completed_at = now();
        $user->onboarding_step = 5;
        $user->save();

        return redirect()
            ->route('admin.sections.index')
            ->with('flash', 'Puedes completar la configuración cuando quieras desde Mi negocio.');
    }

    protected function primaryCompany($user): Company
    {
        $companyId = Cookie::get('selected_company');
        $company = Company::where('user_id', $user->id)
            ->when($companyId, function ($q) use ($companyId) {
                $q->where('id', (int) $companyId);
            })
            ->first();

        if (! $company) {
            $company = Company::where('user_id', $user->id)->latest('id')->firstOrFail();
            Cookie::queue(Cookie::forever('selected_company', $company->id));
        }

        return $company;
    }
}
