<?php

namespace Tests\Unit;

use App\Company;
use App\Services\CompanySlugService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySlugServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CompanySlugService $slugs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->slugs = app(CompanySlugService::class);
    }

    protected function createCompany(string $name, string $slug): Company
    {
        $user = User::factory()->create();

        return Company::create([
            'name' => $name,
            'slug' => $slug,
            'menu_type' => 1,
            'enabled' => true,
            'reservation' => false,
            'user_id' => $user->id,
        ]);
    }

    public function test_generates_slug_from_name(): void
    {
        $this->assertSame('la-brasa-del-puerto', $this->slugs->generateFromName('La Brasa del Puerto'));
    }

    public function test_appends_increment_when_name_is_taken(): void
    {
        $this->createCompany('La Brasa', 'la-brasa');

        $this->assertSame('la-brasa-2', $this->slugs->generateFromName('La Brasa'));
    }

    public function test_prefers_city_suffix_before_numeric_increment(): void
    {
        $this->createCompany('La Brasa', 'la-brasa');

        $this->assertSame('la-brasa-valencia', $this->slugs->generateFromName('La Brasa', 'Valencia'));
    }

    public function test_rejects_reserved_slugs(): void
    {
        $this->assertSame('Esa URL está reservada. Elige otra.', $this->slugs->validateCustomSlug('admin'));
    }
}
