<?php

namespace Tests\Feature;

use App\Company;
use App\Product;
use App\Section;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuFavoritesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_menu_includes_favorites_assets_when_enabled(): void
    {
        $company = $this->createDigitalMenuCompany(true);

        $response = $this->get(route('public.company', ['companySlug' => $company->slug]));

        $response->assertOk();
        $response->assertSee('id="webnu-favorites-catalog"', false);
        $response->assertSee('webnu-menu-favorites.js', false);
        $response->assertSee('data-fav-toggle', false);
        $response->assertSee('wn-favorites-bar', false);
    }

    public function test_public_menu_hides_favorites_when_disabled(): void
    {
        $company = $this->createDigitalMenuCompany(false);

        $response = $this->get(route('public.company', ['companySlug' => $company->slug]));

        $response->assertOk();
        $response->assertDontSee('class="wn-fav-btn"', false);
        $response->assertDontSee('id="webnu-favorites-catalog"', false);
    }

    private function createDigitalMenuCompany(bool $favoritesEnabled): Company
    {
        $user = User::factory()->create(['slug' => 'owner-' . uniqid()]);

        $company = Company::create([
            'name' => 'Carta favoritos test',
            'slug' => 'fav-feature-' . uniqid(),
            'template' => 'pasion',
            'menu_type' => 1,
            'enabled' => true,
            'user_id' => $user->id,
            'default_locale' => 'es',
            'menu_favorites_enabled' => $favoritesEnabled,
        ]);

        $section = Section::create([
            'name' => 'Carta',
            'order' => 1,
            'enabled' => true,
            'company_id' => $company->id,
        ]);

        Product::create([
            'name' => 'Plato de prueba',
            'description' => 'Descripción',
            'price_unit' => '10,00',
            'price_portion' => null,
            'individual_sale' => true,
            'order' => 1,
            'enabled' => true,
            'section_id' => $section->id,
        ]);

        return $company;
    }
}
