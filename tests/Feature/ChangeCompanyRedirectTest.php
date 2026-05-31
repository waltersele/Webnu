<?php

namespace Tests\Feature;

use App\Company;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangeCompanyRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_changecompany_redirects_to_sections_not_platform(): void
    {
        config(['platform.super_admin_emails' => ['super@test.com']]);

        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => Hash::make('password'),
            'onboarding_completed_at' => now(),
        ]);

        $first = Company::create([
            'name' => 'Carta A',
            'slug' => 'carta-a-' . $user->id,
            'template' => 'pasion',
            'menu_type' => 1,
            'enabled' => true,
            'user_id' => $user->id,
        ]);

        Company::create([
            'name' => 'Carta B',
            'slug' => 'carta-b-' . $user->id,
            'template' => 'pasion',
            'menu_type' => 1,
            'enabled' => true,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('admin.companies.changecompany'), [
            'company_selection' => $first->id,
        ]);

        $response->assertRedirect(route('admin.sections.index'));
        $response->assertRedirect('/admin/sections');
    }

    public function test_changecompany_accepts_absolute_redirect_after_url(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
            'onboarding_completed_at' => now(),
        ]);

        $company = Company::create([
            'name' => 'Mi carta',
            'slug' => 'mi-carta-' . $user->id,
            'template' => 'pasion',
            'menu_type' => 1,
            'enabled' => true,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('admin.companies.changecompany'), [
            'company_selection' => $company->id,
            'redirect_after' => route('admin.sections.index'),
        ]);

        $response->assertRedirect('/admin/sections');
    }
}
