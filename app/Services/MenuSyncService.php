<?php

namespace App\Services;

use App\Company;
use App\Product;
use App\Section;
use Carbon\Carbon;

class MenuSyncService
{
    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function companiesPayload(int $userId): array
    {
        $companies = Company::where('user_id', $userId)
            ->orderBy('name')
            ->get();

        return $companies->map(function (Company $company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'slug' => $company->slug,
                'enabled' => (bool) $company->enabled,
                'menu_type' => (int) $company->menu_type,
                'template' => $company->template,
                'sync_version' => $this->syncVersion($company),
                'public_url' => route('see_menu', $company->slug),
                'api_url' => url('/api/signage/menus/' . $company->slug),
            ];
        })->values()->all();
    }

    public function menuPayload(Company $company): array
    {
        $onlyEnabled = (bool) config('digital_signage.only_enabled');

        if ((int) $company->menu_type === 2) {
            return [
                'api_version' => config('digital_signage.api_version'),
                'sync_version' => $this->syncVersion($company),
                'menu_type' => 'pdf',
                'company' => $this->companyMeta($company),
                'pdf_url' => $company->menu_type_2_pdf
                    ? $this->assetUrl($company->menu_type_2_pdf)
                    : null,
                'sections' => [],
            ];
        }

        $sections = $this->menuService->sectionsForCompany($company);

        if ($onlyEnabled) {
            $sections = $sections->filter(function ($section) {
                return (bool) $section->enabled;
            });
        }

        return [
            'api_version' => config('digital_signage.api_version'),
            'sync_version' => $this->syncVersion($company),
            'menu_type' => 'custom',
            'company' => $this->companyMeta($company),
            'pdf_url' => null,
            'sections' => $sections->map(function ($section) use ($onlyEnabled) {
                $products = $section->products;

                if ($onlyEnabled) {
                    $products = $products->filter(function ($product) {
                        return (bool) $product->enabled;
                    });
                }

                return [
                    'id' => $section->id,
                    'name' => $section->name,
                    'order' => (int) $section->order,
                    'enabled' => (bool) $section->enabled,
                    'products' => $products->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'description' => $product->description,
                            'price_unit' => $product->price_unit,
                            'price_portion' => $product->price_portion,
                            'individual_sale' => (bool) $product->individual_sale,
                            'weight_sale' => (bool) $product->weight_sale,
                            'weight_unit_label' => $product->weight_unit_label,
                            'highlight' => $product->highlight,
                            'enabled' => (bool) $product->enabled,
                            'order' => (int) $product->order,
                            'image_url' => $product->image
                                ? $this->assetUrl($product->image)
                                : null,
                            'video_url' => $product->video
                                ? $this->assetUrl($product->video)
                                : null,
                            'allergens' => $product->allergens->map(function ($allergen) {
                                return [
                                    'id' => $allergen->id,
                                    'name' => $allergen->name,
                                    'image_url' => $allergen->image
                                        ? $this->assetUrl($allergen->image)
                                        : null,
                                ];
                            })->values()->all(),
                        ];
                    })->values()->all(),
                ];
            })->values()->all(),
        ];
    }

    public function syncVersion(Company $company): string
    {
        $timestamps = collect([$company->updated_at]);

        $sectionUpdated = Section::where('company_id', $company->id)->max('updated_at');
        if ($sectionUpdated) {
            $timestamps->push(Carbon::parse($sectionUpdated));
        }

        $productUpdated = Product::whereHas('section', function ($query) use ($company) {
            $query->where('company_id', $company->id);
        })->max('updated_at');

        if ($productUpdated) {
            $timestamps->push(Carbon::parse($productUpdated));
        }

        $latest = $timestamps->filter()->max();

        if (!$latest) {
            $latest = $company->updated_at ?? Carbon::now();
        }

        return $latest->toIso8601String();
    }

    protected function companyMeta(Company $company): array
    {
        return [
            'id' => $company->id,
            'name' => $company->name,
            'slug' => $company->slug,
            'chef_name' => $company->chef_name,
            'enabled' => (bool) $company->enabled,
            'template' => $company->template,
            'schedule' => $company->schedule,
            'comments' => $company->comments,
            'logo_url' => $company->logo ? $this->assetUrl($company->logo) : null,
            'header_url' => $company->background_header
                ? $this->assetUrl($company->background_header)
                : null,
            'public_url' => route('see_menu', $company->slug),
        ];
    }

    protected function assetUrl(string $path): string
    {
        return url('img/' . ltrim($path, '/'));
    }
}
