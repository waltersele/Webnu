<?php

namespace App\Services;

use App\Company;
use App\Menu;
use App\MenuScanJob;
use App\Product;
use App\User;
use Illuminate\Validation\ValidationException;

class UserPlanService
{
    public function planKey(User $user): string
    {
        if ($user->isSuperAdmin()) {
            return 'plus';
        }

        if ($this->manualPlanIsActive($user)) {
            $manualTier = $this->resolveTierKey((string) $user->manual_plan_key);
            if ($this->tierExists($manualTier)) {
                return $manualTier;
            }
        }

        if ($user->onGenericTrial()) {
            $trialTier = $this->resolveTierKey($user->trial_plan_key ?: config('plans.trial_tier', 'pro'));

            if ($this->tierExists($trialTier)) {
                return $trialTier;
            }
        }

        if ($user->hasActiveSubscription()) {
            $subscription = $user->primarySubscription();
            if ($subscription && $subscription->name) {
                $mapped = config('plans.subscription_map.' . $subscription->name);
                if ($mapped && $this->tierExists($this->resolveTierKey($mapped))) {
                    return $this->resolveTierKey($mapped);
                }
            }

            return 'pro';
        }

        $plan = $this->resolveTierKey($user->plan ?? config('plans.default', 'free'));

        return $this->tierExists($plan) ? $plan : config('plans.default', 'free');
    }

    public function manualPlanIsActive(User $user): bool
    {
        $key = $user->manual_plan_key ?? null;
        if (! is_string($key) || $key === '') {
            return false;
        }

        if (! $this->tierExists($this->resolveTierKey($key))) {
            return false;
        }

        $until = $user->manual_plan_until ?? null;
        if ($until === null) {
            return true;
        }

        if (is_string($until)) {
            try {
                $until = \Illuminate\Support\Carbon::parse($until);
            } catch (\Throwable $e) {
                return false;
            }
        }

        return $until->isFuture();
    }

    public function tier(User $user): array
    {
        $key = $this->planKey($user);

        return array_merge(
            ['key' => $key],
            config('plans.tiers.' . $key, config('plans.tiers.free', []))
        );
    }

    public function resolveTierKey(?string $key): string
    {
        if ($key === null || $key === '') {
            return config('plans.default', 'free');
        }

        $aliases = config('plans.tier_aliases', []);

        return $aliases[$key] ?? $key;
    }

    public function proPriceLabel(): string
    {
        return config('plans.tiers.pro.price_label', '9,90 €/mes');
    }

    public function plusPriceLabel(): string
    {
        return config('plans.tiers.plus.price_label', '19,90 €/mes');
    }

    public function menuScanLimit(User $user): ?int
    {
        $limit = $this->tier($user)['menu_scans'] ?? null;

        return $limit === null ? null : (int) $limit;
    }

    public function menuScanPeriod(User $user): ?string
    {
        $period = $this->tier($user)['menu_scans_period'] ?? null;

        return $period === null || $period === '' ? null : (string) $period;
    }

    public function menuScansUsed(User $user): int
    {
        $query = MenuScanJob::where('user_id', $user->id)
            ->whereIn('status', MenuScanJob::billableStatuses());

        if ($this->menuScanPeriod($user) === 'monthly') {
            $query->where('created_at', '>=', now()->startOfMonth());
        }

        return (int) $query->count();
    }

    public function menuScansRemaining(User $user): ?int
    {
        $limit = $this->menuScanLimit($user);
        if ($limit === null) {
            return null;
        }

        return max(0, $limit - $this->menuScansUsed($user));
    }

    public function canUseMenuScan(User $user, ?Company $company = null): bool
    {
        if ($this->canBypassMenuScanLimits($user, $company)) {
            return true;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! ($this->tier($user)['menu_scan'] ?? false)) {
            return false;
        }

        $remaining = $this->menuScansRemaining($user);

        return $remaining === null || $remaining > 0;
    }

