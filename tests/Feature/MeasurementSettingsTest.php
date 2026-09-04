<?php

namespace Tests\Feature;

use App\PlatformSetting;
use App\Services\Platform\MeasurementSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MeasurementSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_config_unified_schema_prefers_gtm(): void
    {
        config([
            'measurement.enabled' => true,
            'measurement.gtag_measurement_id' => 'G-ABCDEF123',
            'measurement.gtm_container_id' => 'GTM-ABCDEF1',
            'measurement.plausible_domain' => 'webnu.es',
            'measurement.plausible_upstream_url' => 'https://plausible.io',
        ]);

        $config = app(MeasurementSettingsService::class)->publicConfig();

        $this->assertTrue($config['enabled']);
        $this->assertSame('webnu', $config['brand']);
        $this->assertSame('GTM-ABCDEF1', $config['consented']['gtmId']);
        $this->assertNull($config['consented']['gtagId']);
        $this->assertSame('webnu.es', $config['exempt']['plausibleDomain']);
        $this->assertSame('/stats/js/script.js', $config['exempt']['plausibleScriptUrl']);
    }

    public function test_plausible_alone_does_not_force_cookie_banner(): void
    {
        config([
            'measurement.enabled' => false,
            'measurement.plausible_domain' => 'webnu.es',
            'measurement.plausible_upstream_url' => 'https://plausible.io',
            'measurement.gtag_measurement_id' => null,
            'measurement.gtm_container_id' => null,
            'measurement.clarity_project_id' => null,
        ]);

        $config = app(MeasurementSettingsService::class)->publicConfig();

        $this->assertTrue($config['enabled']);
        $this->assertFalse($config['cookieBanner']);
        $this->assertSame('webnu.es', $config['exempt']['plausibleDomain']);
    }

    public function test_landing_includes_measurement_assets_when_enabled(): void
    {
        config([
            'measurement.enabled' => true,
            'measurement.gtag_measurement_id' => 'G-DK3YFNSMWX',
            'measurement.gtm_container_id' => null,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('webnu-measurement-config', false)
            ->assertSee('measurement.js', false)
            ->assertSee('measurement-consent.css', false)
            ->assertSee('G-DK3YFNSMWX', false)
            ->assertSee('data-manage-cookies', false);
    }

    public function test_plausible_script_proxy_forwards_upstream(): void
    {
        config([
            'measurement.plausible_upstream_url' => 'https://plausible.io',
            'measurement.plausible_domain' => 'webnu.es',
        ]);

        Http::fake([
            'https://plausible.io/js/script.js' => Http::response('/* plausible */', 200, [
                'Content-Type' => 'application/javascript',
            ]),
        ]);

        $this->get(route('stats.plausible.script'))
            ->assertOk()
            ->assertSee('/* plausible */', false);
    }

    public function test_plausible_event_proxy_forwards_body(): void
    {
        config([
            'measurement.plausible_upstream_url' => 'https://plausible.io',
            'measurement.plausible_domain' => 'webnu.es',
        ]);

        Http::fake([
            'https://plausible.io/api/event' => Http::response('ok', 202),
        ]);

        $this->postJson(route('stats.plausible.event'), ['n' => 'pageview', 'd' => 'webnu.es'])
            ->assertStatus(202);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://plausible.io/api/event';
        });
    }

    public function test_plausible_defaults_upstream_when_domain_set(): void
    {
        config([
            'measurement.enabled' => false,
            'measurement.plausible_domain' => 'webnu.es',
            'measurement.plausible_upstream_url' => null,
            'measurement.gtag_measurement_id' => null,
            'measurement.gtm_container_id' => null,
            'measurement.clarity_project_id' => null,
        ]);

        $service = app(MeasurementSettingsService::class);
        $config = $service->publicConfig();

        $this->assertTrue($service->plausibleConfigured());
        $this->assertSame('https://plausible.io', $service->plausibleUpstreamUrl());
        $this->assertTrue($config['enabled']);
        $this->assertSame('webnu.es', $config['exempt']['plausibleDomain']);
        $this->assertSame('/stats/js/script.js', $config['exempt']['plausibleScriptUrl']);
        $this->assertSame('/stats/api/event', $config['exempt']['plausibleApiUrl']);
    }

    public function test_admin_can_persist_plausible_settings(): void
    {
        if (! class_exists(PlatformSetting::class)) {
            $this->markTestSkipped('PlatformSetting unavailable');
        }

        $service = app(MeasurementSettingsService::class);
        $service->update([
            'measurement_enabled' => true,
            'cookie_banner_enabled' => true,
            'load_google_before_consent' => true,
            'plausible_domain' => 'webnu.es',
            'plausible_upstream_url' => 'https://plausible.io',
            'gtag_measurement_id' => 'G-TEST12345',
        ]);

        $this->assertSame('webnu.es', $service->plausibleDomain());
        $this->assertSame('https://plausible.io', $service->plausibleUpstreamUrl());
        $this->assertSame('G-TEST12345', $service->gtagMeasurementId());
    }
}
