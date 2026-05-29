<?php

namespace App\Services\Platform;

use App\Services\UserPlanService;
use App\User;
use Illuminate\Support\Collection;

class UserBillingPresenter
{
    protected UserPlanService $plans;

    public function __construct(UserPlanService $plans)
    {
        $this->plans = $plans;
    }

    public function statusLabel(User $user): string
    {
        if ($user->onGenericTrial()) {
            return 'Prueba';
        }

        $subscription = $user->primarySubscription();
        if (! $subscription) {
            return 'Sin suscripción';
        }

        return $this->mapStripeStatus($subscription->stripe_status);
    }

    public function publicStatusLabel(User $user): string
    {
        if ($user->onGenericTrial()) {
            return 'Prueba gratis';
        }

        if ($this->plans->manualPlanIsActive($user)) {
            return 'Activación manual';
        }

        $subscription = $user->primarySubscription();
        if (! $subscription) {
            return 'Sin suscripción activa';
        }

        return $this->mapStripeStatus($subscription->stripe_status);
    }

    public function statusBadgeClass(User $user): string
    {
        $subscription = $user->primarySubscription();
        if ($user->onGenericTrial()) {
            return 'bg-label-info';
        }
        if (! $subscription) {
            return 'bg-label-secondary';
        }

        switch ($subscription->stripe_status) {
            case 'active':
                return 'bg-label-success';
            case 'trialing':
                return 'bg-label-info';
            case 'past_due':
            case 'unpaid':
                return 'bg-label-danger';
            case 'canceled':
                return 'bg-label-warning';
            default:
                return 'bg-label-secondary';
        }
    }

    public function effectivePlanLabel(User $user): string
    {
        return $this->plans->planPresentation($user)['label'] ?? '—';
    }

    public function planLabel(User $user): string
    {
        $presentation = $this->plans->planPresentation($user);
        $stripePlan = $this->stripeSubscriptionLabel($user);

        if ($stripePlan) {
            return $stripePlan . ' · efectivo: ' . ($presentation['label'] ?? '—');
        }

        return ($presentation['label'] ?? '—') . ' (manual / free)';
    }

    public function stripeSubscriptionLabel(User $user): ?string
    {
        $subscription = $user->primarySubscription();
        if (! $subscription || ! $subscription->name) {
            return null;
        }

        $map = config('billing.subscription_names', []);
        foreach ($map as $catalogKey => $name) {
            if ($subscription->name === $name) {
                return config('billing.display.' . $catalogKey)
                    ?? config('billing.price_catalog.' . $catalogKey . '.label')
                    ?? $subscription->name;
            }
        }

        return $subscription->name;
    }

    public function cardSummary(User $user): string
    {
        if (! $user->card_brand || ! $user->card_last_four) {
            return '—';
        }

        return strtoupper($user->card_brand) . ' ···· ' . $user->card_last_four;
    }

    public function stripeCustomerUrl(User $user): ?string
    {
        if (! $user->stripe_id) {
            return null;
        }

        $base = config('platform.stripe_dashboard_customer_url', 'https://dashboard.stripe.com/test/customers');
        if (! is_string($base) || trim($base) === '') {
            return null;
        }

        return rtrim($base, '/') . '/' . $user->stripe_id;
    }

    /**
     * @return Collection<int, mixed>
     */
    public function invoices(User $user): Collection
    {
        if (! $user->stripe_id) {
            return collect();
        }

        try {
            return collect($user->invoices());
        } catch (\Throwable $e) {
            return collect();
        }
    }

    /** @return array<int, array{key: string, label: string, enabled: bool}> */
    public function planFeatureList(User $user): array
    {
        $tier = $this->plans->tier($user);

        $items = [
            ['key' => 'menu_scan', 'label' => 'Escaneo IA de carta', 'enabled' => (bool) ($tier['menu_scan'] ?? false)],
            ['key' => 'videos', 'label' => 'Vídeos en platos', 'enabled' => (bool) ($tier['videos'] ?? false)],
            ['key' => 'translation', 'label' => 'Carta multilingüe', 'enabled' => (bool) ($tier['translation'] ?? false)],
            ['key' => 'pdf_menu', 'label' => 'Carta en PDF', 'enabled' => (bool) ($tier['pdf_menu'] ?? false)],
            ['key' => 'tvpik', 'label' => 'Pantallas (TVPik)', 'enabled' => (bool) ($tier['tvpik'] ?? false)],
            ['key' => 'priority_support', 'label' => 'Soporte prioritario', 'enabled' => (bool) ($tier['priority_support'] ?? false)],
            ['key' => 'whatsapp_support', 'label' => 'Soporte por WhatsApp', 'enabled' => (bool) ($tier['whatsapp_support'] ?? false)],
        ];

        return array_values(array_filter($items, function ($item) {
            return is_array($item) && !empty($item['label']);
        }));
    }

