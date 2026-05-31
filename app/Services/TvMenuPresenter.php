<?php

namespace App\Services;

use App\Company;
use App\Menu;
use App\Services\MenuSyncService;
use App\Services\Tv\TvTemplateRegistry;
use Illuminate\Support\Collection;

class TvMenuPresenter
{
    public function present(Company $company, string $layout, MenuService $menuService, string $locale): array
    {
        $sections = $menuService->sectionsForCompany($company, $locale)
            ->filter(function ($section) {
                return (bool) $section->enabled;
            });

        $products = $sections->flatMap(function ($section) {
            return $section->products->filter(function ($product) {
                return (bool) $product->enabled;
            });
        });

        $highlights = $products->filter(function ($product) {
            return ! empty($product->highlight) || ! empty($product->image);
        })->take(12)->values();

        $featured = $products->filter(function ($product) {
            return ! empty($product->highlight);
        })->values();

        if ($featured->isEmpty()) {
            $featured = $highlights->take(8);
        }

        $videos = $products->filter(function ($product) {
            return ! empty($product->video);
        })->values();

        $themeTokens = $this->themeTokens($company);
        $registry = app(TvTemplateRegistry::class);
        $templateMeta = $registry->templateByLayout($layout) ?? [];

        $isPlayerMode = request()->boolean('player');
        $menuSync = app(MenuSyncService::class);

        $menus = $this->menusData($company);
        $activeMenu = $this->resolveActiveMenu($company, $menus);

        return [
            'company' => $company,
            'layout' => $layout,
            'templateMeta' => $templateMeta,
            'rotateSeconds' => $registry->rotateSeconds($layout),
            'showHeader' => $registry->showHeader($layout),
            'isPlayerMode' => $isPlayerMode,
            'syncVersion' => $menuSync->syncVersion($company),
            'syncUrl' => route('tv.sync', ['companySlug' => $company->slug]),
            'playerPollSeconds' => (int) config('tvpik.player_poll_seconds', 30),
            'locale' => $locale,
            'sections' => $sections,
            'highlights' => $highlights,
            'featured' => $featured,
            'videos' => $videos,
            'spotlight' => $this->spotlightData($company),
            'menus' => $menus,
            'activeMenu' => $activeMenu,
            'logoUrl' => $company->logo ? url('img/' . ltrim($company->logo, '/')) : null,
            'headerUrl' => $company->background_header
                ? url('img/' . ltrim($company->background_header, '/'))
                : null,
            'accent' => $themeTokens['accent'],
            'themeAccent' => $themeTokens['themeAccent'],
            'themeBg' => $themeTokens['themeBg'],
            'themeSurface' => $themeTokens['themeSurface'],
            'themeText' => $themeTokens['themeText'],
            'themeTextMuted' => $themeTokens['themeTextMuted'],
            'themeFontDisplay' => $themeTokens['themeFontDisplay'],
            'themeFontBody' => $themeTokens['themeFontBody'],
            'themeBadgeFg' => $themeTokens['themeBadgeFg'],
            'isPreview' => request()->boolean('preview'),
        ];
    }

    /**
     * Tokens de tema carta → TV (Personalización en admin).
     *
     * @return array<string, string>
     */
    protected function themeTokens(Company $company): array
    {
        $settings = $company->resolvedThemeSettings();
        $primary = $settings['primary'] ?? '#004ac6';
        $accent = $settings['accent'] ?? $primary;

        return [
            'accent' => $primary,
            'themeAccent' => $accent,
            'themeBg' => $settings['background'] ?? '#0a0e14',
            'themeSurface' => $settings['surface'] ?? 'rgba(255, 255, 255, 0.06)',
            'themeText' => $settings['text'] ?? '#f5f7fa',
            'themeTextMuted' => $settings['text_muted'] ?? 'rgba(245, 247, 250, 0.65)',
            'themeFontDisplay' => $company->themeFontFamily('font_heading'),
            'themeFontBody' => $company->themeFontFamily('font_body'),
            'themeBadgeFg' => $this->contrastingTextColor($accent),
        ];
    }

    protected function contrastingTextColor(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            return '#0f0e0d';
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $lum = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;

        return $lum < 0.55 ? '#f5f7fa' : '#0f0e0d';
    }

    protected function menusData(Company $company): Collection
    {
        return $company->menus()
            ->where('enabled', true)
            ->with([
                'sections' => function ($q) {
                    $q->orderBy('position');
                },
                'sections.items' => function ($q) {
                    $q->orderBy('position');
                },
                'sections.items.product',
            ])
            ->orderBy('position')
            ->get();
    }

    protected function resolveActiveMenu(Company $company, Collection $menus): ?Menu
    {
        $requested = request()->query('menu');
        if ($requested && ctype_digit((string) $requested)) {
            $match = $menus->firstWhere('id', (int) $requested);
            if ($match) {
                return $match;
            }
        }

        return null;
    }

    public function menuHeroImage(Menu $menu): ?string
    {
        if (! empty($menu->image)) {
            return $menu->imageUrl();
        }

        foreach ($menu->sections as $section) {
            foreach ($section->items as $item) {
                $img = $item->imageUrl();
                if ($img) {
                    return $img;
                }
            }
        }

        return null;
    }

    protected function spotlightData(Company $company): ?array
    {
        $items = $company->resolvedDailyHighlights();
        if (count($items) === 0) {
            return null;
        }

        $first = $items[0];

        return [
            'label' => $first['label'],
            'text' => $first['text'],
            'price' => $first['price'],
        ];
    }

    public function productImageUrl($product): ?string
    {
        if (empty($product->image)) {
            return null;
        }

        return url('img/' . ltrim($product->image, '/'));
    }

    public function productVideoUrl($product): ?string
    {
        if (empty($product->video)) {
            return null;
        }

        return url('img/' . ltrim($product->video, '/'));
    }

    public function formatPrice($product): ?string
    {
        if ($product->weight_sale && $product->price_unit) {
            return number_format((float) $product->price_unit, 2, ',', '.') . ' €'
                . ($product->weight_unit_label ? ' / ' . $product->weight_unit_label : '');
        }

        if ($product->individual_sale && $product->price_portion) {
            return number_format((float) $product->price_portion, 2, ',', '.') . ' €';
        }

        if ($product->price_unit) {
            return number_format((float) $product->price_unit, 2, ',', '.') . ' €';
        }

        return null;
    }
}
