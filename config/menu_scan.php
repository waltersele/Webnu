<?php

return [
    'provider' => env('MENU_SCAN_PROVIDER', 'gemini'),
    'fallback' => env('MENU_SCAN_FALLBACK', 'tesseract'),

    'default_gemini_model' => env('GEMINI_MODEL', 'gemini-2.5-flash-lite'),

    'gemini_model_aliases' => [
        'gemini-1.5-flash' => 'gemini-2.5-flash-lite',
        'gemini-1.5-flash-latest' => 'gemini-2.5-flash-lite',
        'gemini-1.5-flash-8b' => 'gemini-2.5-flash-lite',
        'gemini-1.5-pro' => 'gemini-2.5-flash-lite',
        'gemini-pro' => 'gemini-2.5-flash-lite',
        'gemini-2.0-flash' => 'gemini-2.5-flash-lite',
        'gemini-2.0-flash-001' => 'gemini-2.5-flash-lite',
        'gemini-2.0-flash-lite' => 'gemini-2.5-flash-lite',
        'gemini-2.0-flash-lite-001' => 'gemini-2.5-flash-lite',
        'gemini-2.5-flash' => 'gemini-2.5-flash-lite',
    ],

    'gemini_model_fallbacks' => [
        'gemini-flash-lite-latest',
        'gemini-pro-latest',
        'gemini-2.5-pro',
        'gemini-3.1-flash-lite',
    ],

    'recommended_models' => [
        'gemini-2.5-flash-lite' => 'Gemini 2.5 Flash Lite (recomendado)',
        'gemini-flash-lite-latest' => 'Gemini Flash Lite (alias estable)',
        'gemini-2.5-pro' => 'Gemini 2.5 Pro (más preciso, más lento)',
        'gemini-3.1-flash-lite' => 'Gemini 3.1 Flash Lite (si está en tu cuenta)',
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash-lite'),
        'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
        'timeout' => (int) env('MENU_SCAN_TIMEOUT', 90),
        'max_retries' => (int) env('MENU_SCAN_GEMINI_RETRIES', 3),
        'retry_delay_ms' => (int) env('MENU_SCAN_GEMINI_RETRY_DELAY_MS', 1000),
        'ca_bundle' => env('MENU_SCAN_CA_BUNDLE', resource_path('certs/cacert.pem')),
        'verify_ssl' => env('MENU_SCAN_VERIFY_SSL', true),
    ],

    'tesseract' => [
        'binary' => env('TESSERACT_BINARY', 'tesseract'),
        'lang' => env('TESSERACT_LANG', 'spa'),
    ],

    'limits' => [
        'max_files' => 10,
        'max_mb' => 8,
        'pages_pdf' => 20,
        'scans_per_hour' => (int) env('MENU_SCAN_SCANS_PER_HOUR', 5),
    ],

    'storage_disk' => 'local',
    'storage_path' => 'menu-scans',
];
