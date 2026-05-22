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
            return 'Sin suscripción Stripe';
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
}
