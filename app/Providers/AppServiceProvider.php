<?php

namespace App\Providers;

use App\Company;
use App\Observers\CompanyObserver;
use App\Observers\ProductObserver;
use App\Observers\SectionObserver;
use App\Product;
use App\Section;
use App\Services\Platform\PlatformGoogleConfigurator;
use App\Services\Platform\PlatformIntegrationsConfigurator;
use App\Services\Platform\PlatformMailConfigurator;
use App\Services\Platform\PlatformStripeConfigurator;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //Cashier::ignoreMigrations();
        //Cashier::useCurrency('eur', '€');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // El modelo de usuario es App\User (no App\Models\User, el valor por defecto de Cashier).
        // Sin esto, Cashier::findBillable() (webhooks de Stripe) y otras utilidades fallan.
        if (method_exists(Cashier::class, 'useCustomerModel')) {
            Cashier::useCustomerModel(\App\User::class);
        }

        if (method_exists(Cashier::class, 'useSubscriptionModel')) {
            Cashier::useSubscriptionModel(\App\Subscription::class);
        }

        $this->app->make(PlatformMailConfigurator::class)->apply();
        $this->app->make(PlatformStripeConfigurator::class)->apply();
        $this->app->make(PlatformGoogleConfigurator::class)->apply();
        $this->app->make(PlatformIntegrationsConfigurator::class)->apply();

        Company::observe(CompanyObserver::class);
        Section::observe(SectionObserver::class);
        Product::observe(ProductObserver::class);
    }
}
