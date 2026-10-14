<?php

namespace Tests\Unit;

use App\Company;
use Tests\TestCase;

class DeckTemplateTest extends TestCase
{
    public function test_deck_is_registered_with_surface_mode_and_gallery(): void
    {
        $meta = config('company_templates.templates.deck');

        $this->assertIsArray($meta);
        $this->assertTrue($meta['gallery']);
        $this->assertTrue($meta['surface_mode']);
        $this->assertSame('minimal_bar', config('company_templates.template_hero.deck'));
        $this->assertArrayHasKey('dark', config('company_templates.surface_modes.deck'));
        $this->assertArrayHasKey('light', config('company_templates.surface_modes.deck'));
    }

    public function test_deck_light_mode_resolves_palette(): void
    {
        $company = new Company([
            'template' => 'deck',
            'theme_settings' => ['surface_mode' => 'light'],
        ]);

        $theme = $company->resolvedThemeSettings();

        $this->assertSame('light', $theme['surface_mode']);
        $this->assertSame('#f6f1ea', $theme['background']);
    }
}
