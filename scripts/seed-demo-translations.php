<?php

/**
 * Activa EN + RU en la carta demo y carga traducciones de ejemplo.
 * Uso: php scripts/seed-demo-translations.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Company;
use App\Product;
use App\ProductTranslation;
use App\Section;
use App\SectionTranslation;
use App\Services\Demo\DemoTranslationCatalog;

$company = Company::where('slug', 'demo')->first();

if (! $company) {
    echo "No existe la carta demo (slug=demo). Ejecuta: php scripts/seed-local-demo.php\n";
    exit(1);
}

$catalog = new DemoTranslationCatalog();
$locales = $catalog->locales();

$company->default_locale = 'es';
$company->enabled_locales = $locales;
$company->save();

$sectionTranslations = $catalog->sections();
$productTranslations = $catalog->products();

$sections = Section::where('company_id', $company->id)->get();
$sectionCount = 0;
$productCount = 0;

foreach ($sections as $section) {
    $map = $sectionTranslations[$section->name] ?? null;
    if (! $map) {
        continue;
    }

    foreach ($locales as $locale) {
        if (empty($map[$locale])) {
            continue;
        }
        SectionTranslation::updateOrCreate(
            ['section_id' => $section->id, 'locale' => $locale],
            ['name' => $map[$locale], 'source' => SectionTranslation::SOURCE_AI]
        );
        $sectionCount++;
    }
}

$products = Product::whereIn('section_id', $sections->pluck('id'))->get();

foreach ($products as $product) {
    $map = $productTranslations[$product->name] ?? null;
    if (! $map) {
        continue;
    }

    foreach ($locales as $locale) {
        if (empty($map[$locale]['name'])) {
            continue;
        }
        ProductTranslation::updateOrCreate(
            ['product_id' => $product->id, 'locale' => $locale],
            [
                'name' => $map[$locale]['name'],
                'description' => $map[$locale]['description'] ?? null,
                'source' => ProductTranslation::SOURCE_AI,
            ]
        );
        $productCount++;
    }
}

echo "Carta demo multilingüe lista.\n";
echo "  Idiomas: ES (base) + " . strtoupper(implode(' + ', $locales)) . "\n";
echo "  Secciones traducidas: {$sectionCount} registros\n";
echo "  Platos traducidos: {$productCount} registros\n";
echo "  Ver: /carta/demo?lang=en\n";
echo "  Ver: /carta/demo?lang=ru\n";
