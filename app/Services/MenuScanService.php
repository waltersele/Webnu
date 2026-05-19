<?php

namespace App\Services;

use App\Contracts\MenuScanProvider;
use App\Services\MenuScan\GeminiMenuScanProvider;
use App\Services\MenuScan\MenuScanResult;
use App\Services\MenuScan\TesseractMenuScanProvider;

class MenuScanService
{
    public function scan(array $absoluteFilePaths): MenuScanResult
    {
        $primary = $this->resolveProvider(config('menu_scan.provider', 'gemini'));
        $result = $primary->scan($absoluteFilePaths);

        if ($result->isSuccess()) {
            return $result;
        }

        $fallbackName = config('menu_scan.fallback', 'tesseract');
        if ($fallbackName === 'none' || $fallbackName === null) {
            return $result;
        }

        if ($primary->name() === $fallbackName) {
            return $result;
        }

        $fallback = $this->resolveProvider($fallbackName);
        if ($fallback === null) {
            return $result;
        }

        $fallbackResult = $fallback->scan($absoluteFilePaths);
        if ($fallbackResult->isSuccess()) {
            $fallbackResult->fallbackUsed = true;
            if ($result->errorMessage) {
                if ($result->isQuotaExceeded()) {
                    $fallbackResult->warnings[] = 'Gemini sin cuota disponible; se usó OCR local.';
                } elseif ($result->isTransientFailure()) {
                    $fallbackResult->warnings[] = 'Gemini saturado o con error temporal; se usó OCR local.';
                } else {
                    $fallbackResult->warnings[] = 'Gemini no disponible; se usó OCR local.';
                }
            }

            return $fallbackResult;
        }

        if ($result->isQuotaExceeded()) {
            $extra = $fallback instanceof TesseractMenuScanProvider && ! $fallback->isAvailable()
                ? ' Instala Tesseract en el servidor para el modo OCR de respaldo (ver docs/MENU-SCAN.md).'
                : '';

            return MenuScanResult::failed(
                $result->errorMessage . $extra,
                $result->provider,
                'quota_exceeded'
            );
        }

        return $result;
    }

    protected function resolveProvider(string $name): ?MenuScanProvider
    {
        switch ($name) {
            case 'gemini':
                return new GeminiMenuScanProvider();
            case 'tesseract':
                return new TesseractMenuScanProvider();
            default:
                return null;
        }
    }
}
