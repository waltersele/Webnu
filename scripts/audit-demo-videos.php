<?php

/**
 * Lista vídeos asignados en cartas demo (BD local).
 * Uso: php scripts/audit-demo-videos.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Company;
use App\Product;

$videos = config('demo_media.videos', []);

echo "Archivos en config/demo_media.php:\n";
foreach ($videos as $key => $meta) {
    $path = public_path('img/demo/' . $meta['file']);
    $exists = is_file($path) ? 'OK' : 'FALTA';
    echo sprintf("  %-14s %-24s %s\n", $key, $meta['file'], $exists);
}

echo "\nProductos con reel en demos:\n";
Company::where('slug', 'like', 'demo%')->orderBy('slug')->each(function (Company $company) {
    $products = Product::whereIn('section_id', $company->sections()->pluck('id'))
        ->whereNotNull('video')
        ->orderBy('name')
        ->get(['name', 'video']);

    if ($products->isEmpty()) {
        return;
    }

    echo "\n[{$company->slug}] {$company->name}\n";
    foreach ($products as $p) {
        echo "  · {$p->name} → {$p->video}\n";
    }
});
