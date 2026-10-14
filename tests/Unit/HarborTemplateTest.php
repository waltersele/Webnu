<?php

namespace Tests\Unit;

use App\Company;
use Tests\TestCase;

class HarborTemplateTest extends TestCase
{
    public function test_harbor_is_registered_with_surface_mode_and_gallery(): void
    {
        $meta = config('company_templates.templates.harbor');

        $this->assertIsArray($meta);
        $this->assertTrue($meta['gallery']);
        $this->assertTrue($meta['surface_mode']);
        $this->assertSame('minimal_bar', config('company_templates.template_hero.harbor'));
        $this->assertArrayHasKey('dark', config('company_templates.surface_modes.harbor'));
        $this->assertArrayHasKey('light', config('company_templates.surface_modes.harbor'));
    }

    public function test_harbor_light_mode_resolves_palette(): void
    {
        $company = new Company([
            'template' => 'harbor',
            'theme_settings' => ['surface_mode' => 'light'],
        ]);

        $theme = $company->resolvedThemeSettings();

        $this->assertSame('light', $theme['surface_mode']);
        $this->assertSame('#f7f2e8', $theme['background']);
    }
}
