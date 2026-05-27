<?php

namespace Tests\Feature;

use App\Company;
use App\Services\MenuLocaleService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanLocaleLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_registro_usa_idioma_del_navegador_como_base(): void
    {
        $response = $this->withHeader('Accept-Language', 'fr-FR,fr;q=0.9')
            ->post('/register', [
                'name'                  => 'Test User',
                'email'                 => 'fr-user@example.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'business_name'         => 'Bistro Test',
            ]);

        $response->assertRedirect(route('admin.onboarding'));

        $company = User::where('email', 'fr-user@example.com')->firstOrFail()->companies()->first();
        $this->assertSame('fr', $company->default_locale);
    }

    public function test_onboarding_paso4_rechaza_mas_de_dos_extras_en_pro(): void
    {
        $user = $this->createTrialProUser();
        $company = $user->companies()->first();

        $response = $this->actingAs($user)->post(route('admin.onboarding.update'), [
            'step'           => 4,
            'default_locale' => 'es',
            'locales'        => ['en', 'fr', 'de'],
        ]);

        $response->assertSessionHasErrors('locales');
        $this->assertSame([], $company->fresh()->enabled_locales ?? []);
    }

    public function test_public_locales_recorta_a_tres_en_plan_pro(): void
    {
        $user = $this->createTrialProUser();
        $company = $user->companies()->first();
        $company->default_locale = 'es';
        $company->enabled_locales = ['en', 'fr', 'de'];
        $company->save();

        $locales = app(MenuLocaleService::class)->publicLocalesForCompany($company->fresh());

        $this->assertCount(3, $locales);
        $this->assertSame(['es', 'en', 'fr'], $locales);
    }

    public function test_detect_supported_locale_from_request(): void
    {
        $request = request()->create('/', 'GET', [], [], [], [
            'HTTP_ACCEPT_LANGUAGE' => 'de-DE,de;q=0.9,en;q=0.5',
        ]);

        $locale = app(MenuLocaleService::class)->detectSupportedLocaleFromRequest($request);

        $this->assertSame('de', $locale);
    }

    private function createTrialProUser(): User
    {
        $user = User::factory()->create([
            'plan'                    => 'free',
            'onboarding_step'         => 4,
            'onboarding_completed_at' => null,
            'trial_ends_at'           => now()->addDays(30),
            'trial_plan_key'          => 'pro',
            'slug'                    => 'trial-user-' . uniqid(),
        ]);

        Company::create([
            'name'           => 'Mi Restaurante',
            'slug'           => 'mi-restaurante-' . $user->id,
            'template'       => 'lumiere',
            'menu_type'      => 1,
            'enabled'        => false,
            'user_id'        => $user->id,
            'default_locale' => 'es',
        ]);

        return $user;
    }
}
