<?php

namespace Tests\Unit;

use App\Company;
use App\Services\CompanyThemeService;
use Illuminate\Http\Request;
use Tests\TestCase;

class AmbreTemplateTest extends TestCase
{
    public function test_ambre_is_registered_with_surface_mode(): void
    {
        $meta = config('company_templates.templates.ambre');

        $this->assertIsArray($meta);
        $this->assertTrue($meta['gallery']);
        $this->assertTrue($meta['surface_mode']);
        $this->assertSame('spotlight_dish', config('company_templates.template_hero.ambre'));
        $this->assertArrayHasKey('dark', config('company_templates.surface_modes.ambre'));
        $this->assertArrayHasKey('light', config('company_templates.surface_modes.ambre'));
    }

    public function test_resolved_theme_applies_light_surface_mode(): void
    {
        $company = new Company([
            'template' => 'ambre',
            'theme_settings' => ['surface_mode' => 'light'],
        ]);

        $theme = $company->resolvedThemeSettings();

        $this->assertSame('light', $theme['surface_mode']);
        $this->assertSame('#ffffff', $theme['background']);
        $this->assertSame('#0a0a0a', $theme['text']);
    }

    public function test_theme_service_normalizes_surface_mode(): void
    {
        $service = new CompanyThemeService();
        $request = Request::create('/', 'POST', [
            'template' => 'ambre',
            'theme_surface_mode' => 'light',
            'theme_primary' => '#d97706',
        ]);

        $normalized = $service->normalizeFromRequest($request);

        $this->assertSame('light', $normalized['surface_mode']);
        $this->assertSame('#ffffff', $normalized['background']);
        $this->assertSame('#d97706', $normalized['primary']);
    }
}
