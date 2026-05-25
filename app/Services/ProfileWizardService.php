<?php

namespace App\Services;

use App\Company;
use App\User;

class ProfileWizardService
{
    /**
     * Devuelve los pasos agrupados en "negocio" (cuenta) y "carta" (por empresa).
     *
     * Los pasos de negocio son comunes a todas las cartas: nombre, contacto, dirección, logo.
     * Los pasos de carta son específicos de cada carta: plantilla, platos, publicación.
     *
     * @return array{
     *   account_steps: array,
     *   card_steps: array,
     *   account_progress: array{done:int,total:int,pct:int},
     *   card_progress: array{done:int,total:int,pct:int},
     *   overall_progress: array{done:int,total:int,pct:int},
     *   company_name: string
     * }
     */
    public function groupsFor(User $user, ?Company $company): array
    {
        if (! $company) {
            $accountSteps = [[
                'key' => 'create_company',
                'title' => 'Crea tu primera carta',
                'description' => 'Da de alta el restaurante para empezar.',
                'icon' => 'ri-store-2-line',
                'animation' => 'welcome',
                'cta_label' => 'Crear carta',
                'cta_url' => route('admin.companies.index'),
                'is_done' => false,
            ]];

            return [
                'account_steps'    => $accountSteps,
                'card_steps'       => [],
                'account_progress' => $this->calcProgress($accountSteps),
                'card_progress'    => ['done' => 0, 'total' => 0, 'pct' => 0],
                'overall_progress' => $this->calcProgress($accountSteps),
                'company_name'     => '',
            ];
        }

        $accountSteps = $this->accountSteps($company);
        $cardSteps    = $this->cardSteps($company);

        return [
            'account_steps'    => $accountSteps,
            'card_steps'       => $cardSteps,
            'account_progress' => $this->calcProgress($accountSteps),
            'card_progress'    => $this->calcProgress($cardSteps),
            'overall_progress' => $this->calcProgress(array_merge($accountSteps, $cardSteps)),
            'company_name'     => $company->name ?? '',
        ];
    }

    /**
     * Pasos del negocio (cuenta): iguales para todas las cartas del mismo restaurante.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function accountSteps(Company $company): array
    {
        return [
            [
                'key'         => 'business',
                'title'       => 'Completa los datos del negocio',
                'description' => 'Nombre, teléfono, dirección y horario para que tus clientes te encuentren.',
                'icon'        => 'ri-store-2-line',
                'animation'   => 'business-name',
                'cta_label'   => 'Editar negocio',
                'cta_url'     => route('admin.companies.edit', $company),
                'is_done'     => $this->isBusinessComplete($company),
            ],
            [
                'key'         => 'logo',
                'title'       => 'Sube el logo del negocio',
                'description' => 'El logo aparece en la carta y refuerza tu imagen de marca.',
                'icon'        => 'ri-image-line',
                'animation'   => 'business-name',
                'cta_label'   => 'Subir logo',
                'cta_url'     => route('admin.companies.edit', $company),
                'is_done'     => trim((string) $company->logo) !== '',
            ],
        ];
    }

    /**
     * Pasos de la carta concreta: plantilla, contenido y publicación.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function cardSteps(Company $company): array
    {
        $sectionsCount = (int) $company->sections()->count();
        $productsCount = (int) $company->sections()
            ->withCount('products')
            ->get()
            ->sum('products_count');

        return [
            [
                'key'         => 'template',
                'title'       => 'Elige una plantilla',
                'description' => 'Da personalidad a esta carta con un estilo visual.',
                'icon'        => 'ri-palette-line',
                'animation'   => 'template',
                'cta_label'   => 'Personalizar diseño',
                'cta_url'     => route('admin.companies.edit', ['company' => $company, 'step' => 'design']),
                'is_done'     => $this->isTemplateSelected($company),
            ],
            [
                'key'         => 'menu_sections',
                'title'       => 'Crea las secciones de tu carta',
                'description' => 'Organiza tu carta en entrantes, principales, postres, etc.',
                'icon'        => 'ri-list-check',
                'animation'   => 'menu-scan',
                'cta_label'   => 'Añadir secciones',
                'cta_url'     => route('admin.sections.index'),
                'is_done'     => $sectionsCount > 0,
            ],
            [
                'key'         => 'menu_products',
                'title'       => 'Añade al menos 3 platos',
                'description' => 'Sin platos no hay carta. Importa con IA o créalos manualmente.',
                'icon'        => 'ri-restaurant-2-line',
                'animation'   => 'menu-scan',
                'cta_label'   => 'Añadir platos',
                'cta_url'     => route('admin.sections.index'),
                'is_done'     => $productsCount >= 3,
            ],
            [
                'key'         => 'publish',
                'title'       => 'Publica esta carta',
                'description' => 'Activa la carta y comparte el QR con tus clientes.',
                'icon'        => 'ri-rocket-line',
                'animation'   => 'publish',
                'cta_label'   => $company->enabled ? 'Ver carta' : 'Publicar ahora',
                'cta_url'     => $company->enabled
                    ? $company->publicUrl()
                    : route('admin.sections.index'),
                'is_done'     => (bool) $company->enabled && $sectionsCount > 0 && $productsCount > 0,
            ],
        ];
    }

    /**
     * Devuelve todos los pasos en una lista plana (compatibilidad con código existente).
     *
     * @return array<int, array<string, mixed>>
     */
    public function stepsFor(User $user, ?Company $company): array
    {
        $groups = $this->groupsFor($user, $company);
        return array_merge($groups['account_steps'], $groups['card_steps']);
    }

    /**
     * @return array{done:int,total:int,pct:int}
     */
    public function progress(User $user, ?Company $company): array
    {
        $groups = $this->groupsFor($user, $company);
        return $groups['overall_progress'];
    }

    public function shouldShow(User $user, ?Company $company): bool
    {
        $progress = $this->progress($user, $company);
        if ($progress['done'] >= $progress['total']) {
            return false;
        }

        $dismissedAt = $user->profile_wizard_dismissed_at ?? null;
        if (! $dismissedAt) {
            return true;
        }

        if (! $company) {
            return false;
        }

        $companyUpdatedAt = $company->updated_at;
        if ($companyUpdatedAt && $companyUpdatedAt->gt($dismissedAt)) {
            return true;
        }

        return false;
    }

    // -----------------------------------------------------------------------
    // Helpers internos
    // -----------------------------------------------------------------------

    /**
     * @param array<int, array<string, mixed>> $steps
     * @return array{done:int,total:int,pct:int}
     */
    protected function calcProgress(array $steps): array
    {
        $total = max(count($steps), 1);
        $done  = count(array_filter($steps, fn ($s) => ! empty($s['is_done'])));
        return [
            'done'  => $done,
            'total' => count($steps),
            'pct'   => $total > 0 ? (int) round(($done / $total) * 100) : 0,
        ];
    }

    protected function isBusinessComplete(Company $company): bool
    {
        $name = trim((string) $company->name);
        $phone = trim((string) ($company->phone ?: $company->mobile_phone));
        $hasAddress = trim((string) $company->address) !== '' || trim((string) $company->city) !== '';

        return $name !== ''
            && $name !== 'Mi restaurante'
            && ($phone !== '' || trim((string) $company->email) !== '')
            && $hasAddress;
    }

    protected function isTemplateSelected(Company $company): bool
    {
        $template = (string) $company->template;
        return $template !== '' && $template !== 'basic';
    }
}
