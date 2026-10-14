<?php

namespace Tests\Unit;

use App\Company;
use Tests\TestCase;

class SwipeTemplateTest extends TestCase
{
    public function test_swipe_is_registered_with_surface_mode_and_gallery(): void
    {
        $meta = config('company_templates.templates.swipe');

        $this->assertIsArray($meta);
        $this->assertTrue($meta['gallery']);
        $this->assertTrue($meta['surface_mode']);
        $this->assertSame('minimal_bar', config('company_templates.template_hero.swipe'));
        $this->assertArrayHasKey('dark', config('company_templates.surface_modes.swipe'));
        $this->assertArrayHasKey('light', config('company_templates.surface_modes.swipe'));
    }

    public function test_swipe_light_mode_resolves_palette(): void
    {
        $company = new Company([
            'template' => 'swipe',
            'theme_settings' => ['surface_mode' => 'light'],
        ]);

        $theme = $company->resolvedThemeSettings();

        $this->assertSame('light', $theme['surface_mode']);
        $this->assertSame('#f7f7f7', $theme['background']);
    }
}
