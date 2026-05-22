<?php

namespace App\Services;

use App\Company;
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

        $settings = $company->resolvedThemeSettings();
        $registry = app(TvTemplateRegistry::class);
        $templateMeta = $registry->templateByLayout($layout) ?? [];

        $isPlayerMode = request()->boolean('player');
        $menuSync = app(MenuSyncService::class);

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
            'logoUrl' => $company->logo ? url('img/' . ltrim($company->logo, '/')) : null,
            'headerUrl' => $company->background_header
                ? url('img/' . ltrim($company->background_header, '/'))
                : null,
            'accent' => $settings['primary'] ?? '#004ac6',
            'isPreview' => request()->boolean('preview'),
        ];
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
