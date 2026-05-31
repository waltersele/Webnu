<?php

namespace Tests\Unit;

use App\Company;
use App\MenuPreRegistration;
use App\Services\PreAlta\PreAltaClaimService;
use App\Services\PreAlta\PreAltaIdentityService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PreAltaClaimServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_claim_creates_user_company_and_menu(): void
    {
        $identity = new PreAltaIdentityService();
        $token = $identity->generateClaimToken();

        $registration = MenuPreRegistration::create([
            'restaurant_name' => 'Restaurante Claim Test',
            'menu_json' => [
                'sections' => [
                    [
                        'name' => 'Principal',
                        'products' => [
                            [
                                'name' => 'Paella',
                                'description' => 'Arroz',
                                'price_unit' => '12,00',
                                'allergens' => [],
                            ],
                        ],
                    ],
                ],
            ],
            'public_slug' => $identity->generatePublicSlug(),
            'claim_token_hash' => $token['hash'],
            'status' => MenuPreRegistration::STATUS_PENDING,
            'media_manifest' => [],
            'expires_at' => now()->addDays(20),
        ]);

        $service = app(PreAltaClaimService::class);
        $result = $service->claim($token['plain'], [
            'name' => 'Dueño Test',
            'email' => 'claim-test@webnu.local',
            'password' => 'password123',
        ]);

        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertInstanceOf(Company::class, $result['company']);
        $this->assertTrue(Auth::check());

        $registration->refresh();
        $this->assertEquals(MenuPreRegistration::STATUS_CLAIMED, $registration->status);
        $this->assertSame([], $registration->menu_json);

        $this->assertEquals(1, $result['company']->sections()->count());
        $this->assertEquals(1, $result['company']->sections()->first()->products()->count());
    }
}
