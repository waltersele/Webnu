<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Cashier;
use Tests\TestCase;

class CashierCustomerModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_usa_app_user_como_modelo_de_cliente(): void
    {
        $this->assertSame(User::class, Cashier::$customerModel);
    }

    public function test_find_billable_resuelve_el_usuario_por_stripe_id(): void
    {
        $user = User::factory()->create(['stripe_id' => 'cus_test_123']);

        $billable = Cashier::findBillable('cus_test_123');

        $this->assertInstanceOf(User::class, $billable);
        $this->assertSame($user->id, $billable->id);
    }
}
