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
}
