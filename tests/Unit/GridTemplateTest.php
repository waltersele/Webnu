<?php

namespace Tests\Unit;

use App\Company;
use Tests\TestCase;

class GridTemplateTest extends TestCase
{
    public function test_grid_is_registered_with_surface_mode_and_gallery(): void
    {
        $meta = config('company_templates.templates.grid');

        $this->assertIsArray($meta);
        $this->assertTrue($meta['gallery']);
        $this->assertTrue($meta['surface_mode']);
        $this->assertSame('minimal_bar', config('company_templates.template_hero.grid'));
        $this->assertArrayHasKey('dark', config('company_templates.surface_modes.grid'));
        $this->assertArrayHasKey('light', config('company_templates.surface_modes.grid'));
    }

    public function test_grid_dark_mode_resolves_palette(): void
    {
        $company = new Company([
            'template' => 'grid',
            'theme_settings' => ['surface_mode' => 'dark'],
        ]);

        $theme = $company->resolvedThemeSettings();

        $this->assertSame('dark', $theme['surface_mode']);
        $this->assertSame('#101410', $theme['background']);
    }
}
