<?php

namespace App\Providers;

use App\Listeners\SendWelcomeEmail;
use App\Listeners\StripeWebhookSubscriptionListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Laravel\Cashier\Events\WebhookHandled;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendWelcomeEmail::class,
        ],
        WebhookHandled::class => [
            StripeWebhookSubscriptionListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
