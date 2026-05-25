<?php

namespace Tests\Feature;

use App\Company;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Registro → onboarding paso 1
    // -----------------------------------------------------------------------

    public function test_registro_redirige_a_onboarding()
    {
        $response = $this->post('/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'business_name'         => 'Mi Restaurante Test',
        ]);

        $response->assertRedirect(route('admin.onboarding'));

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertNull($user->onboarding_completed_at);
        $this->assertNotNull($user->slug);
        $this->assertTrue($user->companies()->exists(), 'El registro debe crear una empresa por defecto');
    }

    public function test_onboarding_muestra_paso_1()
    {
        $user = $this->createUserWithCompany();

        $response = $this->actingAs($user)->get(route('admin.onboarding'));

        $response->assertStatus(200);
        $response->assertSee('step', false);
    }

    // -----------------------------------------------------------------------
    // Middleware: bloquea panel hasta completar onboarding
    // -----------------------------------------------------------------------

    public function test_middleware_bloquea_panel_sin_onboarding_completo()
    {
        $user = $this->createUserWithCompany();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.onboarding'));
    }

    public function test_middleware_permite_acceso_a_rutas_de_whitelist()
    {
        $user = $this->createUserWithCompany();

        // El listado de cartas debe ser accesible aunque el onboarding esté pendiente
        $response = $this->actingAs($user)->get(route('admin.companies.index'));

        $response->assertStatus(200);
    }

    // -----------------------------------------------------------------------
    // Paso 2: nombre del negocio
    // -----------------------------------------------------------------------

    public function test_onboarding_paso2_actualiza_nombre_empresa()
    {
        $user = $this->createUserWithCompany();

        $response = $this->actingAs($user)->post(route('admin.onboarding.update'), [
            'step' => 2,
            'name' => 'Casa María',
        ]);

        $response->assertRedirect(route('admin.onboarding', ['step' => 3]));

        $company = $user->companies()->first();
        $this->assertEquals('Casa María', $company->fresh()->name);
    }

    // -----------------------------------------------------------------------
    // Validación de step: valores fuera de rango
    // -----------------------------------------------------------------------

    public function test_onboarding_step_fuera_de_rango_devuelve_error()
    {
        $user = $this->createUserWithCompany();

        $response = $this->actingAs($user)->post(route('admin.onboarding.update'), [
            'step' => 99,
        ]);

        $response->assertSessionHasErrors('step');
    }

    // -----------------------------------------------------------------------
    // Paso 6: completa onboarding y desbloquea panel
    // -----------------------------------------------------------------------

    public function test_onboarding_paso6_completa_y_desbloquea_panel()
    {
        $user = $this->createUserWithCompany();

        $response = $this->actingAs($user)->post(route('admin.onboarding.update'), [
            'step' => 6,
        ]);

        $user->refresh();

        $this->assertNotNull($user->onboarding_completed_at, 'El onboarding debe marcarse como completado');

        $company = $user->companies()->first();
        $this->assertTrue((bool) $company->fresh()->enabled, 'La empresa debe quedar habilitada al publicar');

        // Tras completar, el panel debe ser accesible
        $panel = $this->actingAs($user)->get(route('admin.dashboard'));
        $panel->assertStatus(200);
    }

    // -----------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------

    private function createUserWithCompany(): User
    {
        $user = User::factory()->create([
            'plan'                    => 'free',
            'onboarding_step'         => 1,
            'onboarding_completed_at' => null,
            'slug'                    => 'test-user-' . uniqid(),
        ]);

        Company::create([
            'name'      => 'Mi Restaurante',
            'slug'      => 'mi-restaurante-' . $user->id,
            'template'  => 'lumiere',
            'menu_type' => 1,
            'enabled'   => false,
            'user_id'   => $user->id,
        ]);

        return $user;
    }
}
