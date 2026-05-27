<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Controller;
use App\Section;
use App\Services\CompanySlugService;
use App\Services\ProfileWizardService;
use Illuminate\Support\Facades\Cookie;

class AdminController extends Controller
{
    public function index(ProfileWizardService $wizard)
    {
        $user = auth()->user();

        if ($user && $user->isSuperAdmin()) {
            return redirect()->route('admin.platform.dashboard');
        }

        if (! $user) {
            return redirect()->route('login');
        }

        $company = $this->resolveSelectedCompany($user);

        if (! $company) {
            $this->ensureCompanyForOnboarding($user);

            return redirect()->route('admin.onboarding');
        }

        $profileGroups   = $wizard->groupsFor($user, $company);
        $profileSteps    = array_merge($profileGroups['account_steps'], $profileGroups['card_steps']);
        $profileProgress = $profileGroups['overall_progress'];
        $showProfileWizard = $wizard->shouldShow($user, $company);

        return view('admin.dashboard', [
            'dashboardCompany' => $company,
            'dashboard' => [
                'hasCompany' => true,
                'menuViews' => (int) ($company->menu_views ?? 0),
                'progressCompleted' => $profileProgress['done'],
                'progressCurrent' => $profileProgress['done'] + 1,
                'progress' => $this->legacyProgressMap($profileSteps),
                'primaryActionKey' => $this->firstPendingKey($profileSteps),
                'nextStep' => $this->buildNextStep($profileSteps),
                'canMenuScan' => true,
                'canTvpik' => false,
            ],
            'profileGroups'    => $profileGroups,
            'profileSteps'     => $profileSteps,
            'profileProgress'  => $profileProgress,
            'showProfileWizard' => $showProfileWizard,
        ]);
    }

    protected function resolveSelectedCompany($user): ?Company
    {
        $selectedId = (int) \Illuminate\Support\Facades\Cookie::get('selected_company');
        $query = Company::where('user_id', $user->id);

        if ($selectedId > 0) {
            $found = (clone $query)->where('id', $selectedId)->first();
            if ($found) {
                return $found;
            }
        }

        return $query->orderBy('name')->first();
    }

    /**
     * Para usuarios legacy sin company (p.ej. marcados como onboarding completo por la
     * migración 2026_05_20 pero sin negocio creado), bootstrap una company vacía y
     * reinicia el wizard para que /admin/onboarding funcione sin 404 ni bucles.
     */
    protected function ensureCompanyForOnboarding($user): void
    {
        if ($user->companies()->exists()) {
            return;
        }

        $businessName = 'Mi carta';
        $ownerSlug = $user->resolveSlug();

        $company = Company::create([
            'name' => $businessName,
            'slug' => app(CompanySlugService::class)->generateFromName($businessName, null, null, $ownerSlug),
            'template' => 'lumiere',
            'menu_type' => 1,
            'enabled' => false,
            'reservation' => false,
            'user_id' => $user->id,
        ]);

        Cookie::queue(Cookie::forever('selected_company', $company->id));

        $user->onboarding_completed_at = null;
        $user->onboarding_step = 1;
        $user->save();
    }

    protected function companyHasMenuStructure(Company $company): bool
    {
        return Section::where('company_id', $company->id)->exists();
    }

    /**
     * Mapa de pasos legado que la vista dashboard.blade.php espera:
     * account / business / dishes / qr.
     *
     * @param array<int, array<string, mixed>> $steps
     * @return array<string, bool>
     */
    protected function legacyProgressMap(array $steps): array
    {
        $byKey = collect($steps)->keyBy('key');

        return [
            'account' => true,
            'business' => (bool) ($byKey->get('business')['is_done'] ?? false),
            'dishes' => (bool) ($byKey->get('menu_products')['is_done'] ?? false),
            'qr' => (bool) ($byKey->get('publish')['is_done'] ?? false),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $steps
     */
    protected function firstPendingKey(array $steps): string
    {
        foreach ($steps as $step) {
            if (empty($step['is_done'])) {
                return $step['key'];
            }
        }
        return 'publish';
    }

    /**
     * @param array<int, array<string, mixed>> $steps
     * @return array<string, mixed>
     */
    protected function buildNextStep(array $steps): array
    {
        foreach ($steps as $step) {
            if (empty($step['is_done'])) {
                return [
                    'key' => $step['key'],
                    'title' => $step['title'],
                    'subtitle' => $step['description'],
                    'cta' => $step['cta_label'],
                    'ctaUrl' => $step['cta_url'],
                    'icon' => 'ti-rocket',
                ];
            }
        }

        return [
            'key' => 'done',
            'title' => 'Todo listo',
            'subtitle' => 'Tu perfil está completo. Comparte tu carta con tus clientes.',
            'cta' => 'Ver carta',
            'ctaUrl' => route('admin.sections.index'),
            'icon' => 'ti-check',
        ];
    }
}
