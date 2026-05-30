<?php

namespace App\Services;

use App\Company;
use App\Product;
use Illuminate\Support\Collection;

class MenuFavoritesCatalog
{
    /**
     * @param  \Illuminate\Support\Collection<int, \App\Section>  $sections
     * @return array<string, mixed>
     */
    public function build(Company $company, Collection $sections, string $menuLocale): array
    {
        $defaultLocale = $company->defaultLocale();
        $supported = config('menu_locales.supported', []);
        $localeLabels = [];

        foreach ($supported as $code => $meta) {
            $localeLabels[$code] = $meta['native'] ?? $meta['label'] ?? strtoupper($code);
        }

        $products = [];

        foreach ($sections as $section) {
            $sectionName = (string) $section->name;

            foreach ($section->products as $product) {
                if (! $product->id || (int) $product->id <= 0) {
                    continue;
                }

                $products[(string) $product->id] = [
                    'id' => (int) $product->id,
                    'nameLocale' => (string) ($product->name_locale ?? $product->name),
                    'nameOriginal' => (string) ($product->name_original ?? $product->name),
                    'imageUrl' => $this->imageUrl($product),
                    'priceLabel' => $this->formatPriceLabel($product),
                    'sectionName' => $sectionName,
                ];
            }
        }

        return [
            'companyId' => (int) $company->id,
            'defaultLocale' => $defaultLocale,
            'menuLocale' => $menuLocale,
            'localeLabels' => $localeLabels,
            'products' => $products,
        ];
    }

    protected function imageUrl(Product $product): ?string
    {
        $path = $product->display_image ?? $product->image;

        return $path ? asset('img/' . ltrim($path, '/')) : null;
    }

    protected function formatPriceLabel(Product $product): ?string
    {
        $parts = [];

        if ($product->price_portion) {
            $parts[] = 'Media: ' . $product->price_portion . ' €';
        }

        if ($product->price_unit) {
            $label = ($product->price_portion ? 'Entera: ' : '') . $product->price_unit . ' €';
            if ($product->weight_sale && $product->weight_unit_label) {
                $label .= ' / ' . $product->weight_unit_label;
            }
            $parts[] = $label;
        }

        return $parts !== [] ? implode(' · ', $parts) : null;
    }
}
