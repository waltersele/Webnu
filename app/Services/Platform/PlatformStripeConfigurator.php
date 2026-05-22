<?php

namespace App\Services\Platform;

use App\PlatformSetting;
use Illuminate\Support\Facades\Schema;

class PlatformStripeConfigurator
{
    public function apply(): void
    {
        if (! $this->tableReady()) {
            return;
        }

        $key = PlatformSetting::stripeKey();
        $secret = PlatformSetting::stripeSecret();
        $webhook = PlatformSetting::stripeWebhookSecret();

        if ($key) {
            config(['services.stripe.key' => $key]);
        }

        if ($secret) {
            config(['services.stripe.secret' => $secret]);
        }

        if ($webhook) {
            config(['services.stripe.webhook_secret' => $webhook]);
            putenv('STRIPE_WEBHOOK_SECRET=' . $webhook);
            $_ENV['STRIPE_WEBHOOK_SECRET'] = $webhook;
        }
    }

    protected function tableReady(): bool
    {
        try {
            return Schema::hasTable('platform_settings');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
