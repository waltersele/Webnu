<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingPortalFreemiumTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_sin_stripe_redirige_a_ajustes_plan(): void
    {
        $user = User::factory()->create([
            'onboarding_completed_at' => now(),
            'stripe_id' => null,
        ]);

        $response = $this->actingAs($user)->post(route('admin.billing.portal'));

        $response->assertRedirect(route('admin.settings') . '#plan');
        $response->assertSessionHas('flash_warning');
    }

    public function test_checkout_legacy_devuelve_410(): void
    {
        $this->post(route('pay_product'))->assertStatus(410);
        $this->post(route('process_subscription'))->assertStatus(410);
    }
}