    /**
     * @return array{
     *   mode: string,
     *   current: array{label_caps: string, title: string, items: array<int, array{label: string, ok: bool}>, footer: array{label: string, disabled: bool}},
     *   upgrade: array{label_caps: string, title: string, badge: ?string, items: array<int, array{label: string, ok: bool}>, cta: array{label: string, href: ?string, portal: bool}},
     *   bento: array{title: string, items: array<int, array{key: string, title: string, desc: string, icon: string, locked: bool}>}
     * }
     */
    public function planComparison(User $user): array
    {
        $presentation = $this->plans->planPresentation($user);
        $tier = $this->plans->tier($user);
        $tierKey = $this->plans->planKey($user);
        $hasAccess = $user->hasActiveSubscription();

        $isTrial = !empty($presentation['trial_active']);
        $trialDays = $presentation['trial_days_remaining'] ?? null;
        $trialEnds = $presentation['trial_ends_at_formatted'] ?? null;
        $isManual = !empty($presentation['manual_active']);

        $upgradeTierKey = $tierKey === 'free' ? 'pro' : ($tierKey === 'pro' ? 'plus' : 'plus');
        $upgradeTier = array_merge(['key' => $upgradeTierKey], config('plans.tiers.' . $upgradeTierKey, []));

        $currentItems = $this->comparisonItemsForTier($tierKey, $tier);
        $upgradeItems = $this->comparisonItemsForTier($upgradeTierKey, $upgradeTier);

        $mode = 'default';
        if ($isTrial) {
            $mode = 'trial';
        } elseif ($hasAccess) {
            $mode = 'active';
        } elseif ($isManual) {
            $mode = 'manual';
        }

        $currentTitle = $tier['label'] ?? 'Plan';
        if ($isTrial) {
            $currentTitle = 'Prueba ' . ($tier['label'] ?? 'Pro');
        } elseif ($isManual) {
            $currentTitle = ($tier['label'] ?? 'Plan') . ' (manual)';
        }

        $upgradeTitle = $upgradeTier['label'] ?? 'Pro';
        $upgradeCaps = $tierKey === 'free' ? 'Revoluciona tu negocio' : 'Mejora tu plan';

        $cta = [
            'label' => $tierKey === 'free' ? 'Mejorar a Pro ahora' : 'Mejorar ahora',
            'href' => null,
            'portal' => false,
        ];

        if ($user->stripe_id) {
            $cta['href'] = route('admin.billing.portal');
            $cta['portal'] = true;
        } else {
            $cta['href'] = route('welcome');
        }

        if ($hasAccess) {
            $cta['label'] = 'Gestionar suscripción';
            $cta['href'] = route('admin.billing.portal');
            $cta['portal'] = true;
        }

        $bento = $this->bentoForUser($user);

        if ($isTrial) {
            $extra = [];
            if ($trialDays !== null) {
                $extra[] = ['label' => "Te quedan {$trialDays} " . ((int) $trialDays === 1 ? 'día' : 'días') . ' de prueba', 'ok' => true];
            }
            if ($trialEnds) {
                $extra[] = ['label' => "Finaliza el {$trialEnds}", 'ok' => true];
            }
            $currentItems = array_merge($extra, $currentItems);
        }

        return [
            'mode' => $mode,
            'current' => [
                'label_caps' => 'Tu plan actual',
                'title' => $currentTitle,
                'items' => $currentItems,
                'footer' => [
                    'label' => 'Plan en uso',
                    'disabled' => true,
                ],
            ],
            'upgrade' => [
                'label_caps' => $upgradeCaps,
                'title' => $upgradeTitle,
                'badge' => $tierKey === 'free' ? 'Recomendado' : null,
                'items' => $upgradeItems,
                'cta' => $cta,
            ],
            'bento' => $bento,
        ];
    }

    public function mrrContributionEur(User $user): float
    {
        if (! $user->hasActiveSubscription()) {
            return 0.0;
        }

        $subscription = $user->primarySubscription();
        if (! $subscription || ! in_array($subscription->stripe_status, ['active', 'trialing'], true)) {
            return 0.0;
        }

        $map = config('billing.subscription_names', []);
        foreach ($map as $catalogKey => $name) {
            if ($subscription->name === $name) {
                $cents = config('billing.price_catalog.' . $catalogKey . '.amount_cents');
                if ($cents) {
                    $monthly = ((int) $cents) / 100;
                    if (substr($catalogKey, -7) === '_yearly') {
                        return round($monthly / 12, 2);
                    }

                    return round($monthly, 2);
                }
            }
        }

        $mrr = config('platform.mrr', []);

        return (float) ($mrr['monthly_eur'] ?? 9.90);
    }