    public function canBypassMenuScanLimits(User $user, ?Company $company = null): bool
    {
        if (! $company || ! $user->isSalesRep()) {
            return false;
        }

        return $company->isActiveSalesLead()
            && (int) $company->sales_rep_user_id === (int) $user->id;
    }

    public function canUseVideos(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return (bool) ($this->tier($user)['videos'] ?? false);
    }

    public function canUseProductPhotos(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return (bool) ($this->tier($user)['product_photos'] ?? false);
    }

    public function canUsePdfMenu(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return (bool) ($this->tier($user)['pdf_menu'] ?? false);
    }

    public function canUseChefSuggestions(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return (bool) ($this->tier($user)['chef_suggestions'] ?? false);
    }

    public function assertCanUseChefSuggestions(User $user): void
    {
        if ($this->canUseChefSuggestions($user)) {
            return;
        }

        throw ValidationException::withMessages([
            'highlights' => 'Las sugerencias del chef están disponibles desde el plan Pro (' . $this->proPriceLabel() . ').',
        ]);
    }

    public function shouldShowWebnuBadge(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        return (bool) ($this->tier($user)['show_webnu_badge'] ?? false);
    }

    public function maxProductsPerCompany(User $user): ?int
    {
        $max = $this->tier($user)['max_products_per_company'] ?? null;

        return $max === null ? null : (int) $max;
    }

    public function productsCountForCompany(Company $company): int
    {
        return (int) Product::whereIn('section_id', function ($query) use ($company) {
            $query->select('id')
                ->from('sections')
                ->where('company_id', $company->id);
        })->count();
    }

    public function canAddProduct(User $user, Company $company): bool
    {
        $max = $this->maxProductsPerCompany($user);
        if ($max === null) {
            return true;
        }

        return $this->productsCountForCompany($company) < $max;
    }

    public function tvpikScreensIncluded(User $user): ?int
    {
        if ($user->isSuperAdmin()) {
            return null;
        }

        $included = $this->tier($user)['tvpik_screens_included'] ?? 0;

        return $included === null ? null : (int) $included;
    }

    public function tvpikExtraScreens(User $user): int
    {
        if ($user->isSuperAdmin()) {
            return 0;
        }

        return max(0, (int) ($user->tvpik_extra_screens ?? 0));
    }

    public function tvpikMaxScreens(User $user): ?int
    {
        if ($user->isSuperAdmin()) {
            return null;
        }

        $included = $this->tvpikScreensIncluded($user);
        $extra = $this->tvpikExtraScreens($user);

        if ($included === null) {
            return $extra > 0 ? $extra : null;
        }

        $total = $included + $extra;

        return $total > 0 ? $total : 0;
    }

    public function canUseTvpik(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ((bool) ($this->tier($user)['tvpik'] ?? false)) {
            return true;
        }

        return $this->tvpikMaxScreens($user) > 0;
    }

    /** @return string[] */
    public function premiumTvpikTemplateKeys(): array
    {
        $keys = [];
        foreach (config('tvpik_templates.templates', []) as $key => $template) {
            if (! empty($template['premium'])) {
                $keys[] = (string) ($template['key'] ?? $key);
            }
        }

        return $keys;
    }

    public function isPremiumTvpikTemplate(string $templateKey): bool
    {
        $template = config('tvpik_templates.templates.' . $templateKey);

        return ! empty($template['premium']);
    }

    public function canUseTvpikPremiumTemplates(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $this->canUseTvpik($user)) {
            return false;
        }

