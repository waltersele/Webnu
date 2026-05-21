<?php

namespace App\Services\Platform;

use App\User;
use Illuminate\Support\Facades\DB;

class PlatformMetricsService
{
    /** @var UserBillingPresenter */
    protected $billing;

    public function __construct(UserBillingPresenter $billing)
    {
        $this->billing = $billing;
    }

    public function summary(): array
    {
        $users = User::withBillingSummary()->get();

        $total = $users->count();
        $active = 0;
        $trialing = 0;
        $pastDue = 0;
        $none = 0;
        $mrr = 0.0;

        foreach ($users as $user) {
            $subscription = $user->primarySubscription();

            if ($user->onGenericTrial()) {
                $trialing++;
            } elseif (! $subscription) {
                $none++;
            } elseif (in_array($subscription->stripe_status, ['active'], true)) {
                $active++;
                $mrr += $this->billing->mrrContributionEur($user);
            } elseif ($subscription->stripe_status === 'trialing') {
                $trialing++;
                $mrr += $this->billing->mrrContributionEur($user);
            } elseif (in_array($subscription->stripe_status, ['past_due', 'unpaid'], true)) {
                $pastDue++;
            } else {
                $none++;
            }
        }

        $companies = (int) DB::table('companies')
            ->where(function ($q) {
                $q->whereNull('sales_rep_user_id')
                    ->orWhereNotNull('sales_converted_at');
            })
            ->count();

        return [
            'total_users' => $total,
            'active_subscriptions' => $active,
            'trialing' => $trialing,
            'past_due' => $pastDue,
            'without_subscription' => $none,
            'estimated_mrr_eur' => round($mrr, 2),
            'total_companies' => $companies,
        ];
    }
}
