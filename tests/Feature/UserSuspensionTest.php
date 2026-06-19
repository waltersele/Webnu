<?php

namespace Tests\Feature;

use App\Company;
use App\Services\Platform\UserDeletionService;
use App\Services\Platform\UserSuspensionService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserSuspensionTest extends TestCase
{
    use RefreshDatabase;

    public function test_carta_suspendida_muestra_overlay_publico(): void
    {
        $user = User::factory()->create(['slug' => 'owner-test']);
        $company = Company::create([
            'name' => 'Restaurante Demo',
            'slug' => 'restaurante-demo',
            'public_url_format' => 'simple',
            'menu_type' => 1,
            'template' => 'lumiere',
            'enabled' => true,
            'reservation' => false,
            'user_id' => $user->id,
        ]);

        app(UserSuspensionService::class)->suspend($user, 'trial_expired');

        $response = $this->get('/' . $company->slug);

        $response->assertStatus(403);
        $response->assertSee('Parece que este usuario ya no está usando Webnu', false);
        $response->assertSee('Restaurante Demo', false);
        $response->assertSee('wn-suspended-overlay', false);
    }

    public function test_login_suspendido_redirige_a_cuenta_suspendida(): void
    {
        $user = User::factory()->create([
            'email' => 'suspendido@test.com',
            'password' => Hash::make('password123'),
            'suspended_at' => now(),
            'suspended_reason' => 'trial_expired',
        ]);

        $response = $this->post('/login', [
            'email' => 'suspendido@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('account.suspended'));
        $this->assertGuest();
    }

    public function test_panel_bloqueado_para_usuario_suspendido(): void
    {
        $user = User::factory()->create([
            'onboarding_completed_at' => now(),
            'suspended_at' => now(),
            'suspended_reason' => 'subscription_ended',
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertRedirect(route('account.suspended'));
    }

    public function test_unsuspend_restaura_snapshot_de_visibilidad(): void
    {
        $user = User::factory()->create();
        $published = Company::create([
            'name' => 'Publicada',
            'slug' => 'publicada-' . $user->id,
            'menu_type' => 1,
            'enabled' => true,
            'reservation' => false,
            'user_id' => $user->id,
        ]);
        $draft = Company::create([
            'name' => 'Borrador',
            'slug' => 'borrador-' . $user->id,
            'menu_type' => 1,
            'enabled' => false,
            'reservation' => false,
            'user_id' => $user->id,
        ]);

        $suspension = app(UserSuspensionService::class);
        $suspension->suspend($user, 'admin');

        $this->assertFalse($published->fresh()->enabled);
        $this->assertFalse($draft->fresh()->enabled);

        $suspension->unsuspend($user);

        $this->assertTrue($published->fresh()->enabled);
        $this->assertFalse($draft->fresh()->enabled);
    }

    public function test_usuario_puede_eliminar_su_propia_cuenta(): void
    {
        $user = User::factory()->create([
            'email' => 'borrar@test.com',
            'password' => Hash::make('password123'),
            'onboarding_completed_at' => now(),
        ]);

        $response = $this->actingAs($user)->delete(route('admin.settings.account'), [
            'confirm_email' => 'borrar@test.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/');
        $response->assertSessionHas('flash', 'Tu cuenta ha sido eliminada.');
    }

    public function test_user_deletion_service_borra_usuario(): void
    {
        $user = User::factory()->create();
        Company::create([
            'name' => 'Negocio',
            'slug' => 'negocio-svc-' . $user->id,
            'menu_type' => 1,
            'enabled' => true,
            'reservation' => false,
            'user_id' => $user->id,
        ]);

        app(UserDeletionService::class)->delete($user, $user);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_no_se_puede_eliminar_superadmin(): void
    {
        $this->seed(\Database\Seeders\PlatformRolesSeeder::class);

        $admin = User::factory()->create(['email' => 'admin@test.com']);
        $admin->assignRole('super-admin');

        $this->expectException(\InvalidArgumentException::class);

        app(UserDeletionService::class)->delete($admin, $admin);
    }
}
