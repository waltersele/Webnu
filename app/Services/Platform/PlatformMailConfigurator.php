<?php

namespace App\Services\Platform;

use App\PlatformSetting;
use Illuminate\Support\Facades\Schema;

class PlatformMailConfigurator
{
    /**
     * @param  array<string, mixed>  $overrides
     */
    public function apply(array $overrides = []): void
    {
        if (! $this->tableReady()) {
            return;
        }

        $mailer = $this->value('mail_mailer', 'platform.mail.mailer', $overrides);
        if ($mailer) {
            config(['mail.default' => $mailer]);
        }

        $host = $this->value('mail_host', 'platform.mail.host', $overrides);
        if ($host) {
            config([
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => (int) $this->value('mail_port', 'platform.mail.port', $overrides),
                'mail.mailers.smtp.username' => $this->value('mail_username', 'platform.mail.username', $overrides),
                'mail.mailers.smtp.encryption' => $this->nullableValue('mail_encryption', 'platform.mail.encryption', $overrides),
            ]);

            $password = $this->password($overrides);
            if ($password !== null && $password !== '') {
                config(['mail.mailers.smtp.password' => $password]);
            }
        }

        $fromAddress = $this->value('mail_from_address', 'platform.mail.from_address', $overrides);
        $fromName = $this->value('mail_from_name', 'platform.mail.from_name', $overrides);
        if ($fromAddress) {
            config([
                'mail.from.address' => $fromAddress,
                'mail.from.name' => $fromName ?: 'Webnu',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function value(string $key, string $configKey, array $overrides): ?string
    {
        if (array_key_exists($key, $overrides) && $overrides[$key] !== null && $overrides[$key] !== '') {
            return trim((string) $overrides[$key]);
        }

        $fromDb = PlatformSetting::getValue($key);
        if ($fromDb !== null && $fromDb !== '') {
            return trim($fromDb);
        }

        $fromConfig = config($configKey);

        return $fromConfig !== null && $fromConfig !== '' ? trim((string) $fromConfig) : null;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function nullableValue(string $key, string $configKey, array $overrides): ?string
    {
        if (array_key_exists($key, $overrides)) {
            $value = $overrides[$key];

            return $value === null || $value === '' ? null : trim((string) $value);
        }

        $fromDb = PlatformSetting::getValue($key);
        if ($fromDb !== null) {
            return $fromDb === '' ? null : trim($fromDb);
        }

        $fromConfig = config($configKey);

        return $fromConfig === null || $fromConfig === '' ? null : trim((string) $fromConfig);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function password(array $overrides): ?string
    {
        if (! empty($overrides['mail_password'])) {
            return trim((string) $overrides['mail_password']);
        }

        return PlatformSetting::mailPassword();
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
