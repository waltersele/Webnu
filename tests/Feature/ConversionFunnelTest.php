<?php

namespace Tests\Feature;

use App\Company;
use App\Services\Lifecycle\LifecycleMailer;
use App\Services\Platform\PlatformAnalyticsRecorder;
use App\User;
use App\UserLifecycleEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ConversionFunnelTest extends TestCase
{
    use RefreshDatabase;

    public function test_beacon_records_named_cta(): void
    {
        $this->postJson('/analytics/beacon', [
            'event' => 'cta',
            'path' => '/',
            'cta' => 'auth_register_submit',
        ])->assertNoContent();

        $this->assertDatabaseHas('platform_analytics_dailies', [
            'metric' => PlatformAnalyticsRecorder::METRIC_LANDING_CTA,
            'dimension' => 'auth_register_submit',
        ]);
    }

    public function test_onboarding_step_records_funnel_event(): void
    {
        $user = User::factory()->create([
            'slug' => 'owner-funnel',
            'onboarding_step' => 1,
            'onboarding_completed_at' => null,
            'trial_ends_at' => now()->addDays(30),
            'trial_plan_key' => 'pro',
            'plan' => 'free',
        ]);
        Company::create([
            'name' => 'Mi restaurante',
            'slug' => 'funnel-carta-' . uniqid(),
            'template' => 'lumiere',
            'menu_type' => 1,
            'enabled' => false,
            'reservation' => false,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->post(route('admin.onboarding.update'), [
            'step' => 2,
            'business_name' => 'Casa Funnel',
            'name' => 'Casa Funnel',
            'company_slug' => 'casa-funnel-' . substr(uniqid(), -6),
        ])->assertRedirect();

        $this->assertDatabaseHas('platform_analytics_dailies', [
            'metric' => PlatformAnalyticsRecorder::METRIC_ONBOARDING_STEP,
            'dimension' => '2',
        ]);
    }

    public function test_company_public_qr_url_has_utm_medium(): void
    {
        $user = User::factory()->create(['slug' => 'owner-qr']);
        $company = Company::create([
            'name' => 'QR Test',
            'slug' => 'qr-test-' . substr(uniqid(), -6),
            'public_url_format' => 'simple',
            'menu_type' => 1,
            'enabled' => true,
            'reservation' => false,
            'user_id' => $user->id,
        ]);

        $url = $company->publicQrUrl();
        $this->assertStringContainsString('utm_medium=qr', $url);
        $this->assertStringContainsString('utm_source=webnu', $url);
    }

    public function test_lifecycle_welcome_does_not_duplicate(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'lifecycle@example.com',
            'slug' => 'lifecycle-user',
        ]);

        $mailer = app(LifecycleMailer::class);
        $this->assertTrue($mailer->sendWelcome($user));
        $this->assertFalse($mailer->sendWelcome($user));

        $this->assertSame(1, UserLifecycleEmail::where('user_id', $user->id)
            ->where('template_key', LifecycleMailer::KEY_WELCOME)
            ->count());

        Mail::assertSent(\App\Mail\Lifecycle\WelcomeMail::class, 1);
    }

    public function test_login_layout_includes_analytics_beacon(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('webnu-analytics.js', false);
    }

    public function test_register_page_has_cta_attribute(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('data-wn-analytics-cta="auth_register_submit"', false);
    }
}
