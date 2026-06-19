<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        \App\Company::class => \App\Policies\CompanyPolicy::class,
        \App\Section::class => \App\Policies\SectionPolicy::class,
        \App\Product::class => \App\Policies\ProductPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('platform.access', function ($user) {
            if ($user->isSuperAdmin()) {
                return true;
            }

            $impersonatorId = (int) session('impersonator_id', 0);
            if ($impersonatorId <= 0) {
                return false;
            }

            $impersonator = \App\User::find($impersonatorId);

            return $impersonator && $impersonator->isSuperAdmin();
        });
    }
}
