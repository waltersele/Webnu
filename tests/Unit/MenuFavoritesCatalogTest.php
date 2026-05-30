<?php

namespace Tests\Unit;

use App\Company;
use App\Product;
use App\ProductTranslation;
use App\Section;
use App\Services\MenuFavoritesCatalog;
use App\Services\MenuService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuFavoritesCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_catalog_uses_bilingual_product_names(): void
    {
        $company = $this->createCompanyWithProduct('Tortilla española', 'Spanish omelette');

        $sections = app(MenuService::class)->sectionsForCompany($company, 'en');
        $catalog = app(MenuFavoritesCatalog::class)->build($company, $sections, 'en');

        $product = $company->sections()->first()->products()->first();
        $entry = $catalog['products'][(string) $product->id];

        $this->assertSame('Spanish omelette', $entry['nameLocale']);
        $this->assertSame('Tortilla española', $entry['nameOriginal']);
        $this->assertNotSame($entry['nameLocale'], $entry['nameOriginal']);
        $this->assertSame('en', $catalog['menuLocale']);
        $this->assertSame('es', $catalog['defaultLocale']);
    }

    public function test_build_catalog_formats_price_label(): void
    {
        $company = new Company([
            'id' => 99,
            'default_locale' => 'es',
        ]);

        $product = new Product([
            'id' => 7,
            'name' => 'Ensalada',
            'price_unit' => '9,50',
            'enabled' => true,
        ]);
        $product->setAttribute('name_locale', 'Ensalada');
        $product->setAttribute('name_original', 'Ensalada');

        $section = new Section(['name' => 'Entrantes']);
        $section->setRelation('products', collect([$product]));

        $catalog = app(MenuFavoritesCatalog::class)->build($company, collect([$section]), 'es');
        $entry = $catalog['products']['7'];

        $this->assertSame('9,50 €', $entry['priceLabel']);
        $this->assertSame(99, $catalog['companyId']);
    }

    private function createCompanyWithProduct(string $defaultName, string $englishName): Company
    {
        $user = User::factory()->create(['slug' => 'owner-' . uniqid()]);

        $company = Company::create([
            'name' => 'Restaurante test',
            'slug' => 'fav-catalog-' . uniqid(),
            'template' => 'basic',
            'menu_type' => 1,
            'enabled' => true,
            'user_id' => $user->id,
            'default_locale' => 'es',
            'menu_favorites_enabled' => true,
        ]);

        $section = Section::create([
            'name' => 'Principales',
            'order' => 1,
            'enabled' => true,
            'company_id' => $company->id,
        ]);

        $product = Product::create([
            'name' => $defaultName,
            'description' => '',
            'price_unit' => '12,00',
            'price_portion' => null,
            'individual_sale' => true,
            'order' => 1,
            'enabled' => true,
            'section_id' => $section->id,
        ]);

        ProductTranslation::create([
            'product_id' => $product->id,
            'locale' => 'en',
            'name' => $englishName,
            'description' => '',
            'source' => ProductTranslation::SOURCE_MANUAL,
        ]);

        return $company->fresh();
    }
}
