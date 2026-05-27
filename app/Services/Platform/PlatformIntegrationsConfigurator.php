<?php

namespace App\Services\Platform;

use App\PlatformSetting;
use Illuminate\Support\Facades\Schema;

class PlatformIntegrationsConfigurator
{
    public function apply(): void
    {
        if (! $this->tableReady()) {
            return;
        }

        $tvpikUrl = PlatformSetting::tvpikApiUrl();
        if ($tvpikUrl !== null && $tvpikUrl !== '') {
            config(['tvpik.api_url' => rtrim($tvpikUrl, '/')]);
        }

        $tvpikKey = PlatformSetting::tvpikAppKey();
        if ($tvpikKey) {
            config(['tvpik.app_key' => $tvpikKey]);
        }

        $tvpikWeb = PlatformSetting::tvpikWebUrl();
        if ($tvpikWeb) {
            config(['tvpik.web_app_url' => rtrim($tvpikWeb, '/')]);
        }

        config(['tvpik.stub_screens' => PlatformSetting::tvpikStubScreens()]);

        $signageKey = PlatformSetting::digitalSignageAppKey();
        if ($signageKey) {
            config(['digital_signage.app_key' => $signageKey]);
        }

        config(['digital_signage.only_enabled' => PlatformSetting::digitalSignageOnlyEnabled()]);

        $preAltaKey = PlatformSetting::preAltaIngestKey();
        if ($preAltaKey) {
            config(['pre_alta.ingest_key' => $preAltaKey]);
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
