<?php

namespace Tests\Unit;

use App\Services\Billing\TvpikScreenPricing;
use Tests\TestCase;

class TvpikScreenPricingTest extends TestCase
{
    /** @var TvpikScreenPricing */
    protected $pricing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricing = new TvpikScreenPricing();
    }

    public function test_monthly_totals_match_tvpik_examples(): void
    {
        $this->assertSame(2400, $this->pricing->monthlyTotalCents(3));
        $this->assertSame(3500, $this->pricing->monthlyTotalCents(5));
        $this->assertSame(6000, $this->pricing->monthlyTotalCents(10));
        $this->assertSame(1600, $this->pricing->monthlyTotalCents(2));
    }

    public function test_plus_addon_subtracts_included_screen_value(): void
    {
        $addon = $this->pricing->addonRecurringCents(5, 'plus', 'monthly');
        $this->assertSame(2700, $addon);
    }

    public function test_plus_two_screens_charges_one_extra_at_tier_rate(): void
    {
        $addon = $this->pricing->addonRecurringCents(2, 'plus', 'monthly');
        $this->assertSame(800, $addon);
    }

    public function test_included_screen_credit_uses_minimum_tier_rate(): void
    {
        $this->assertSame(800, $this->pricing->includedScreenCreditCents(1));
    }

    public function test_pro_charges_full_tvpik_total(): void
    {
        $addon = $this->pricing->addonRecurringCents(5, 'pro', 'monthly');
        $this->assertSame(3500, $addon);
    }

    public function test_yearly_applies_twenty_percent_discount_on_tvpik(): void
    {
        $addon = $this->pricing->addonRecurringCents(5, 'pro', 'yearly');
        $this->assertSame(33600, $addon);
    }

    public function test_pro_rejects_single_screen(): void
    {
        $this->assertFalse($this->pricing->isValidTotalForTier('pro', 1));
        $this->assertTrue($this->pricing->isValidTotalForTier('pro', 2));
    }
}
