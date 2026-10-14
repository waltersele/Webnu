<?php

namespace Tests\Unit;

use App\Company;
use Tests\TestCase;

class RailTemplateTest extends TestCase
{
    public function test_rail_is_registered_with_surface_mode_and_gallery(): void
    {
        $meta = config('company_templates.templates.rail');

        $this->assertIsArray($meta);
        $this->assertTrue($meta['gallery']);
        $this->assertTrue($meta['surface_mode']);
        $this->assertSame('minimal_bar', config('company_templates.template_hero.rail'));
        $this->assertArrayHasKey('dark', config('company_templates.surface_modes.rail'));
        $this->assertArrayHasKey('light', config('company_templates.surface_modes.rail'));
    }

    public function test_rail_light_mode_resolves_palette(): void
    {
        $company = new Company([
            'template' => 'rail',
            'theme_settings' => ['surface_mode' => 'light'],
        ]);

        $theme = $company->resolvedThemeSettings();

        $this->assertSame('light', $theme['surface_mode']);
        $this->assertSame('#ffffff', $theme['background']);
    }
}
