<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PlatformSetting extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    protected static $encryptedKeys = [
        'gemini_api_key',
        'mail_password',
        'stripe_secret',
        'stripe_webhook_secret',
        'google_client_secret',
        'pre_alta_ingest_key',
        'tvpik_app_key',
        'digital_signage_app_key',
    ];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        $row = static::find($key);
        if (! $row || $row->value === null || $row->value === '') {
            return $default;
        }

        if (in_array($key, static::$encryptedKeys, true)) {
            try {
                return Crypt::decryptString($row->value);
            } catch (\Throwable $e) {
                return $default;
            }
        }

        return $row->value;
    }

    public static function setValue(string $key, ?string $value): void
    {
        if ($value === null || $value === '') {
            static::where('key', $key)->delete();

            return;
        }

        $stored = $value;
        if (in_array($key, static::$encryptedKeys, true)) {
            $stored = Crypt::encryptString($value);
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $stored]
        );
    }

    public static function geminiApiKey(): ?string
    {
        $fromDb = static::getValue('gemini_api_key');
        if ($fromDb) {
            return trim($fromDb);
        }

        $fromEnv = env('GEMINI_API_KEY');

        return $fromEnv !== null && $fromEnv !== '' ? trim($fromEnv) : null;
    }

    public static function geminiModel(): string
    {
        $default = config('menu_scan.default_gemini_model', 'gemini-2.5-flash-lite');
        $model = static::getValue('gemini_model') ?? env('GEMINI_MODEL', $default);
        $model = trim($model) !== '' ? trim($model) : $default;

        return static::resolveGeminiModel($model);
    }

    public static function resolveGeminiModel(string $model): string
    {
        $aliases = config('menu_scan.gemini_model_aliases', []);

        return $aliases[$model] ?? $model;
    }

    /**
     * Modelos a probar en orden (preferido + respaldos verificados en generateContent).
     *
     * @return array<int, string>
     */
    public static function geminiModelsToTry(?string $preferred = null): array
    {
        $preferred = static::resolveGeminiModel(trim($preferred ?? static::geminiModel()));
        $fallbacks = config('menu_scan.gemini_model_fallbacks', []);

        $models = array_merge([$preferred], $fallbacks);
        $resolved = [];
        foreach ($models as $model) {
            $model = static::resolveGeminiModel($model);
            if ($model !== '' && ! in_array($model, $resolved, true)) {
                $resolved[] = $model;
            }
        }

        return $resolved;
    }

    public static function hasGeminiApiKey(): bool
    {
        return static::geminiApiKey() !== null && static::geminiApiKey() !== '';
    }

    public static function geminiApiKeyHint(): ?string
    {
        $key = static::geminiApiKey();
        if (! $key || strlen($key) < 8) {
            return null;
        }

        return '••••' . substr($key, -4);
    }

    protected static function stringSetting(string $key, string $configPath, string $fallback): string
    {
        $value = static::getValue($key);
        if ($value !== null && $value !== '') {
            return $value;
        }

        $fromConfig = config($configPath);
        if ($fromConfig !== null && $fromConfig !== '') {
            return (string) $fromConfig;
        }

        return $fallback;
    }

    public static function mailMailer(): string
    {
        return static::stringSetting('mail_mailer', 'platform.mail.mailer', 'smtp');
    }

    public static function mailHost(): ?string
    {
        return static::getValue('mail_host') ?? config('platform.mail.host');
    }

    public static function mailPort(): int
    {
        $port = static::getValue('mail_port') ?? config('platform.mail.port', 587);

        return (int) $port;
    }

    public static function mailUsername(): ?string
    {
        return static::getValue('mail_username') ?? config('platform.mail.username');
    }

    public static function mailPassword(): ?string
    {
        $fromDb = static::getValue('mail_password');
        if ($fromDb !== null && $fromDb !== '') {
            return $fromDb;
        }

        $fromEnv = env('MAIL_PASSWORD');

        return $fromEnv !== null && $fromEnv !== '' ? $fromEnv : null;
    }

    public static function mailEncryption(): ?string
    {
        $value = static::getValue('mail_encryption');
        if ($value !== null) {
            return $value === '' ? null : $value;
        }

        $fromConfig = config('platform.mail.encryption');

        return $fromConfig === null || $fromConfig === '' ? null : $fromConfig;
    }

    public static function mailFromAddress(): string
    {
        return static::stringSetting('mail_from_address', 'platform.mail.from_address', 'info@webnu.es');
    }

    public static function mailFromName(): string
    {
        return static::stringSetting('mail_from_name', 'platform.mail.from_name', 'Webnu');
    }

    public static function contactLeadsEmail(): string
    {
        return static::stringSetting('contact_leads_email', 'platform.contact.leads_email', 'hello@webnu.es');
    }

    public static function contactSuggestionsEmail(): string
    {
        return static::stringSetting('contact_suggestions_email', 'platform.contact.suggestions_email', 'hello@webnu.es');
    }

    public static function contactPublicEmail(): string
    {
        return static::stringSetting('contact_public_email', 'platform.contact.public_email', 'hello@webnu.es');
    }

    public static function mailPasswordHint(): ?string
    {
        $password = static::mailPassword();
        if (! $password || strlen($password) < 4) {
            return null;
        }

        return '••••' . substr($password, -2);
    }

    public static function hasMailPassword(): bool
    {
        return static::mailPassword() !== null && static::mailPassword() !== '';
    }

    /** URL pública de un asset de marca (clave en config platform.brand). */
    public static function brandUrl(string $key = 'logo'): string
    {
        $custom = static::getValue('brand_' . $key . '_path');
        if (is_string($custom) && $custom !== '') {
            return asset($custom);
        }

        $paths = config('platform.brand', []);
        $path = $paths[$key] ?? $paths['logo'] ?? 'adminlte/img/logo-color.png';

        return asset($path);
    }

    /** Ruta absoluta en disco para PDF/QR (misma clave que brandUrl). */
    public static function brandPath(string $key = 'logo'): string
    {
        $custom = static::getValue('brand_' . $key . '_path');
        if (is_string($custom) && $custom !== '') {
            $clean = preg_replace('/\?.*$/', '', $custom);
            if (is_string($clean) && $clean !== '') {
                return public_path($clean);
            }
        }

        $paths = config('platform.brand', []);
        $path = $paths[$key] ?? $paths['logo'] ?? 'adminlte/img/logo-color.png';

        return public_path($path);
    }

    public static function salesHandoffPlanKey(): string
    {
        $key = static::getValue('sales_handoff_plan_key');

        return $key !== null && $key !== '' ? $key : (string) config('plans.trial_tier', 'pro');
    }

    public static function salesHandoffTrialDays(): int
    {
        $days = static::getValue('sales_handoff_trial_days');

        return $days !== null && $days !== '' ? max(1, (int) $days) : (int) config('plans.trial_days', 30);
    }

    public static function salesDemoMaxPhotoProducts(): int
    {
        $max = static::getValue('sales_demo_max_photo_products');

        return $max !== null && $max !== '' ? max(1, (int) $max) : 2;
    }

    public static function secretHint(?string $value): ?string
    {
        if (! $value || strlen($value) < 8) {
            return null;
        }

        return '••••' . substr($value, -4);
    }

    protected static function secretFromDbOrEnv(string $dbKey, string $envKey): ?string
    {
        $fromDb = static::getValue($dbKey);
        if ($fromDb !== null && trim($fromDb) !== '') {
            return trim($fromDb);
        }

        $fromEnv = env($envKey);

        return $fromEnv !== null && $fromEnv !== '' ? trim($fromEnv) : null;
    }

    public static function stripeKey(): ?string
    {
        $fromDb = static::getValue('stripe_key');
        if ($fromDb !== null && trim($fromDb) !== '') {
            return trim($fromDb);
        }

        $fromEnv = env('STRIPE_KEY');

        return $fromEnv !== null && $fromEnv !== '' ? trim($fromEnv) : null;
    }

    public static function stripeSecret(): ?string
    {
        return static::secretFromDbOrEnv('stripe_secret', 'STRIPE_SECRET');
    }

    public static function stripeWebhookSecret(): ?string
    {
        return static::secretFromDbOrEnv('stripe_webhook_secret', 'STRIPE_WEBHOOK_SECRET');
    }

    public static function hasStripeSecret(): bool
    {
        $secret = static::stripeSecret();

        return $secret !== null && $secret !== '';
    }

    public static function stripeSecretHint(): ?string
    {
        return static::secretHint(static::stripeSecret());
    }

    public static function stripeWebhookHint(): ?string
    {
        return static::secretHint(static::stripeWebhookSecret());
    }

    public static function tvpikApiUrl(): ?string
    {
        $fromDb = static::getValue('tvpik_api_url');
        if ($fromDb !== null && trim($fromDb) !== '') {
            return rtrim(trim($fromDb), '/');
        }

        $fromEnv = env('TVPIK_API_URL');

        return $fromEnv !== null && $fromEnv !== '' ? rtrim(trim($fromEnv), '/') : null;
    }

    public static function tvpikAppKey(): ?string
    {
        $fromDb = static::getValue('tvpik_app_key');
        if ($fromDb !== null && trim($fromDb) !== '') {
            return trim($fromDb);
        }

        $fromEnv = env('TVPIK_APP_KEY') ?: env('DIGITAL_SIGNAGE_APP_KEY');

        return $fromEnv !== null && $fromEnv !== '' ? trim($fromEnv) : null;
    }

    public static function tvpikWebUrl(): string
    {
        $fromDb = static::getValue('tvpik_web_url');
        if ($fromDb !== null && trim($fromDb) !== '') {
            return rtrim(trim($fromDb), '/');
        }

        return rtrim((string) env('TVPIK_WEB_URL', 'https://tvpik.es'), '/');
    }

    public static function tvpikStubScreens(): bool
    {
        $fromDb = static::getValue('tvpik_stub_screens');
        if ($fromDb !== null && $fromDb !== '') {
            return filter_var($fromDb, FILTER_VALIDATE_BOOLEAN);
        }

        return filter_var(env('TVPIK_STUB_SCREENS', false), FILTER_VALIDATE_BOOLEAN);
    }

    public static function tvpikAppKeyHint(): ?string
    {
        return static::secretHint(static::tvpikAppKey());
    }

    public static function digitalSignageAppKey(): ?string
    {
        $fromDb = static::getValue('digital_signage_app_key');
        if ($fromDb !== null && trim($fromDb) !== '') {
            return trim($fromDb);
        }

        $fromEnv = env('DIGITAL_SIGNAGE_APP_KEY');

        return $fromEnv !== null && $fromEnv !== '' ? trim($fromEnv) : null;
    }

    public static function digitalSignageOnlyEnabled(): bool
    {
        $fromDb = static::getValue('digital_signage_only_enabled');
        if ($fromDb !== null && $fromDb !== '') {
            return filter_var($fromDb, FILTER_VALIDATE_BOOLEAN);
        }

        return filter_var(env('DIGITAL_SIGNAGE_ONLY_ENABLED', true), FILTER_VALIDATE_BOOLEAN);
    }

    public static function digitalSignageAppKeyHint(): ?string
    {
        return static::secretHint(static::digitalSignageAppKey());
    }

    public static function googleClientId(): ?string
    {
        $fromDb = static::getValue('google_client_id');
        if ($fromDb !== null && trim($fromDb) !== '') {
            return trim($fromDb);
        }

        $fromEnv = env('GOOGLE_CLIENT_ID');

        return $fromEnv !== null && $fromEnv !== '' ? trim($fromEnv) : null;
    }

    public static function googleClientSecret(): ?string
    {
        return static::secretFromDbOrEnv('google_client_secret', 'GOOGLE_CLIENT_SECRET');
    }

    public static function googleRedirectUri(): string
    {
        $fromDb = static::getValue('google_redirect_uri');
        if ($fromDb !== null && trim($fromDb) !== '') {
            return rtrim(trim($fromDb), '/');
        }

        $fromEnv = env('GOOGLE_REDIRECT_URI');
        if ($fromEnv !== null && trim($fromEnv) !== '') {
            return rtrim(trim($fromEnv), '/');
        }

        return rtrim((string) config('app.url'), '/') . '/auth/google/callback';
    }

    public static function hasGoogleOAuth(): bool
    {
        $id = static::googleClientId();
        $secret = static::googleClientSecret();

        return $id !== null && $id !== '' && $secret !== null && $secret !== '';
    }

    public static function googleClientSecretHint(): ?string
    {
        return static::secretHint(static::googleClientSecret());
    }

    public static function preAltaIngestKey(): ?string
    {
        return static::secretFromDbOrEnv('pre_alta_ingest_key', 'PRE_ALTA_INGEST_KEY');
    }

    public static function hasPreAltaIngestKey(): bool
    {
        $key = static::preAltaIngestKey();

        return $key !== null && $key !== '';
    }

    public static function preAltaIngestKeyHint(): ?string
    {
        return static::secretHint(static::preAltaIngestKey());
    }
}
