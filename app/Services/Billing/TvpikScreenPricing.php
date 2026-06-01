<?php

namespace App\Services\Billing;

class TvpikScreenPricing
{
    public function minProfessionalScreens(): int
    {
        return (int) config('tvpik_pricing.min_professional_screens', 2);
    }

    public function maxScreens(): int
    {
        return (int) config('tvpik_pricing.max_screens', 20);
    }

    public function annualDiscount(): float
    {
        return (float) config('tvpik_pricing.annual_discount', 0.20);
    }

    /**
     * @return array<int, array{min: int, max: int, rate_eur: int}>
     */
    public function tiers(): array
    {
        return config('tvpik_pricing.tiers', []);
    }

    public function ratePerScreenEuros(int $totalScreens): int
    {
        if ($totalScreens <= 0) {
            return 0;
        }

        foreach ($this->tiers() as $tier) {
            $min = (int) ($tier['min'] ?? 0);
            $max = (int) ($tier['max'] ?? 0);
            if ($totalScreens >= $min && $totalScreens <= $max) {
                return (int) ($tier['rate_eur'] ?? 0);
            }
        }

        return 0;
    }

    public function monthlyTotalCents(int $totalScreens): int
    {
        if ($totalScreens <= 0) {
            return 0;
        }

        $rate = $this->ratePerScreenEuros($totalScreens);

        return $totalScreens * $rate * 100;
    }

    public function monthlyTotalLabel(int $totalScreens): string
    {
        if ($totalScreens <= 0) {
            return '0 €';
        }

        $cents = $this->monthlyTotalCents($totalScreens);

        return $this->formatEuroFromCents($cents) . '/mes';
    }

    public function screensIncludedForPlanTier(string $tierKey): int
    {
        $included = config('plans.tiers.' . $tierKey . '.tvpik_screens_included', 0);

        return $included === null ? 0 : (int) $included;
    }

    /**
     * Total de pantallas licenciadas tras alta/checkout.
     */
    public function normalizeTotalScreens(string $planTier, ?int $requested): int
    {
        $total = max(0, (int) $requested);
        $included = $this->screensIncludedForPlanTier($planTier);

        if ($planTier === 'plus' && $total === 0) {
            return max(1, $included);
        }

        return min($total, $this->maxScreens());
    }

    public function extraScreensBeyondIncluded(string $planTier, int $totalLicensed): int
    {
        $included = $this->screensIncludedForPlanTier($planTier);

        return max(0, $totalLicensed - $included);
    }

    /**
     * Importe recurrente de la parte TVPik (sin plan Webnu), en céntimos.
     */
    /**
     * Valor de las pantallas incluidas en el plan (p. ej. 1×8 € en Plus).
     */
    public function includedScreenCreditCents(int $includedScreens): int
    {
        if ($includedScreens <= 0) {
            return 0;
        }

        $tiers = $this->tiers();
        $rate = (int) ($tiers[0]['rate_eur'] ?? 8);

        return $includedScreens * $rate * 100;
    }

    public function addonRecurringCents(int $totalLicensed, string $planTier, string $billingCycle = 'monthly'): int
    {
        $included = $this->screensIncludedForPlanTier($planTier);
        $monthlyAddon = max(0, $this->monthlyTotalCents($totalLicensed) - $this->includedScreenCreditCents($included));

        if ($monthlyAddon <= 0) {
            return 0;
        }

        if ($billingCycle === 'yearly') {
            return (int) round($monthlyAddon * 12 * (1 - $this->annualDiscount()));
        }

        return $monthlyAddon;
    }

    public function isValidTotalForTier(string $planTier, int $totalLicensed): bool
    {
        if ($totalLicensed <= 0) {
            return true;
        }

        if ($totalLicensed > $this->maxScreens()) {
            return false;
        }

        $included = $this->screensIncludedForPlanTier($planTier);
        $minBillable = $included > 0 ? 1 : $this->minProfessionalScreens();

        if ($totalLicensed < $minBillable) {
            return false;
        }

        if ($included === 0 && $totalLicensed === 1) {
            return false;
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function quote(string $planTier, int $requestedScreens, string $billingCycle = 'monthly'): array
    {
        $total = $this->normalizeTotalScreens($planTier, $requestedScreens);
        $included = $this->screensIncludedForPlanTier($planTier);
        $rate = $this->ratePerScreenEuros($total);
        $addonCents = $this->addonRecurringCents($total, $planTier, $billingCycle);
        $valid = $this->isValidTotalForTier($planTier, $requestedScreens > 0 ? $requestedScreens : 0);

        return [
            'plan_tier' => $planTier,
            'billing_cycle' => $billingCycle,
            'total_screens' => $total,
            'screens_included' => $included,
            'extra_screens' => $this->extraScreensBeyondIncluded($planTier, $total),
            'rate_per_screen_eur' => $rate,
            'monthly_total_label' => $this->monthlyTotalLabel($total),
            'addon_cents' => $addonCents,
            'addon_label' => $this->formatAddonLabel($addonCents, $billingCycle),
            'valid' => $valid,
            'needs_franchise' => $requestedScreens > $this->maxScreens(),
            'min_professional_screens' => $this->minProfessionalScreens(),
            'max_screens' => $this->maxScreens(),
            'tiers' => $this->tiers(),
            'annual_discount_percent' => (int) round($this->annualDiscount() * 100),
        ];
    }

    public function formatEuroFromCents(int $cents): string
    {
        return number_format($cents / 100, 2, ',', '.') . ' €';
    }

    protected function formatAddonLabel(int $cents, string $billingCycle): string
    {
        if ($cents <= 0) {
            return 'Incluido';
        }

        $suffix = $billingCycle === 'yearly' ? '/año' : '/mes';

        return $this->formatEuroFromCents($cents) . $suffix . ' · sin IVA';
    }
}
