<?php

namespace Tests\Feature;

use App\Company;
use App\PublicSlugRedirect;
use App\Services\CompanySlugService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicUrlSignupTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_simple_public_url(): void
    {
        $user = User::factory()->create(['slug' => 'grupo-brasa']);
        $company = Company::create([
            'name' => 'La Brasa',
            'slug' => 'la-brasa',
            'public_url_format' => 'simple',
            'menu_type' => 1,
            'enabled' => true,
            'reservation' => false,
            'user_id' => $user->id,
        ]);

        $this->assertStringContainsString('/la-brasa', $company->publicUrl());
        $this->assertSame('la-brasa', $company->publicPath());
    }

    public function test_company_nested_public_url(): void
    {
        $user = User::factory()->create(['slug' => 'grupo-brasa']);
        $company = Company::create([
            'name' => 'Terraza',
            'slug' => 'terraza',
            'public_url_format' => 'nested',
            'menu_type' => 1,
            'enabled' => true,
            'reservation' => false,
            'user_id' => $user->id,
        ]);

        $this->assertStringContainsString('/terraza', $company->publicUrl());
        $this->assertSame('terraza', $company->publicPath());
    }

    public function test_simple_hub_route_serves_company_without_redirect_to_nested(): void
    {
        $user = User::factory()->create(['slug' => 'grupo-brasa']);
        Company::create([
            'name' => 'La Brasa',
            'slug' => 'la-brasa',
            'public_url_format' => 'simple',
            'menu_type' => 1,
            'enabled' => true,
            'reservation' => false,
            'user_id' => $user->id,
        ]);

        $response = $this->get('/la-brasa');
        $this->assertTrue(in_array($response->status(), [200, 302], true));
        if ($response->isRedirect()) {
            $this->assertStringNotContainsString('/grupo-brasa/la-brasa', $response->headers->get('Location') ?? '');
        }
    }

    public function test_public_slug_redirect(): void
    {
        PublicSlugRedirect::create([
            'from_path' => 'carta/vieja',
            'to_path' => 'carta/nueva',
            'http_status' => 301,
        ]);

        $user = User::factory()->create(['slug' => 'negocio']);
        Company::create([
            'name' => 'Nueva',
            'slug' => 'nueva',
            'public_url_format' => 'simple',
            'menu_type' => 1,
            'enabled' => true,
            'reservation' => false,
            'user_id' => $user->id,
        ]);

        $response = $this->get('/carta/vieja');
        $response->assertRedirect('/carta/nueva');
        $response->assertStatus(301);
    }

    public function test_company_slug_collides_with_user_slug(): void
    {
        User::factory()->create(['slug' => 'la-brasa']);

        $slugs = app(CompanySlugService::class);
        $this->assertFalse($slugs->isAvailable('la-brasa'));
    }

    public function test_placeholder_slug_detection(): void
    {
        $slugs = app(CompanySlugService::class);
        $this->assertTrue($slugs->isPlaceholderSlug('pendiente-abc123'));
        $this->assertTrue($slugs->isAutoCartaSlug('carta-2'));
    }
}
