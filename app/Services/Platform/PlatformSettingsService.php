<?php

namespace App\Services\Platform;

use App\PlatformSetting;

class PlatformSettingsService
{
    public function geminiApiKey(): ?string
    {
        return PlatformSetting::geminiApiKey();
    }

    public function geminiModel(): string
    {
        return PlatformSetting::geminiModel();
    }

    public function hasGeminiApiKey(): bool
    {
        return PlatformSetting::hasGeminiApiKey();
    }

    public function geminiApiKeyHint(): ?string
    {
        return PlatformSetting::geminiApiKeyHint();
    }

    public function updateGemini(?string $apiKey, ?string $model): void
    {
        if ($apiKey !== null && $apiKey !== '') {
            PlatformSetting::setValue('gemini_api_key', trim($apiKey));
        }

        if ($model !== null && $model !== '') {
            PlatformSetting::setValue('gemini_model', PlatformSetting::resolveGeminiModel(trim($model)));
        }
    }

    public function clearGeminiApiKey(): void
    {
        PlatformSetting::where('key', 'gemini_api_key')->delete();
    }

    public function mailSettingsForForm(): array
    {
        return [
            'mail_mailer' => PlatformSetting::mailMailer(),
            'mail_host' => PlatformSetting::mailHost(),
            'mail_port' => PlatformSetting::mailPort(),
            'mail_username' => PlatformSetting::mailUsername(),
            'mail_encryption' => PlatformSetting::mailEncryption(),
            'mail_from_address' => PlatformSetting::mailFromAddress(),
            'mail_from_name' => PlatformSetting::mailFromName(),
            'mail_password_configured' => PlatformSetting::hasMailPassword(),
            'mail_password_hint' => PlatformSetting::mailPasswordHint(),
        ];
    }

