<?php

namespace App\Services;

use App\Company;
use App\MenuScanJob;
use App\User;
use Illuminate\Validation\ValidationException;

class UserPlanService
{
    public function planKey(User $user): string
    {
        if ($user->isSuperAdmin()) {
            return 'unlimited';
        }

        if ($user->hasActiveSubscription()) {
            $subscription = $user->primarySubscription();
            if ($subscription && $subscription->name) {
                $mapped = config('plans.subscription_map.' . $subscription->name);
                if ($mapped && $this->tierExists($mapped)) {
                    return $mapped;
                }
            }

            return 'plus';
        }

        $plan = $user->plan ?? config('plans.default', 'free');

        return $this->tierExists($plan) ? $plan : config('plans.default', 'free');
    }

    public function tier(User $user): array
    {
        $key = $this->planKey($user);

        return array_merge(
            ['key' => $key],
            config('plans.tiers.' . $key, config('plans.tiers.free', []))
        );
    }

    public function menuScanLimit(User $user): ?int
    {
        $limit = $this->tier($user)['menu_scans'] ?? null;

        return $limit === null ? null : (int) $limit;
    }

    public function menuScansUsed(User $user): int
    {
        return (int) MenuScanJob::where('user_id', $user->id)->count();
    }

    public function menuScansRemaining(User $user): ?int
    {
        $limit = $this->menuScanLimit($user);
        if ($limit === null) {
            return null;
        }

        return max(0, $limit - $this->menuScansUsed($user));
    }

    public function canUseMenuScan(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! ($this->tier($user)['menu_scan'] ?? false)) {
            return false;
        }

        $remaining = $this->menuScansRemaining($user);

        return $remaining === null || $remaining > 0;
    }

    public function canUseVideos(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return (bool) ($this->tier($user)['videos'] ?? false);
    }

    public function canUseTvpik(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return (bool) ($this->tier($user)['tvpik'] ?? false);
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

        return Company::where('user_id', $user->id)->count() < $max;
    }

    public function assertCanCreateCompany(User $user): void
    {
        if ($this->canCreateCompany($user)) {
            return;
        }

        $max = $this->maxCompanies($user);
        throw ValidationException::withMessages([
            'name' => "Tu plan permite hasta {$max} " . ($max === 1 ? 'carta' : 'cartas') . '. Mejora a Plus o Ilimitado para añadir más.',
        ]);
    }

    public function assertCanUseMenuScan(User $user): void
    {
        if ($this->canUseMenuScan($user)) {
            return;
        }

        $remaining = $this->menuScansRemaining($user);
        if ($remaining === 0) {
            throw ValidationException::withMessages([
                'files' => 'Has usado tus 5 escaneos IA del plan Gratis. Pásate a Plus (9,90 €/mes) para escaneos ilimitados.',
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
            'product_add_video' => 'Los vídeos en platos están disponibles desde el plan Plus (9,90 €/mes).',
        ]);
    }

    protected function tierExists(string $key): bool
    {
        return array_key_exists($key, config('plans.tiers', []));
    }
}
