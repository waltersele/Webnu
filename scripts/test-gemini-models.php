<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\PlatformSetting;
use GuzzleHttp\Client;

$apiKey = PlatformSetting::geminiApiKey();
if (! $apiKey) {
    fwrite(STDERR, "No API key in platform_settings\n");
    exit(1);
}

$client = new Client(App\Services\MenuScan\GeminiMenuScanProvider::httpClientOptions());
$base = rtrim(config('menu_scan.gemini.base_url'), '/');

echo "Listing models...\n";
$r = $client->get($base . '/models', ['headers' => ['x-goog-api-key' => $apiKey]]);
$data = json_decode((string) $r->getBody(), true);
$models = [];
foreach ($data['models'] ?? [] as $m) {
    $name = $m['name'] ?? '';
    $short = str_replace('models/', '', $name);
    $methods = $m['supportedGenerationMethods'] ?? [];
    if (in_array('generateContent', $methods, true)) {
        $models[] = $short;
        echo "  OK: {$short}\n";
    }
}

$candidates = array_merge(
    [PlatformSetting::geminiModel()],
    array_keys(config('menu_scan.recommended_models', [])),
    $models
);
$candidates = array_values(array_unique($candidates));

echo "\nTesting generateContent...\n";
foreach ($candidates as $model) {
    $url = $base . '/models/' . $model . ':generateContent';
    try {
        $resp = $client->post($url, [
            'headers' => ['x-goog-api-key' => $apiKey],
            'json' => ['contents' => [['parts' => [['text' => 'OK']]]]],
        ]);
        echo "  WORKS: {$model} (HTTP {$resp->getStatusCode()})\n";
    } catch (Exception $e) {
        $code = method_exists($e, 'getResponse') && $e->getResponse() ? $e->getResponse()->getStatusCode() : '?';
        echo "  FAIL:  {$model} (HTTP {$code})\n";
    }
}
