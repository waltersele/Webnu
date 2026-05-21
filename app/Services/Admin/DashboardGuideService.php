<?php

namespace App\Services\Admin;

use App\Company;
use App\Product;
use App\Section;
use App\User;
use App\Services\UserPlanService;

class DashboardGuideService
{
    protected UserPlanService $plans;

    public function __construct(UserPlanService $plans)
    {
        $this->plans = $plans;
    }

    /**
     * @return array<string, mixed>
     */
    public function build(User $user, ?Company $company): array
    {
        $planPresentation = $this->plans->planPresentation($user);
        $featureFlags = $this->plans->featureFlags($user);

        if ($company === null) {
            return [
                'hasCompany' => false,
                'productCount' => 0,
                'menuViews' => 0,
                'trialDaysRemaining' => $planPresentation['trial_days_remaining'] ?? null,
                'trialActive' => ! empty($planPresentation['trial_active']),
                'templateLabel' => null,
                'publicUrl' => null,
                'publicPath' => null,
                'progress' => $this->emptyProgress(),
                'nextStep' => $this->noCompanyNextStep(),
                'primaryActionKey' => 'create_business',
                'canMenuScan' => (bool) ($featureFlags['menu_scan'] ?? false),
                'canTvpik' => (bool) ($featureFlags['tvpik'] ?? false),
            ];
        }

        $productCount = $this->productCountForCompany($company);
        $progress = $this->buildProgress($company, $productCount);
        $completedSteps = $this->countCompletedSteps($progress);
        $nextStep = $this->resolveNextStep($company, $productCount, $progress, $featureFlags);
        $primaryActionKey = $this->primaryActionKey($productCount, $progress);

        $templates = config('company_templates.templates', []);
        $templateKey = $company->template ?: 'lumiere';
        $templateLabel = $templates[$templateKey]['label'] ?? $templateKey;

        return [
            'hasCompany' => true,
            'productCount' => $productCount,
            'menuViews' => (int) ($company->menu_views ?? 0),
            'trialDaysRemaining' => $planPresentation['trial_days_remaining'] ?? null,
            'trialActive' => ! empty($planPresentation['trial_active']),
            'templateLabel' => $templateLabel,
            'publicUrl' => route('see_menu', $company->slug),
            'publicPath' => 'webnu.es/carta/' . $company->slug,
            'progress' => $progress,
            'progressCompleted' => $completedSteps,
            'progressCurrent' => min(4, max(1, $completedSteps + 1)),
            'nextStep' => $nextStep,
            'primaryActionKey' => $primaryActionKey,
            'isPublished' => $productCount > 0 && (bool) $company->enabled,
            'canMenuScan' => (bool) ($featureFlags['menu_scan'] ?? false),
            'canTvpik' => (bool) ($featureFlags['tvpik'] ?? false),
        ];
    }

    public function productCountForCompany(Company $company): int
    {
        $sectionIds = Section::where('company_id', $company->id)->pluck('id');

        if ($sectionIds->isEmpty()) {
            return 0;
        }

        return (int) Product::whereIn('section_id', $sectionIds)->count();
    }

    public function companyHasIdentity(Company $company): bool
    {
        $name = trim((string) $company->name);

        return $name !== '' && $name !== 'Mi restaurante';
    }

    /**
     * @return array<string, bool>
     */
    protected function buildProgress(Company $company, int $productCount): array
    {
        return [
            'account' => true,
            'business' => $this->companyHasIdentity($company),
            'dishes' => $productCount > 0,
            'qr' => $productCount > 0 && (bool) $company->enabled,
        ];
    }

    /**
     * @return array<string, bool>
     */
    protected function emptyProgress(): array
    {
        return [
            'account' => true,
            'business' => false,
            'dishes' => false,
            'qr' => false,
        ];
    }

    /**
     * @param array<string, bool> $progress
     */
    protected function countCompletedSteps(array $progress): int
    {
        $count = 0;
        foreach (['account', 'business', 'dishes', 'qr'] as $key) {
            if (! empty($progress[$key])) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param array<string, bool> $progress
     * @param array<string, bool> $featureFlags
     * @return array<string, string>
     */
    protected function resolveNextStep(Company $company, int $productCount, array $progress, array $featureFlags): array
    {
        if ($productCount === 0) {
            $scanUrl = route('admin.menu-scan.create');
            if (! ($featureFlags['menu_scan'] ?? false)) {
                $scanUrl = route('admin.sections.index');
            }

            return [
                'key' => 'import_dishes',
                'title' => 'Siguiente paso: añade tus platos',
                'subtitle' => 'Fotografía tu carta en papel y la IA los añade solos — sin escribir nada',
                'cta' => 'Importar con IA',
                'ctaUrl' => $scanUrl,
                'icon' => 'ti-camera',
            ];
        }

        if (empty($progress['qr'])) {
            return [
                'key' => 'download_qr',
                'title' => 'Siguiente paso: descarga tu QR',
                'subtitle' => 'Imprímelo y colócalo en las mesas para que tus clientes escaneen la carta',
                'cta' => 'Descargar QR',
                'ctaUrl' => route('admin.qrgenerator', $company),
                'icon' => 'ti-qrcode',
            ];
        }

        return [
            'key' => 'connect_tv',
            'title' => 'Siguiente paso: conecta la TV del local',
            'subtitle' => 'Muestra tu carta en pantalla y se actualiza sola cuando cambias algo',
            'cta' => 'Conectar TV',
            'ctaUrl' => route('admin.tvpik.index'),
            'icon' => 'ti-device-tv',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function noCompanyNextStep(): array
    {
        return [
            'key' => 'create_business',
            'title' => 'Siguiente paso: crea tu negocio',
            'subtitle' => 'En un minuto tendrás tu carta digital lista para personalizar',
            'cta' => 'Crear mi negocio',
            'ctaUrl' => route('admin.companies.index'),
            'icon' => 'ti-store',
        ];
    }

    /**
     * @param array<string, bool> $progress
     */
    protected function primaryActionKey(int $productCount, array $progress): string
    {
        if ($productCount === 0) {
            return 'add_dishes';
        }

        if (empty($progress['qr'])) {
            return 'qr';
        }

        return 'view_menu';
    }
}
