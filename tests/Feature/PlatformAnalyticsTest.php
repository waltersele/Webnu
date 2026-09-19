<?php

namespace Tests\Feature;

use App\Services\Platform\PlatformAnalyticsRecorder;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PlatformAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['platform.super_admin_emails' => ['super@test.com']]);
    }

    public function test_beacon_records_landing_pageview(): void
    {
        $this->postJson('/analytics/beacon', [
            'event' => 'pageview',
            'path' => '/',
            'referrer' => 'https://www.google.com/search?q=webnu',
        ])->assertNoContent();

        $this->assertDatabaseHas('platform_analytics_dailies', [
            'metric' => PlatformAnalyticsRecorder::METRIC_LANDING_PAGEVIEW,
            'dimension' => '/',
        ]);

        $this->assertDatabaseHas('platform_analytics_dailies', [
            'metric' => 'landing_referrer',
            'dimension' => 'www.google.com',
        ]);
    }

    public function test_beacon_records_cta_click(): void
    {
        $this->postJson('/analytics/beacon', [
            'event' => 'cta',
            'path' => '/',
            'cta' => 'hero_register',
        ])->assertNoContent();

        $this->assertDatabaseHas('platform_analytics_dailies', [
            'metric' => PlatformAnalyticsRecorder::METRIC_LANDING_CTA,
            'dimension' => 'hero_register',
        ]);
    }

    public function test_superadmin_can_view_analytics_panel(): void
    {
        $user = User::factory()->create([
            'email' => 'super@test.com',
        ]);

        Gate::define('platform.access', function ($u) {
            return $u->isSuperAdmin();
        });

        $this->actingAs($user)
            ->get('/admin/platform/analytics')
            ->assertOk()
            ->assertSee('Analítica')
            ->assertSee('Visitas landing');
    }
}
