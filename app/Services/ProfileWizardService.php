<?php

namespace App\Services;

use App\Company;
use App\User;

class ProfileWizardService
{
    /**
     * Pasos para completar el perfil. Reutiliza las animaciones del onboarding.
     *
     * @return array<int, array<string, mixed>>
     */
    public function stepsFor(User $user, ?Company $company): array
    {
        if (! $company) {
            return [[
                'key' => 'business',
                'title' => 'Crea tu primer negocio',
                'description' => 'Da de alta el restaurante para empezar.',
                'icon' => 'ri-store-2-line',
                'animation' => 'welcome',
                'cta_label' => 'Crear negocio',
                'cta_url' => route('admin.companies.index'),
                'is_done' => false,
            ]];
        }

        $sectionsCount = (int) $company->sections()->count();
        $productsCount = (int) $company->sections()
            ->withCount('products')
            ->get()
            ->sum('products_count');

        $steps = [
            [
                'key' => 'business',
                'title' => 'Completa los datos del negocio',
                'description' => 'Nombre, teléfono, dirección y horario para que tus clientes te encuentren.',
                'icon' => 'ri-store-2-line',
                'animation' => 'business-name',
                'cta_label' => 'Editar negocio',
                'cta_url' => route('admin.companies.edit', $company),
                'is_done' => $this->isBusinessComplete($company),
            ],
            [
                'key' => 'branding',
                'title' => 'Elige plantilla y sube tu logo',
                'description' => 'Da personalidad a tu carta con un estilo visual.',
                'icon' => 'ri-palette-line',
                'animation' => 'template',
                'cta_label' => 'Personalizar diseño',
                'cta_url' => route('admin.companies.edit', ['company' => $company, 'step' => 'design']),
                'is_done' => $this->isBrandingComplete($company),
            ],
            [
                'key' => 'menu_sections',
                'title' => 'Crea las secciones de tu carta',
                'description' => 'Organiza tu carta en entrantes, principales, postres, etc.',
                'icon' => 'ri-list-check',
                'animation' => 'menu-scan',
                'cta_label' => 'Añadir secciones',
                'cta_url' => route('admin.sections.index'),
                'is_done' => $sectionsCount > 0,
            ],
            [
                'key' => 'menu_products',
                'title' => 'Añade al menos 3 platos',
                'description' => 'Sin platos no hay carta. Importa con IA o créalos manualmente.',
                'icon' => 'ri-restaurant-2-line',
                'animation' => 'menu-scan',
                'cta_label' => 'Añadir platos',
                'cta_url' => route('admin.sections.index'),
                'is_done' => $productsCount >= 3,
            ],
            [
                'key' => 'languages',
                'title' => 'Configura idiomas (opcional)',
                'description' => 'Activa traducciones para tus clientes internacionales.',
                'icon' => 'ri-translate-2',
                'animation' => 'languages',
                'cta_label' => 'Configurar idiomas',
                'cta_url' => route('admin.companies.languages', $company),
                'is_done' => is_array($company->enabled_locales) && count($company->enabled_locales) > 0,
            ],
            [
                'key' => 'publish',
                'title' => 'Publica tu carta',
                'description' => 'Activa la carta y comparte el QR con tus clientes.',
                'icon' => 'ri-rocket-line',
                'animation' => 'publish',
                'cta_label' => $company->enabled ? 'Ver carta' : 'Publicar ahora',
                'cta_url' => $company->enabled
                    ? $company->publicUrl()
                    : route('admin.sections.index'),
                'is_done' => (bool) $company->enabled && $sectionsCount > 0 && $productsCount > 0,
            ],
        ];

        return array_values($steps);
    }

    /**
     * @return array{done:int,total:int,pct:int}
     */
    public function progress(User $user, ?Company $company): array
    {
        $steps = $this->stepsFor($user, $company);
        $total = max(count($steps), 1);
        $done = count(array_filter($steps, fn ($step) => ! empty($step['is_done'])));
        $pct = (int) round(($done / $total) * 100);

        return [
            'done' => $done,
            'total' => $total,
            'pct' => $pct,
        ];
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

        // Si la compañía se ha actualizado después del dismiss significa
        // que el usuario está avanzando y volvemos a mostrar el widget.
        $companyUpdatedAt = $company->updated_at;
        if ($companyUpdatedAt && $companyUpdatedAt->gt($dismissedAt)) {
            return true;
        }

        return false;
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

    protected function isBrandingComplete(Company $company): bool
    {
        $template = (string) $company->template;
        $hasLogo = trim((string) $company->logo) !== '';

        return $template !== '' && $template !== 'basic' && $hasLogo;
    }
}
