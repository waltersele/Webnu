<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Platform\PlatformMailConfigurator;
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
        if (method_exists(Cashier::class, 'useSubscriptionModel')) {
            Cashier::useSubscriptionModel(\App\Subscription::class);
        }

        $this->app->make(PlatformMailConfigurator::class)->apply();
    }
}
