<?php

namespace Tests\Feature;

use App\Company;
use App\Services\UserPlanService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExpireTrialsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_expire_trials_pasa_a_free_sin_suspender_y_mantiene_carta(): void
    {
        $user = User::factory()->create([
            'email' => 'trial-end@test.com',
            'password' => Hash::make('password123'),
            'plan' => 'free',
            'trial_plan_key' => 'pro',
            'trial_ends_at' => now()->subDay(),
            'onboarding_completed_at' => now(),
            'onboarding_step' => 6,
        ]);

        $company = Company::create([
            'name' => 'Bar Trial',
            'slug' => 'bar-trial-' . $user->id,
            'public_url_format' => 'simple',
            'menu_type' => 1,
            'template' => 'lumiere',
            'enabled' => true,
            'reservation' => false,
            'user_id' => $user->id,
        ]);

        $this->artisan('webnu:expire-trials')
            ->assertExitCode(0);

        $user->refresh();

        $this->assertSame('free', $user->plan);
        $this->assertNull($user->trial_plan_key);
        $this->assertNull($user->suspended_at);
        $this->assertNull($user->suspended_reason);
        $this->assertTrue($company->fresh()->enabled);
        $this->assertSame('free', app(UserPlanService::class)->planKey($user));

        $this->post('/login', [
            'email' => 'trial-end@test.com',
            'password' => 'password123',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($user);

        $this->get(route('admin.dashboard'))->assertOk();
        $this->get('/' . $company->slug)->assertOk();
    }

    public function test_expire_trials_dry_run_no_modifica(): void
    {
        $user = User::factory()->create([
            'plan' => 'free',
            'trial_plan_key' => 'pro',
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->artisan('webnu:expire-trials', ['--dry-run' => true])
            ->assertExitCode(0);

        $user->refresh();
        $this->assertSame('pro', $user->trial_plan_key);
    }
}
