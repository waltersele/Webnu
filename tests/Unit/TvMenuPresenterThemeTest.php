<?php

namespace Tests\Unit;

use App\Company;
use App\Services\TvMenuPresenter;
use Tests\TestCase;

class TvMenuPresenterThemeTest extends TestCase
{
    public function test_theme_tokens_map_company_settings_to_tv_variables(): void
    {
        $company = new Company([
            'template' => 'maison',
            'theme_settings' => [
                'primary' => '#f5f3ef',
                'accent' => '#c4a574',
                'background' => '#1a1816',
                'surface' => '#2a2622',
                'text' => '#f3eee3',
                'text_muted' => '#a89f92',
                'font_heading' => 'playfair',
                'font_body' => 'inter',
            ],
        ]);

        $presenter = new TvMenuPresenter();
        $method = new \ReflectionMethod(TvMenuPresenter::class, 'themeTokens');
        $method->setAccessible(true);
        $tokens = $method->invoke($presenter, $company);

        $this->assertSame('#f5f3ef', $tokens['accent']);
        $this->assertSame('#c4a574', $tokens['themeAccent']);
        $this->assertSame('#1a1816', $tokens['themeBg']);
        $this->assertSame('#2a2622', $tokens['themeSurface']);
        $this->assertSame('#f3eee3', $tokens['themeText']);
        $this->assertSame('#a89f92', $tokens['themeTextMuted']);
        $this->assertStringContainsString('Playfair Display', $tokens['themeFontDisplay']);
        $this->assertStringContainsString('Inter', $tokens['themeFontBody']);
        $this->assertSame('#0f0e0d', $tokens['themeBadgeFg']);
    }

    public function test_badge_foreground_uses_light_text_on_dark_accent(): void
    {
        $presenter = new TvMenuPresenter();
        $method = new \ReflectionMethod(TvMenuPresenter::class, 'contrastingTextColor');
        $method->setAccessible(true);

        $this->assertSame('#f5f7fa', $method->invoke($presenter, '#004ac6'));
        $this->assertSame('#0f0e0d', $method->invoke($presenter, '#f5f3ef'));
    }
}
