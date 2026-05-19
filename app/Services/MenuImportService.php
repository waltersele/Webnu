<?php

namespace App\Services;

use App\Company;
use App\Product;
use App\Section;
use App\Services\MenuScan\MenuScanResult;
use App\Services\AllergenCatalogService;
use Illuminate\Support\Facades\DB;

class MenuImportService
{
    public const MODE_APPEND = 'append';
    public const MODE_REPLACE = 'replace';

    /**
     * @param array{sections?: array} $parsedMenu
     */
    public function import(Company $company, array $parsedMenu, string $mode): int
    {
        $sections = MenuScanResult::normalizeSections($parsedMenu['sections'] ?? []);
        if (count($sections) === 0) {
            throw new \InvalidArgumentException('No hay secciones para importar.');
        }

        $productCount = 0;

        DB::transaction(function () use ($company, $sections, $mode, &$productCount) {
            if ($mode === self::MODE_REPLACE) {
                $sectionIds = Section::where('company_id', $company->id)->pluck('id');
                Product::whereIn('section_id', $sectionIds)->delete();
                Section::where('company_id', $company->id)->delete();
                $sectionOrder = 0;
            } else {
                $sectionOrder = (int) Section::where('company_id', $company->id)->max('order');
            }

            foreach ($sections as $sectionData) {
                $sectionOrder++;
                $section = Section::create([
                    'name' => $sectionData['name'],
                    'order' => $sectionOrder,
                    'enabled' => true,
                    'company_id' => $company->id,
                ]);

                $productOrder = 0;
                foreach ($sectionData['products'] as $productData) {
                    $productOrder++;
                    $product = Product::create([
                        'name' => $productData['name'],
                        'description' => $productData['description'] ?? null,
                        'image' => null,
                        'video' => null,
                        'price_unit' => $productData['price_unit'] ?: '0,00',
                        'price_portion' => $productData['price_portion'] ?? null,
                        'order' => $productOrder,
                        'individual_sale' => false,
                        'weight_sale' => false,
                        'weight_unit_label' => null,
                        'highlight' => false,
                        'enabled' => true,
                        'section_id' => $section->id,
                    ]);

                    $allergenIds = AllergenCatalogService::matchIdsByNames($productData['allergens'] ?? []);
                    if (count($allergenIds) > 0) {
                        $product->allergens()->sync($allergenIds);
                    }

                    $productCount++;
                }
            }
        });

        return $productCount;
    }
}
