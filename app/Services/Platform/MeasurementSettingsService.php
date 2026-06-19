<?php

namespace App\Services\Platform;

use App\PlatformSetting;
use Illuminate\Support\Facades\Schema;

final class MeasurementSettingsService
{
    public function isEnabled(): bool
    {
        if (! $this->platformSettingsAvailable()) {
            return (bool) config('measurement.enabled', false);
        }

        $stored = PlatformSetting::getValue('measurement_enabled');

        if ($stored !== null) {
            return $this->truthy($stored);
        }

        return (bool) config('measurement.enabled', false);
    }

    public function cookieBannerEnabled(): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        if (! $this->hasConsentableTools()) {
            return false;
        }

        $value = PlatformSetting::getValue('cookie_banner_enabled');

        if ($value === null) {
            return (bool) config('measurement.cookie_banner_enabled', true);
        }

        return $this->truthy($value);
    }

    public function googleSiteVerification(): ?string
    {
        $fromDb = $this->clean(PlatformSetting::getValue('google_site_verification'));

        return $fromDb ?: $this->clean(config('measurement.google_site_verification'));
    }

    public function gtagMeasurementId(): ?string
    {
        $fromDb = $this->clean(PlatformSetting::getValue('gtag_measurement_id'));

        return $fromDb ?: $this->clean(config('measurement.gtag_measurement_id'));
    }

    public function gtmContainerId(): ?string
    {
        $fromDb = $this->clean(PlatformSetting::getValue('gtm_container_id'));

        return $fromDb ?: $this->clean(config('measurement.gtm_container_id'));
    }

    public function clarityProjectId(): ?string
    {
        $fromDb = $this->clean(PlatformSetting::getValue('clarity_project_id'));

        return $fromDb ?: $this->clean(config('measurement.clarity_project_id'));
    }

    /** @return array<string, mixed> */
    public function publicConfig(?string $pendingEvent = null): array
    {
        if (! $this->isEnabled()) {
            return [
                'enabled' => false,
                'cookieBanner' => false,
                'tools' => [],
                'pendingEvent' => $pendingEvent,
            ];
        }

        $tools = [];

        if ($this->gtagMeasurementId()) {
            $tools['gtag'] = ['id' => $this->gtagMeasurementId()];
        }

        if ($this->gtmContainerId()) {
            $tools['gtm'] = ['id' => $this->gtmContainerId()];
        }

        if ($this->clarityProjectId()) {
            $tools['clarity'] = ['id' => $this->clarityProjectId()];
        }

        return [
            'enabled' => count($tools) > 0,
            'cookieBanner' => $this->cookieBannerEnabled(),
            'tools' => $tools,
            'pendingEvent' => $pendingEvent,
        ];
    }

    /** @return array<string, mixed> */
    public function settingsForForm(): array
    {
        return [
            'measurement_enabled' => $this->isEnabled(),
            'cookie_banner_enabled' => $this->cookieBannerEnabled(),
            'google_site_verification' => PlatformSetting::getValue('google_site_verification') ?? '',
            'gtag_measurement_id' => PlatformSetting::getValue('gtag_measurement_id') ?? '',
            'gtm_container_id' => PlatformSetting::getValue('gtm_container_id') ?? '',
            'clarity_project_id' => PlatformSetting::getValue('clarity_project_id') ?? '',
        ];
    }

    /** @param  array<string, mixed>  $data */
    public function update(array $data): void
    {
        PlatformSetting::setValue(
            'measurement_enabled',
            ! empty($data['measurement_enabled']) ? '1' : '0'
        );

        PlatformSetting::setValue(
            'cookie_banner_enabled',
            ! empty($data['cookie_banner_enabled']) ? '1' : '0'
        );

        foreach (['google_site_verification', 'gtag_measurement_id', 'gtm_container_id', 'clarity_project_id'] as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $value = trim((string) $data[$field]);
            PlatformSetting::setValue($field, $value === '' ? null : $value);
        }
    }

    public function hasConsentableTools(): bool
    {
        return $this->gtagMeasurementId() !== null
            || $this->gtmContainerId() !== null
            || $this->clarityProjectId() !== null;
    }

    private function platformSettingsAvailable(): bool
    {
        try {
            return Schema::hasTable('platform_settings');
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function truthy(?string $value): bool
    {
        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    private function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
