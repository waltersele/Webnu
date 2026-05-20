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

        if ($user->onGenericTrial()) {
            $trialTier = config('plans.trial_tier', 'plus');
            if ($this->tierExists($trialTier)) {
                return $trialTier;
            }
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
            'locales' => 'La carta multilingüe está disponible desde el plan Plus (9,90 €/mes).',
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
            throw ValidationException::withMessages([
                'locales' => "Tu plan permite hasta {$max} " . ($max === 1 ? 'idioma extra' : 'idiomas extra') . '. Mejora a Ilimitado para más idiomas.',
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
            'label' => $tier['label'] ?? 'Gratis',
            'trial_active' => false,
            'trial_expired' => false,
            'trial_days_remaining' => null,
            'trial_ends_at' => null,
        ];

        if ($user->isSuperAdmin()) {
            return $presentation;
        }

        if ($user->onGenericTrial()) {
            $presentation['trial_active'] = true;
            $presentation['trial_ends_at'] = $user->trial_ends_at;
            $presentation['trial_days_remaining'] = $user->trial_ends_at
                ? max(0, (int) now()->diffInDays($user->trial_ends_at, false))
                : null;
            $presentation['label'] = ($tier['label'] ?? 'Plus') . ' · prueba gratis';

            return $presentation;
        }

        if ($user->trial_ends_at && $user->trial_ends_at->isPast() && ! $user->hasActiveSubscription()) {
            $presentation['trial_expired'] = true;
        }

        return $presentation;
    }

    /** @return array<string, bool> */
    public function featureFlags(User $user): array
    {
        return [
            'videos' => $this->canUseVideos($user),
            'translation' => $this->canUseTranslation($user),
            'tvpik' => $this->canUseTvpik($user),
            'menu_scan' => $this->canUseMenuScan($user),
            'multi_company' => $this->maxCompanies($user) === null || $this->maxCompanies($user) > 1,
        ];
    }

    public function requiredPlanLabel(string $feature): ?string
    {
        $map = [
            'videos' => 'Plus',
            'translation' => 'Plus',
            'menu_scan' => 'Plus',
            'multi_company' => 'Plus',
            'tvpik' => 'Ilimitado',
        ];

        return $map[$feature] ?? null;
    }
}
