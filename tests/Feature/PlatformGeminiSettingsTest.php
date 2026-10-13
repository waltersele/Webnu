<?php

namespace Tests\Feature;

use App\Services\MenuScan\GeminiMenuScanProvider;
use App\Services\Platform\PlatformSettingsService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class PlatformGeminiSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        config(['platform.super_admin_emails' => ['super@test.com']]);
        Gate::define('platform.access', fn ($u) => $u->email === 'super@test.com');

        return User::create([
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => Hash::make('password'),
            'onboarding_completed_at' => now(),
        ]);
    }

    public function test_settings_muestra_aviso_sin_api_key_guardada(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->get(route('admin.platform.settings'))
            ->assertOk()
            ->assertSee('Sin API key', false)
            ->assertSee('guardada', false);
    }

    public function test_probar_conexion_ok_guarda_api_key_del_formulario(): void
    {
        $admin = $this->superAdmin();
        $settings = app(PlatformSettingsService::class);
        $this->assertFalse($settings->hasGeminiApiKey());

        $mock = Mockery::mock('alias:' . GeminiMenuScanProvider::class);
        $mock->shouldReceive('testConnection')
            ->once()
            ->andReturn([
                'ok' => true,
                'message' => 'Conexión correcta con el modelo «gemini-2.5-flash-lite».',
            ]);

        $response = $this->actingAs($admin)->post(route('admin.platform.settings.test-gemini'), [
            'gemini_api_key' => 'AIzaSyDummyKeyForTestingOnly1234567890',
            'gemini_model' => 'gemini-2.5-flash-lite',
        ]);

        $response->assertRedirect(route('admin.platform.settings'));
        $response->assertSessionHas('flash');
        $this->assertStringContainsString('guardado', session('flash'));
        $this->assertTrue(app(PlatformSettingsService::class)->hasGeminiApiKey());

        $this->actingAs($admin)
            ->get(route('admin.platform.settings'))
            ->assertOk()
            ->assertSee('API configurada', false)
            ->assertDontSee('Sin API key', false);
    }
}
