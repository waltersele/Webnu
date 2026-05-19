<?php

namespace App\Services\Platform;

use App\User;
use Illuminate\Support\Collection;

class UserBillingPresenter
{
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

    public function planLabel(User $user): string
    {
        $subscription = $user->primarySubscription();
        if (! $subscription) {
            return '—';
        }

        $monthly = config('billing.subscription_names.monthly');
        $yearly = config('billing.subscription_names.yearly');

        if ($subscription->name === $monthly) {
            return 'Mensual (10 €/mes)';
        }
        if ($subscription->name === $yearly) {
            return 'Anual (100 €/año)';
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

        return rtrim(config('platform.stripe_dashboard_customer_url'), '/') . '/' . $user->stripe_id;
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

        $monthly = config('billing.subscription_names.monthly');
        $mrr = config('platform.mrr');

        if ($subscription->name === $monthly) {
            return (float) $mrr['monthly_eur'];
        }

        return round((float) $mrr['yearly_eur'] / 12, 2);
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