    public function contactSettingsForForm(): array
    {
        return [
            'contact_leads_email' => PlatformSetting::contactLeadsEmail(),
            'contact_suggestions_email' => PlatformSetting::contactSuggestionsEmail(),
            'contact_public_email' => PlatformSetting::contactPublicEmail(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateMail(array $data): void
    {
        $simpleFields = [
            'mail_mailer',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_encryption',
            'mail_from_address',
            'mail_from_name',
        ];

        foreach ($simpleFields as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];
            PlatformSetting::setValue($field, $value === null || $value === '' ? null : trim((string) $value));
        }

        if (! empty($data['mail_password'])) {
            PlatformSetting::setValue('mail_password', trim((string) $data['mail_password']));
        }
    }

    public function clearMailPassword(): void
    {
        PlatformSetting::where('key', 'mail_password')->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateContact(array $data): void
    {
        foreach (['contact_leads_email', 'contact_suggestions_email', 'contact_public_email'] as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            PlatformSetting::setValue($field, trim((string) $data[$field]));
        }
    }

    public function salesSettingsForForm(): array
    {
        return [
            'sales_handoff_plan_key' => PlatformSetting::salesHandoffPlanKey(),
            'sales_handoff_trial_days' => PlatformSetting::salesHandoffTrialDays(),
            'sales_demo_max_photo_products' => PlatformSetting::salesDemoMaxPhotoProducts(),
        ];
    }

    /**
     * @return array<int, string>
     */
    public function availablePlanKeys(): array
    {
        return array_keys(config('plans.tiers', []));
    }

    /**
     * Planes válidos para cierre comercial (sin plan gratuito).
     *
     * @return array<int, string>
     */
    public function handoffPlanKeys(): array
    {
        return array_values(array_filter($this->availablePlanKeys(), function ($key) {
            return $key !== 'free';
        }));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function integrationsSettingsForForm(): array
    {
        return [
            'stripe_key' => PlatformSetting::stripeKey() ?? '',
            'stripe_secret_configured' => PlatformSetting::hasStripeSecret(),
            'stripe_secret_hint' => PlatformSetting::stripeSecretHint(),
            'stripe_webhook_configured' => PlatformSetting::stripeWebhookSecret() !== null,
            'stripe_webhook_hint' => PlatformSetting::stripeWebhookHint(),
            'tvpik_api_url' => PlatformSetting::tvpikApiUrl() ?? '',
            'tvpik_web_url' => PlatformSetting::tvpikWebUrl(),
            'tvpik_app_key_configured' => PlatformSetting::tvpikAppKey() !== null,
            'tvpik_app_key_hint' => PlatformSetting::tvpikAppKeyHint(),
            'tvpik_stub_screens' => PlatformSetting::tvpikStubScreens(),
            'digital_signage_app_key_configured' => PlatformSetting::digitalSignageAppKey() !== null,
            'digital_signage_app_key_hint' => PlatformSetting::digitalSignageAppKeyHint(),
            'digital_signage_only_enabled' => PlatformSetting::digitalSignageOnlyEnabled(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateIntegrations(array $data): void
    {
        if (array_key_exists('stripe_key', $data)) {
            $key = trim((string) $data['stripe_key']);
            PlatformSetting::setValue('stripe_key', $key === '' ? null : $key);
        }

        if (! empty($data['stripe_secret'])) {
            PlatformSetting::setValue('stripe_secret', trim((string) $data['stripe_secret']));
        }

        if ($data['clear_stripe_secret'] ?? false) {
            PlatformSetting::where('key', 'stripe_secret')->delete();
        }

        if (! empty($data['stripe_webhook_secret'])) {
            PlatformSetting::setValue('stripe_webhook_secret', trim((string) $data['stripe_webhook_secret']));
        }

        if ($data['clear_stripe_webhook_secret'] ?? false) {
            PlatformSetting::where('key', 'stripe_webhook_secret')->delete();
        }

        if (array_key_exists('tvpik_api_url', $data)) {
            $url = trim((string) $data['tvpik_api_url']);
            PlatformSetting::setValue('tvpik_api_url', $url === '' ? null : rtrim($url, '/'));
        }

        if (array_key_exists('tvpik_web_url', $data)) {
            $url = trim((string) $data['tvpik_web_url']);
            PlatformSetting::setValue('tvpik_web_url', $url === '' ? null : rtrim($url, '/'));
        }

        if (! empty($data['tvpik_app_key'])) {
            PlatformSetting::setValue('tvpik_app_key', trim((string) $data['tvpik_app_key']));
        }

        if ($data['clear_tvpik_app_key'] ?? false) {
            PlatformSetting::where('key', 'tvpik_app_key')->delete();
        }

        PlatformSetting::setValue(
            'tvpik_stub_screens',
            ! empty($data['tvpik_stub_screens']) ? '1' : '0'
        );

        if (! empty($data['digital_signage_app_key'])) {
            PlatformSetting::setValue('digital_signage_app_key', trim((string) $data['digital_signage_app_key']));
        }

        if ($data['clear_digital_signage_app_key'] ?? false) {
            PlatformSetting::where('key', 'digital_signage_app_key')->delete();
        }

        PlatformSetting::setValue(
            'digital_signage_only_enabled',
            ! empty($data['digital_signage_only_enabled']) ? '1' : '0'
        );
    }

    public function updateSales(array $data): void
    {
        if (array_key_exists('sales_handoff_plan_key', $data)) {
            $key = trim((string) $data['sales_handoff_plan_key']);
            if (in_array($key, $this->availablePlanKeys(), true)) {
                PlatformSetting::setValue('sales_handoff_plan_key', $key);
            }
        }

        if (array_key_exists('sales_handoff_trial_days', $data)) {
            PlatformSetting::setValue('sales_handoff_trial_days', (string) max(1, (int) $data['sales_handoff_trial_days']));
        }

        if (array_key_exists('sales_demo_max_photo_products', $data)) {
            PlatformSetting::setValue('sales_demo_max_photo_products', (string) max(1, (int) $data['sales_demo_max_photo_products']));
        }
    }
}
