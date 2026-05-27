<?php

namespace App\Services\Platform;

use App\PlatformSetting;
use Illuminate\Support\Facades\Schema;

class PlatformGoogleConfigurator
{
    public function apply(): void
    {
        if (! $this->tableReady()) {
            return;
        }

        $clientId = PlatformSetting::googleClientId();
        $clientSecret = PlatformSetting::googleClientSecret();

        if ($clientId) {
            config(['services.google.client_id' => $clientId]);
        }

        if ($clientSecret) {
            config(['services.google.client_secret' => $clientSecret]);
        }

        config(['services.google.redirect' => PlatformSetting::googleRedirectUri()]);
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
