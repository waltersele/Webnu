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

    public static function mailMailer(): string
    {
        return static::getValue('mail_mailer') ?? config('platform.mail.mailer', 'smtp');
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
        return static::getValue('mail_from_address') ?? config('platform.mail.from_address', 'info@webnu.es');
    }

    public static function mailFromName(): string
    {
        return static::getValue('mail_from_name') ?? config('platform.mail.from_name', 'Webnu');
    }

    public static function contactLeadsEmail(): string
    {
        return static::getValue('contact_leads_email') ?? config('platform.contact.leads_email', 'info@webnu.es');
    }

    public static function contactSuggestionsEmail(): string
    {
        return static::getValue('contact_suggestions_email') ?? config('platform.contact.suggestions_email', 'hola@webnu.es');
    }

    public static function contactPublicEmail(): string
    {
        return static::getValue('contact_public_email') ?? config('platform.contact.public_email', 'hola@webnu.es');
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
}