    protected function mapStripeStatus(?string $status): string
    {
        $map = [
            'active' => 'Activa',
            'trialing' => 'En prueba',
            'past_due' => 'Impago',
            'unpaid' => 'Impago',
            'canceled' => 'Cancelada',
            'incomplete' => 'Incompleta',
            'incomplete_expired' => 'Expirada',
        ];

        return $map[$status] ?? ucfirst((string) $status);
    }

    /**
     * @param array<string, mixed> $tier
     * @return array<int, array{label: string, ok: bool}>
     */
    protected function comparisonItemsForTier(string $tierKey, array $tier): array
    {
        $items = [];

        $maxCompanies = $tier['max_companies'] ?? null;
        if ($maxCompanies === null) {
            $items[] = ['label' => 'Negocios ilimitados', 'ok' => true];
        } else {
            $items[] = ['label' => "Hasta {$maxCompanies} negocio" . ((int) $maxCompanies === 1 ? '' : 's'), 'ok' => true];
        }

        $maxMenus = $tier['max_menus'] ?? null;
        if ($maxMenus === null) {
            $items[] = ['label' => 'Menús ilimitados', 'ok' => true];
        } else {
            $items[] = ['label' => "Hasta {$maxMenus} menú" . ((int) $maxMenus === 1 ? '' : 's'), 'ok' => true];
        }

        $items[] = [
            'label' => ($tier['videos'] ?? false) ? 'Vídeos en platos' : 'Sin vídeos en platos',
            'ok' => (bool) ($tier['videos'] ?? false),
        ];
        $items[] = [
            'label' => ($tier['translation'] ?? false) ? 'Carta multilingüe' : 'Sin carta multilingüe',
            'ok' => (bool) ($tier['translation'] ?? false),
        ];

        $items[] = [
            'label' => ($tier['whatsapp_support'] ?? false) ? 'Soporte por WhatsApp' : 'Sin soporte por WhatsApp',
            'ok' => (bool) ($tier['whatsapp_support'] ?? false),
        ];

        $items[] = [
            'label' => ($tier['show_webnu_badge'] ?? false) ? 'Incluye marca Webnu en la carta' : 'Sin marca Webnu en la carta',
            'ok' => !(bool) ($tier['show_webnu_badge'] ?? false),
        ];

        $menuScans = $tier['menu_scans'] ?? null;
        $scanPeriod = $tier['menu_scans_period'] ?? null;
        if ($menuScans === null) {
            $items[] = ['label' => 'Escaneo IA sin límite', 'ok' => true];
        } else {
            $suffix = '';
            if ($scanPeriod === 'monthly') {
                $suffix = ' / mes';
            } elseif ($scanPeriod === 'lifetime') {
                $suffix = ' (total)';
            }
            $items[] = ['label' => "Escaneo IA: {$menuScans}{$suffix}", 'ok' => true];
        }

        return $items;
    }

    /**
     * @return array{title: string, items: array<int, array{key: string, title: string, desc: string, icon: string, locked: bool}>}
     */
    protected function bentoForUser(User $user): array
    {
        $flags = $this->plans->featureFlags($user);

        $items = [
            [
                'key' => 'menu_scan',
                'title' => 'Optimización por IA',
                'desc' => 'Escanea tu carta y acelera la carga de platos con ayuda de IA.',
                'icon' => 'ti-brain',
                'locked' => empty($flags['menu_scan']),
            ],
            [
                'key' => 'videos',
                'title' => 'Vídeos en platos',
                'desc' => 'Añade vídeos a tus platos para una carta más atractiva.',
                'icon' => 'ti-movie',
                'locked' => empty($flags['videos']),
            ],
            [
                'key' => 'tvpik',
                'title' => 'TVPik y pantallas',
                'desc' => 'Muestra menús y promociones en TV con tu carta digital.',
                'icon' => 'ti-device-tv',
                'locked' => empty($flags['tvpik']),
            ],
        ];

        $hasLocked = false;
        foreach ($items as $it) {
            if (!empty($it['locked'])) {
                $hasLocked = true;
                break;
            }
        }

        return [
            'title' => 'Funciones que estás perdiendo',
            'items' => $hasLocked ? $items : [],
        ];
    }
}
