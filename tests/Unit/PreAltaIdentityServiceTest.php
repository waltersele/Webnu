<?php

namespace Tests\Unit;

use App\MenuPreRegistration;
use App\Services\PreAlta\PreAltaIdentityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreAltaIdentityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_unique_slug_and_token_hash(): void
    {
        $service = new PreAltaIdentityService();

        $slug = $service->generatePublicSlug();
        $this->assertStringStartsWith('pa-', $slug);

        $token = $service->generateClaimToken();
        $this->assertEquals(64, strlen($token['plain']));
        $this->assertEquals(MenuPreRegistration::hashClaimToken($token['plain']), $token['hash']);
    }
}
