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

$company = Company::where('slug', 'demo')->first();

if (! $company) {
    echo "No existe la carta demo (slug=demo). Ejecuta: php scripts/seed-local-demo.php\n";
    exit(1);
}

$company->default_locale = 'es';
$company->enabled_locales = ['en', 'ru'];
$company->save();

$sectionTranslations = [
    'Entrantes' => ['en' => 'Starters', 'ru' => 'Закуски'],
    'Principales' => ['en' => 'Main courses', 'ru' => 'Основные блюда'],
    'Postres' => ['en' => 'Desserts', 'ru' => 'Десерты'],
];

$productTranslations = [
    'Gazpacho andaluz' => [
        'en' => ['name' => 'Andalusian gazpacho', 'description' => 'Pear tomato, cucumber, pepper and estate extra virgin olive oil.'],
        'ru' => ['name' => 'Гаспачо по-андалузски', 'description' => 'Помидоры грушевидные, огурец, перец и оливковое масло extra virgin с поместья.'],
    ],
    'Croquetas de jamón ibérico' => [
        'en' => ['name' => 'Iberian ham croquettes', 'description' => 'House recipe, creamy béchamel and 36-month cured ham. Six pieces.'],
        'ru' => ['name' => 'Крокеты с иберийской ветчиной', 'description' => 'Домашний рецепт, нежная бешамель и ветчина 36 месяцев выдержки. 6 штук.'],
    ],
    'Ensalada de burrata' => [
        'en' => ['name' => 'Burrata salad', 'description' => 'Fresh burrata, confit cherry tomatoes, basil pesto and balsamic reduction.'],
        'ru' => ['name' => 'Салат с burrata', 'description' => 'Свежая burrata, томаты черри confit, pesto из базилика и бalsamic reduction.'],
    ],
    'Solomillo al Pedro Ximénez' => [
        'en' => ['name' => 'Sirloin with Pedro Ximénez', 'description' => 'Beef sirloin, Pedro Ximénez reduction, confit potatoes and seasonal vegetables.'],
        'ru' => ['name' => 'Вырезка с Pedro Ximénez', 'description' => 'Говяжья вырезка, соус Pedro Ximénez, картофель confit и сезонные овощи.'],
    ],
    'Lubina a la espalda' => [
        'en' => ['name' => 'Grilled sea bass', 'description' => 'Wild sea bass, tender garlic soffrito, guindilla pepper and garden vegetables.'],
        'ru' => ['name' => 'Морской судак на гриле', 'description' => 'Дикая лубина, нежный refrito из чеснока, перец guindilla и овощи с огорода.'],
    ],
    'Arroz meloso de setas' => [
        'en' => ['name' => 'Mushroom risotto', 'description' => 'Bomba rice, seasonal mushrooms, Parmigiano Reggiano and truffle oil.'],
        'ru' => ['name' => 'Ризотто с грибами', 'description' => 'Рис bomba, сезонные грибы, Parmigiano Reggiano и масло трюфеля.'],
    ],
    'Tarta de queso' => [
        'en' => ['name' => 'Cheesecake', 'description' => 'Basque style, baked to order. Red berry coulis from the orchard.'],
        'ru' => ['name' => 'Чизкейк', 'description' => 'По-баскски, печётся на заказ. Coulis из красных ягод с огорода.'],
    ],
    'Brownie con helado' => [
        'en' => ['name' => 'Brownie with ice cream', 'description' => '70% chocolate, artisan vanilla ice cream and hazelnut crumble.'],
        'ru' => ['name' => 'Брауни с мороженым', 'description' => 'Шоколад 70 %, ванильное мороженое ручной работы и crumble из фундука.'],
    ],
];

$sections = Section::where('company_id', $company->id)->get();
$sectionCount = 0;
$productCount = 0;

foreach ($sections as $section) {
    $map = $sectionTranslations[$section->name] ?? null;
    if (! $map) {
        continue;
    }

    foreach (['en', 'ru'] as $locale) {
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

    foreach (['en', 'ru'] as $locale) {
        ProductTranslation::updateOrCreate(
            ['product_id' => $product->id, 'locale' => $locale],
            [
                'name' => $map[$locale]['name'],
                'description' => $map[$locale]['description'],
                'source' => ProductTranslation::SOURCE_AI,
            ]
        );
        $productCount++;
    }
}

echo "Carta demo multilingüe lista.\n";
echo "  Idiomas: ES (base) + EN + RU\n";
echo "  Secciones traducidas: {$sectionCount} registros\n";
echo "  Platos traducidos: {$productCount} registros\n";
echo "  Ver: http://127.0.0.1:8000/carta/demo?lang=en\n";
echo "  Ver: http://127.0.0.1:8000/carta/demo?lang=ru\n";