        return (bool) ($this->tier($user)['tvpik_premium_templates'] ?? false);
    }

    public function canUseTvpikTemplate(User $user, string $templateKey): bool
    {
        if (! $this->canUseTvpik($user)) {
            return false;
        }

        if ($this->isPremiumTvpikTemplate($templateKey)) {
            return $this->canUseTvpikPremiumTemplates($user);
        }

        return true;
    }

    public function assertCanUseTvpikTemplate(User $user, string $templateKey): void
    {
        if ($this->canUseTvpikTemplate($user, $templateKey)) {
            return;
        }

        if (! $this->canUseTvpik($user)) {
            throw ValidationException::withMessages([
                'template_key' => 'Las pantallas TV requieren plan Plus o un add-on de pantalla en Pro.',
            ]);
        }

        $template = config('tvpik_templates.templates.' . $templateKey);
        $label = (string) ($template['label'] ?? $templateKey);
        $planLabel = $this->requiredPlanLabel('tvpik_premium_templates') ?? 'Plus';

        throw ValidationException::withMessages([
            'template_key' => "La plantilla «{$label}» requiere plan {$planLabel} (plantillas TV premium).",
        ]);
    }

    /**
     * @return array{can_use_premium: bool, allowed_keys: string[], locked_keys: string[]}
     */
    public function tvpikTemplateAccessForUser(User $user): array
    {
        $all = array_keys(config('tvpik_templates.templates', []));
        $premium = $this->premiumTvpikTemplateKeys();
        $standard = array_values(array_diff($all, $premium));

        if (! $this->canUseTvpik($user)) {
            return [
                'can_use_premium' => false,
                'allowed_keys' => [],
                'locked_keys' => $all,
            ];
        }

        if ($this->canUseTvpikPremiumTemplates($user)) {
            return [
                'can_use_premium' => true,
                'allowed_keys' => $all,
                'locked_keys' => [],
            ];
        }

        return [
            'can_use_premium' => false,
            'allowed_keys' => $standard,
            'locked_keys' => $premium,
        ];
    }

    public function maxCompanies(User $user): ?int
    {
        $max = $this->tier($user)['max_companies'] ?? null;

        return $max === null ? null : (int) $max;
    }

    public function canCreateCompany(User $user): bool
    {
        $max = $this->maxCompanies($user);
        if ($max === null) {
            return true;
        }

        return Company::where('user_id', $user->id)
            ->countsTowardPlanLimit()
            ->count() < $max;
    }

    public function assertCanCreateCompany(User $user): void
    {
        if ($this->canCreateCompany($user)) {
            return;
        }

        $max = $this->maxCompanies($user);
        throw ValidationException::withMessages([
            'name' => "Tu plan permite hasta {$max} " . ($max === 1 ? 'carta' : 'cartas') . '. Mejora a Pro (' . $this->proPriceLabel() . ') o Plus (' . $this->plusPriceLabel() . ') para añadir más.',
        ]);
    }

    public function maxMenus(User $user): ?int
    {
        $max = $this->tier($user)['max_menus'] ?? null;

        return $max === null ? null : (int) $max;
    }

    public function menuCount(User $user): int
    {
        return Menu::whereHas('company', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->count();
    }

    public function canCreateMenu(User $user): bool
    {
        $max = $this->maxMenus($user);
        if ($max === null) {
            return true;
        }

        return $this->menuCount($user) < $max;
    }

    public function assertCanCreateMenu(User $user): void
    {
        if ($this->canCreateMenu($user)) {
            return;
        }

        $max = $this->maxMenus($user);
        throw ValidationException::withMessages([
            'name' => "Tu plan permite hasta {$max} " . ($max === 1 ? 'menú' : 'menús') . '. Mejora a Pro (' . $this->proPriceLabel() . ') o Plus (' . $this->plusPriceLabel() . ') para añadir más.',
        ]);
    }

    public function assertCanAddProduct(User $user, Company $company): void
    {
        if ($this->canAddProduct($user, $company)) {
            return;
        }

        $max = $this->maxProductsPerCompany($user);
        throw ValidationException::withMessages([
            'product_add_name' => "Tu plan Free permite hasta {$max} platos por carta. Pásate a Pro (" . $this->proPriceLabel() . ') para platos ilimitados.',
        ]);
    }

    public function assertCanUseProductPhotos(User $user): void
    {
        if ($this->canUseProductPhotos($user)) {
            return;
        }

        throw ValidationException::withMessages([
            'product_add_image' => 'Las fotos de platos están disponibles desde el plan Pro (' . $this->proPriceLabel() . ').',
        ]);
    }

    public function assertCanUsePdfMenu(User $user): void
    {
        if ($this->canUsePdfMenu($user)) {
            return;
        }

        throw ValidationException::withMessages([
            'menu_type' => 'La carta en PDF está disponible desde el plan Pro (' . $this->proPriceLabel() . ').',
        ]);
    }

    public function assertCanUseMenuScan(User $user, ?Company $company = null): void
    {
        if ($this->canUseMenuScan($user, $company)) {
            return;
        }

        $remaining = $this->menuScansRemaining($user);
        if ($remaining === 0) {
            $key = $this->planKey($user);
            if ($key === 'free') {
                throw ValidationException::withMessages([
                    'files' => 'Has usado tu escaneo IA del plan Free. Pásate a Pro (' . $this->proPriceLabel() . ') para escaneos ilimitados.',
                ]);
            }

            throw ValidationException::withMessages([
                'files' => 'Has alcanzado el límite de escaneos IA de tu plan.',
            ]);
        }

        throw ValidationException::withMessages([
            'files' => 'El escaneo IA no está disponible en tu plan actual.',
        ]);
    }

    public function assertCanUseVideos(User $user): void
    {
        if ($this->canUseVideos($user)) {
            return;
        }

        throw ValidationException::withMessages([
            'product_add_video' => 'Los vídeos en platos están disponibles desde el plan Pro (' . $this->proPriceLabel() . ').',
        ]);
    }

    public function canUseTranslation(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return (bool) ($this->tier($user)['translation'] ?? false);
    }

    public function maxTranslationLocales(User $user): ?int
    {
        if ($user->isSuperAdmin()) {
            return null;
        }

        $max = $this->tier($user)['translation_max_locales'] ?? 0;

        return $max === null ? null : (int) $max;
    }

    public function assertCanUseTranslation(User $user): void
    {
        if ($this->canUseTranslation($user)) {
            return;
        }

        throw ValidationException::withMessages([
            'locales' => 'La carta multilingüe está disponible desde el plan Pro (' . $this->proPriceLabel() . ').',
        ]);
    }

    public function assertCanEnableLocales(User $user, int $extraLocaleCount): void
    {
        $this->assertCanUseTranslation($user);

        $max = $this->maxTranslationLocales($user);
        if ($max === null) {
            return;
        }

        if ($extraLocaleCount > $max) {
            $total = $max + 1;
            throw ValidationException::withMessages([
                'locales' => "Tu plan Pro permite hasta {$total} idiomas en la carta (idioma base + {$max} extras). Mejora a Plus (" . $this->plusPriceLabel() . ') para idiomas ilimitados.',
            ]);
        }
    }

    protected function tierExists(string $key): bool
    {
        return array_key_exists($key, config('plans.tiers', []));
    }

    /** @return array<string, mixed> */
    public function planPresentation(User $user): array
    {
        $tier = $this->tier($user);
        $presentation = [
            'key' => $tier['key'] ?? 'free',
            'label' => $tier['label'] ?? 'Free',
            'trial_active' => false,
            'trial_expired' => false,
            'trial_days_remaining' => null,
            'trial_ends_at' => null,
            'trial_ends_at_formatted' => null,
            'manual_active' => false,
            'manual_until' => null,
            'manual_until_formatted' => null,
            'manual_days_remaining' => null,
        ];

        if ($user->isSuperAdmin()) {
            return $presentation;
        }

        if ($this->manualPlanIsActive($user)) {
            $presentation['manual_active'] = true;
            $presentation['manual_until'] = $user->manual_plan_until;
            $presentation['manual_until_formatted'] = $user->manual_plan_until
                ? $user->manual_plan_until->format('d/m/Y')
                : null;
            $presentation['manual_days_remaining'] = $user->manual_plan_until
                ? max(0, (int) now()->diffInDays($user->manual_plan_until, false))
                : null;
            if ($user->manual_plan_until) {
                $presentation['label'] = ($tier['label'] ?? 'Plan') . ' · manual (hasta ' . $user->manual_plan_until->format('d/m/Y') . ')';
            } else {
                $presentation['label'] = ($tier['label'] ?? 'Plan') . ' · manual';
            }

            return $presentation;
        }

        if ($user->onGenericTrial()) {
            $presentation['trial_active'] = true;
            $presentation['trial_ends_at'] = $user->trial_ends_at;
            $presentation['trial_ends_at_formatted'] = $user->trial_ends_at
                ? $user->trial_ends_at->format('d/m/Y')
                : null;
            $presentation['trial_days_remaining'] = $user->trial_ends_at
                ? max(0, (int) now()->diffInDays($user->trial_ends_at, false))
                : null;
            $presentation['label'] = ($tier['label'] ?? 'Pro') . ' · prueba gratis';

            return $presentation;
        }

        if ($user->trial_ends_at && $user->trial_ends_at->isPast() && ! $user->hasActiveSubscription()) {
            $presentation['trial_expired'] = true;
        }

        return $presentation;
    }

    /** @return string[] */
    public function freeTemplateKeys(): array
    {
        $catalog = array_keys(config('company_templates.templates', []));
        $configured = config('plans.free_template_keys', []);

        return array_values(array_filter($configured, function ($key) use ($catalog) {
            return in_array($key, $catalog, true);
        }));
    }

    public function hasAllTemplates(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->planKey($user) !== 'free';
    }

    public function canUseTemplate(User $user, string $templateKey): bool
    {
        if ($this->hasAllTemplates($user)) {
            return true;
        }

        return in_array($templateKey, $this->freeTemplateKeys(), true);
    }

    public function assertCanUseTemplate(User $user, string $templateKey): void
    {
        if ($this->canUseTemplate($user, $templateKey)) {
            return;
        }

        $label = $this->requiredPlanLabel('templates') ?? 'Pro';

        throw ValidationException::withMessages([
            'template' => ["Esta plantilla requiere el plan {$label}. Mejora tu suscripción en Ajustes → Plan."],
        ]);
    }

    /**
     * @return array{can_use_all: bool, allowed_keys: string[], locked_keys: string[]}
     */
    public function templateAccessForUser(User $user): array
    {
        $all = array_keys(config('company_templates.templates', []));
        $allowed = $this->hasAllTemplates($user)
            ? $all
            : $this->freeTemplateKeys();

        return [
            'can_use_all' => $this->hasAllTemplates($user),
            'allowed_keys' => $allowed,
            'locked_keys' => array_values(array_diff($all, $allowed)),
        ];
    }

    public function isTemplateLockedForUser(User $user, string $templateKey, ?string $currentTemplate = null): bool
    {
        if ($this->canUseTemplate($user, $templateKey)) {
            return false;
        }

        if ($currentTemplate !== null && $templateKey === $currentTemplate) {
            return false;
        }

        return true;
    }

    /** @return array<string, bool> */
    public function featureFlags(User $user): array
    {
        return [
            'videos' => $this->canUseVideos($user),
            'product_photos' => $this->canUseProductPhotos($user),
            'pdf_menu' => $this->canUsePdfMenu($user),
            'chef_suggestions' => $this->canUseChefSuggestions($user),
            'translation' => $this->canUseTranslation($user),
            'tvpik' => $this->canUseTvpik($user),
            'tvpik_premium_templates' => $this->canUseTvpikPremiumTemplates($user),
            'menu_scan' => $this->canUseMenuScan($user),
            'multi_company' => $this->maxCompanies($user) === null || $this->maxCompanies($user) > 1,
            'show_webnu_badge' => $this->shouldShowWebnuBadge($user),
            'all_templates' => $this->hasAllTemplates($user),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function signageEntitlements(User $user): array
    {
        $planKey = $this->planKey($user);
        $tier = $this->tier($user);
        $presentation = $this->planPresentation($user);
        $features = $this->featureFlags($user);

        $billingSource = 'manual';
        if ($user->isSuperAdmin()) {
            $billingSource = 'superadmin';
        } elseif ($user->onGenericTrial()) {
            $billingSource = 'trial';
        } elseif ($user->hasActiveSubscription()) {
            $billingSource = 'stripe';
        }

        return [
            'api_version' => config('digital_signage.api_version', '1.0'),
            'billing' => [
                'owner' => 'webnu',
                'source' => $billingSource,
                'upgrade_url' => url('/admin/settings') . '#plan',
                'portal_available' => $user->hasActiveSubscription(),
            ],
            'plan' => [
                'key' => $planKey,
                'label' => $presentation['label'],
                'price_label' => $tier['price_label'] ?? null,
                'trial_active' => $presentation['trial_active'],
                'trial_expired' => $presentation['trial_expired'],
                'trial_ends_at' => $presentation['trial_ends_at']
                    ? $presentation['trial_ends_at']->toIso8601String()
                    : null,
            ],
            'features' => [
                'tvpik' => $features['tvpik'],
                'tvpik_premium_templates' => $features['tvpik_premium_templates'],
                'videos' => $features['videos'],
                'translation' => $features['translation'],
                'menu_scan' => $features['menu_scan'],
                'multi_company' => $features['multi_company'],
                'product_photos' => $features['product_photos'],
                'pdf_menu' => $features['pdf_menu'],
                'chef_suggestions' => $features['chef_suggestions'],
            ],
            'limits' => [
                'max_companies' => $this->maxCompanies($user),
                'max_menus' => $this->maxMenus($user),
                'max_products_per_company' => $this->maxProductsPerCompany($user),
                'menu_scans_remaining' => $this->menuScansRemaining($user),
                'translation_max_locales' => $this->maxTranslationLocales($user),
                'tvpik_max_screens' => $this->tvpikMaxScreens($user),
            ],
            'required_plan_for' => [
                'tvpik' => $this->requiredPlanLabel('tvpik'),
                'tvpik_premium_templates' => $this->requiredPlanLabel('tvpik_premium_templates'),
                'videos' => $this->requiredPlanLabel('videos'),
                'translation' => $this->requiredPlanLabel('translation'),
                'product_photos' => $this->requiredPlanLabel('product_photos'),
                'pdf_menu' => $this->requiredPlanLabel('pdf_menu'),
                'templates' => $this->requiredPlanLabel('templates'),
                'chef_suggestions' => $this->requiredPlanLabel('chef_suggestions'),
            ],
        ];
    }

    public function requiredPlanLabel(string $feature): ?string
    {
        foreach (config('plans.tiers', []) as $tierKey => $tier) {
            $requiredFor = $tier['required_for'] ?? [];
            if (! empty($requiredFor[$feature])) {
                return $tier['label'] ?? ucfirst($tierKey);
            }
        }

        $fallback = [
            'videos' => 'pro',
            'translation' => 'pro',
            'menu_scan' => 'pro',
            'multi_company' => 'pro',
            'product_photos' => 'pro',
            'pdf_menu' => 'pro',
            'templates' => 'pro',
            'chef_suggestions' => 'pro',
            'tvpik' => 'plus',
            'tvpik_premium_templates' => 'plus',
        ];

        $tierKey = $fallback[$feature] ?? null;
        if ($tierKey && isset(config('plans.tiers')[$tierKey])) {
            return config('plans.tiers.' . $tierKey . '.label');
        }

        return null;
    }
}
