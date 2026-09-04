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
        if (! $this->hasConsentableTools() && ! $this->plausibleConfigured()) {
            return false;
        }

        if (! $this->isEnabled() && ! $this->plausibleConfigured()) {
            return false;
        }

        $value = PlatformSetting::getValue('cookie_banner_enabled');

        if ($value === null) {
            return (bool) config('measurement.cookie_banner_enabled', true);
        }

        return $this->truthy($value);
    }

    public function loadGoogleBeforeConsent(): bool
    {
        $fromDb = PlatformSetting::getValue('load_google_before_consent');

        if ($fromDb !== null) {
            return $this->truthy($fromDb);
        }

        return (bool) config('measurement.load_google_before_consent', true);
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

    public function metaPixelId(): ?string
    {
        $fromDb = $this->clean(PlatformSetting::getValue('meta_pixel_id'));

        return $fromDb ?: $this->clean(config('measurement.meta_pixel_id'));
    }

    public function linkedinPartnerId(): ?string
    {
        $fromDb = $this->clean(PlatformSetting::getValue('linkedin_partner_id'));

        return $fromDb ?: $this->clean(config('measurement.linkedin_partner_id'));
    }

    public function plausibleDomain(): ?string
    {
        $fromDb = $this->clean(PlatformSetting::getValue('plausible_domain'));

        return $fromDb ?: $this->clean(config('measurement.plausible_domain'));
    }

    public function plausibleUpstreamUrl(): ?string
    {
        $fromDb = $this->clean(PlatformSetting::getValue('plausible_upstream_url'));
        if ($fromDb !== null) {
            return $fromDb;
        }

        $fromConfig = $this->clean(config('measurement.plausible_upstream_url'));
        if ($fromConfig !== null) {
            return $fromConfig;
        }

        // Si hay dominio, no dejar Capa 1 muerta en silencio: Plausible cloud por defecto.
        if ($this->plausibleDomain() !== null) {
            return 'https://plausible.io';
        }

        return null;
    }

    public function plausibleConfigured(): bool
    {
        return $this->plausibleUpstreamUrl() !== null
            && $this->plausibleDomain() !== null;
    }

    /** @return array<string, mixed> */
    public function publicConfig(?string $pendingEvent = null): array
    {
        $plausibleOn = $this->plausibleConfigured();
        $platformOn = $this->isEnabled();

        $gtmId = $platformOn ? $this->gtmContainerId() : null;
        $gtagId = $platformOn ? $this->gtagMeasurementId() : null;

        $consented = [
            'gtmId' => $gtmId,
            'gtagId' => $gtmId ? null : $gtagId,
            'clarityId' => $platformOn ? $this->clarityProjectId() : null,
            'metaPixelId' => $platformOn ? $this->metaPixelId() : null,
            'linkedinPartnerId' => $platformOn ? $this->linkedinPartnerId() : null,
        ];

        $hasGoogleOrMarketing = $this->anyFilled($consented);
        $enabled = $platformOn || $plausibleOn;

        $exempt = [
            'plausibleDomain' => null,
            'plausibleScriptUrl' => null,
            'plausibleApiUrl' => null,
        ];

        if ($plausibleOn) {
            $exempt = [
                'plausibleDomain' => $this->plausibleDomain(),
                'plausibleScriptUrl' => (string) config('measurement.plausible_script_url', '/stats/js/script.js'),
                'plausibleApiUrl' => (string) config('measurement.plausible_api_url', '/stats/api/event'),
            ];
        }

        $showBanner = false;
        if ($enabled && ($hasGoogleOrMarketing || $plausibleOn)) {
            $showBanner = $this->cookieBannerEnabled() && $hasGoogleOrMarketing;
        }

        return [
            'enabled' => $enabled,
            'brand' => (string) config('measurement.brand', 'webnu'),
            'cookieBanner' => $showBanner,
            'loadGoogleBeforeConsent' => $this->loadGoogleBeforeConsent(),
            'exempt' => $exempt,
            'consented' => $consented,
            'pendingEvent' => $pendingEvent,
            'privacyUrl' => route('legal.privacy'),
        ];
    }

    /** @return array<string, mixed> */
    public function settingsForForm(): array
    {
        return [
            'measurement_enabled' => $this->isEnabled(),
            'cookie_banner_enabled' => $this->cookieBannerEnabled(),
            'load_google_before_consent' => $this->loadGoogleBeforeConsent(),
            'google_site_verification' => PlatformSetting::getValue('google_site_verification') ?? '',
            'gtag_measurement_id' => PlatformSetting::getValue('gtag_measurement_id') ?? '',
            'gtm_container_id' => PlatformSetting::getValue('gtm_container_id') ?? '',
            'clarity_project_id' => PlatformSetting::getValue('clarity_project_id') ?? '',
            'meta_pixel_id' => PlatformSetting::getValue('meta_pixel_id') ?? '',
            'linkedin_partner_id' => PlatformSetting::getValue('linkedin_partner_id') ?? '',
            'plausible_domain' => $this->plausibleDomain() ?? '',
            'plausible_upstream_url' => $this->plausibleUpstreamUrl() ?? '',
            'plausible_configured' => $this->plausibleConfigured(),
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

        PlatformSetting::setValue(
            'load_google_before_consent',
            ! empty($data['load_google_before_consent']) ? '1' : '0'
        );

        foreach ([
            'google_site_verification',
            'gtag_measurement_id',
            'gtm_container_id',
            'clarity_project_id',
            'meta_pixel_id',
            'linkedin_partner_id',
            'plausible_domain',
            'plausible_upstream_url',
        ] as $field) {
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
            || $this->clarityProjectId() !== null
            || $this->metaPixelId() !== null
            || $this->linkedinPartnerId() !== null;
    }

    /** @param  array<string, mixed>  $values */
    private function anyFilled(array $values): bool
    {
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                return true;
            }
        }

        return false;
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
