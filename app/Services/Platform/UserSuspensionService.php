<?php

namespace App\Services\Platform;

use App\Company;
use App\User;

class UserSuspensionService
{
    public function suspend(User $user, string $reason, ?User $actor = null): void
    {
        if ($user->isSuspended()) {
            return;
        }

        if ($user->isSuperAdmin()) {
            return;
        }

        $user->load('companies');
        $snapshot = [];
        foreach ($user->companies as $company) {
            $snapshot[(string) $company->id] = (bool) $company->enabled;
        }

        Company::where('user_id', $user->id)->update(['enabled' => false]);

        $user->suspended_at = now();
        $user->suspended_reason = $reason;
        $user->suspension_snapshot = ['companies' => $snapshot];
        $user->suspended_by = $actor?->id;
        $user->save();
    }

    public function unsuspend(User $user): void
    {
        if (! $user->isSuspended()) {
            return;
        }

        $snapshot = $user->suspension_snapshot['companies'] ?? [];
        if (is_array($snapshot)) {
            foreach ($snapshot as $companyId => $wasEnabled) {
                if (! $wasEnabled) {
                    continue;
                }
                Company::where('id', (int) $companyId)
                    ->where('user_id', $user->id)
                    ->update(['enabled' => true]);
            }
        }

        $user->suspended_at = null;
        $user->suspended_reason = null;
        $user->suspension_snapshot = null;
        $user->suspended_by = null;
        $user->save();
    }

    public function shouldAutoUnsuspend(User $user): bool
    {
        if (! $user->isSuspended()) {
            return false;
        }

        return in_array($user->suspended_reason, ['trial_expired', 'subscription_ended'], true);
    }
}
