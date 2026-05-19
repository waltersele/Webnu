<?php

/**
 * Ajusta el modelo Gemini guardado a uno que responde en generateContent.
 * Uso: php scripts/fix-gemini-model.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\PlatformSetting;

$target = config('menu_scan.default_gemini_model', 'gemini-2.5-flash-lite');
$current = PlatformSetting::getValue('gemini_model');
$resolved = $current ? PlatformSetting::resolveGeminiModel($current) : null;

$broken = [
    'gemini-1.5-flash',
    'gemini-2.0-flash',
    'gemini-2.0-flash-001',
    'gemini-2.0-flash-lite',
    'gemini-2.0-flash-lite-001',
    'gemini-2.5-flash',
];

if (! $current || in_array($current, $broken, true) || in_array($resolved, $broken, true)) {
    PlatformSetting::setValue('gemini_model', $target);
    echo "Modelo Gemini actualizado a {$target}\n";
} else {
    echo "Modelo Gemini sin cambios: {$resolved}\n";
}
