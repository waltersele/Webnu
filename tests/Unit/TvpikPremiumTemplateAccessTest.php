<?php

namespace Tests\Unit;

use App\Services\UserPlanService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TvpikPremiumTemplateAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_plus_user_can_use_premium_tv_templates(): void
    {
        $user = User::factory()->create(['plan' => 'unlimited']);
        $plans = app(UserPlanService::class);

        $this->assertTrue($plans->canUseTvpikPremiumTemplates($user));
        $this->assertTrue($plans->canUseTvpikTemplate($user, 'cinema'));
        $this->assertTrue($plans->canUseTvpikTemplate($user, 'menu'));
    }

    public function test_pro_user_with_extra_screen_cannot_use_premium_templates(): void
    {
        $user = User::factory()->create([
            'plan' => 'pro',
            'tvpik_extra_screens' => 1,
        ]);
        $plans = app(UserPlanService::class);

        $this->assertTrue($plans->canUseTvpik($user));
        $this->assertFalse($plans->canUseTvpikPremiumTemplates($user));
        $this->assertTrue($plans->canUseTvpikTemplate($user, 'menu'));
        $this->assertFalse($plans->canUseTvpikTemplate($user, 'marquee'));
    }

    public function test_tvpik_template_access_lists_locked_premium_for_pro(): void
    {
        $user = User::factory()->create([
            'plan' => 'pro',
            'tvpik_extra_screens' => 1,
        ]);
        $access = app(UserPlanService::class)->tvpikTemplateAccessForUser($user);

        $this->assertFalse($access['can_use_premium']);
        $this->assertContains('menu', $access['allowed_keys']);
        $this->assertContains('cinema', $access['locked_keys']);
        $this->assertNotContains('cinema', $access['allowed_keys']);
    }
}
