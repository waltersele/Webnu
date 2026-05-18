<?php

/**
 * Datos de demostración local: usuario, negocio, secciones, platos con fotos y alérgenos.
 * Uso: php scripts/seed-local-demo.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Allergen;
use App\Company;
use App\Product;
use App\Section;
use App\Services\AllergenCatalogService;
use App\User;
use Illuminate\Support\Facades\Hash;

$user = User::firstOrCreate(
    ['email' => 'demo@webnu.local'],
    [
        'name' => 'Demo Local',
        'password' => Hash::make('demo123'),
    ]
);

$company = Company::firstOrCreate(
    ['slug' => 'demo'],
    [
        'name' => 'Restaurante Demo',
        'chef_name' => 'Chef Demo',
        'address' => 'Calle Ejemplo 1',
        'postal_code' => '03001',
        'city' => 'Alicante',
        'province' => 'Alicante',
        'country' => 'España',
        'phone' => '600000000',
        'mobile_phone' => '600000000',
        'email' => 'demo@test.com',
        'web' => '',
        'whatsapp' => '',
        'menu_type' => 1,
        'enabled' => true,
        'user_id' => $user->id,
        'reservation' => 0,
        'template' => 'basic',
    ]
);
$company->user_id = $user->id;
$company->save();

app(AllergenCatalogService::class)->sync();
$allergens = Allergen::orderBy('name')->get();
$allergenIds = $allergens->pluck('id')->all();

$sectionsData = [
    'Entrantes' => 0,
    'Principales' => 1,
    'Postres' => 2,
];

$sections = [];
foreach ($sectionsData as $name => $order) {
    $sections[$name] = Section::firstOrCreate(
        ['company_id' => $company->id, 'name' => $name],
        ['order' => $order, 'enabled' => 1]
    );
}

$dishes = [
    ['section' => 'Entrantes', 'name' => 'Gazpacho andaluz', 'description' => 'Tomate, pepino y un toque de aove de la casa.', 'price' => '7.50', 'image' => 'demo-gazpacho.jpg', 'allergens' => ['Apio', 'Sulfitos'], 'highlight' => 'featured'],
    ['section' => 'Entrantes', 'name' => 'Croquetas de jamón', 'description' => 'Cremosas por dentro, crujientes por fuera. 6 unidades.', 'price' => '9.00', 'image' => 'demo-croquetas.jpg', 'allergens' => ['Gluten', 'Lácteos', 'Huevos']],
    ['section' => 'Entrantes', 'name' => 'Ensalada de burrata', 'description' => 'Burrata, tomate cherry confitado y pesto de albahaca.', 'price' => '12.50', 'image' => 'demo-ensalada.jpg', 'allergens' => ['Lácteos']],
    ['section' => 'Principales', 'name' => 'Solomillo al Pedro Ximénez', 'description' => 'Con reducción dulce de Pedro Ximénez y patata confitada.', 'price' => '24.50', 'image' => 'demo-solomillo.jpg', 'allergens' => ['Sulfitos'], 'highlight' => 'bestseller'],
    ['section' => 'Principales', 'name' => 'Lubina a la espalda', 'description' => 'Con refrito de ajos tiernos y guarnición de verduras.', 'price' => '22.00', 'image' => 'demo-lubina.jpg', 'allergens' => ['Pescados']],
    ['section' => 'Principales', 'name' => 'Arroz meloso de setas', 'description' => 'Arroz cremoso con setas de temporada y parmesano.', 'price' => '18.00', 'image' => 'demo-arroz.jpg', 'allergens' => ['Lácteos', 'Sulfitos']],
    ['section' => 'Postres', 'name' => 'Tarta de queso', 'description' => 'Estilo vasco, horneada al momento. Con coulis de frutos rojos.', 'price' => '6.50', 'image' => 'demo-tarta.jpg', 'allergens' => ['Gluten', 'Lácteos', 'Huevos'], 'highlight' => 'new'],
    ['section' => 'Postres', 'name' => 'Brownie con helado', 'description' => 'Chocolate intenso, helado de vainilla y crumble de nueces.', 'price' => '7.00', 'image' => 'demo-brownie.jpg', 'allergens' => ['Gluten', 'Lácteos', 'Huevos', 'Frutos secos']],
];

$imgDir = public_path('img/productos');
if (!is_dir($imgDir)) {
    mkdir($imgDir, 0755, true);
}

/**
 * Descarga una imagen de comida (Picsum) o genera un JPEG de color si falla la red.
 */
function ensureDemoImage(string $dir, string $filename, int $seed): string
{
    $path = $dir . DIRECTORY_SEPARATOR . $filename;
    $relative = 'productos/' . $filename;

    if (is_file($path) && filesize($path) > 5000) {
        return $relative;
    }

    $url = 'https://picsum.photos/seed/webnu' . $seed . '/640/480.jpg';
    $ctx = stream_context_create([
        'http' => ['timeout' => 15, 'user_agent' => 'WebnuLocalSeeder/1.0'],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    $data = @file_get_contents($url, false, $ctx);

    if ($data && strlen($data) > 5000) {
        file_put_contents($path, $data);
        return $relative;
    }

    if (!function_exists('imagecreatetruecolor')) {
        return $relative;
    }

    $w = 640;
    $h = 480;
    $img = imagecreatetruecolor($w, $h);
    $hue = ($seed * 47) % 360;
    $rgb = hsvToRgb($hue, 0.35, 0.75);
    $bg = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
    imagefill($img, 0, 0, $bg);
    $white = imagecolorallocate($img, 255, 255, 255);
    imagestring($img, 5, (int) ($w / 2 - 40), (int) ($h / 2 - 10), 'Webnu Demo', $white);
    imagejpeg($img, $path, 88);
    imagedestroy($img);

    return $relative;
}

function hsvToRgb(int $h, float $s, float $v): array
{
    $c = $v * $s;
    $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
    $m = $v - $c;
    if ($h < 60) {
        [$r, $g, $b] = [$c, $x, 0];
    } elseif ($h < 120) {
        [$r, $g, $b] = [$x, $c, 0];
    } elseif ($h < 180) {
        [$r, $g, $b] = [0, $c, $x];
    } elseif ($h < 240) {
        [$r, $g, $b] = [0, $x, $c];
    } elseif ($h < 300) {
        [$r, $g, $b] = [$x, 0, $c];
    } else {
        [$r, $g, $b] = [$c, 0, $x];
    }

    return [
        (int) round(($r + $m) * 255),
        (int) round(($g + $m) * 255),
        (int) round(($b + $m) * 255),
    ];
}

$order = 0;
$seed = 1;
foreach ($dishes as $dish) {
    $section = $sections[$dish['section']];
    $imagePath = ensureDemoImage($imgDir, $dish['image'], $seed++);

    $product = Product::updateOrCreate(
        [
            'section_id' => $section->id,
            'name' => $dish['name'],
        ],
        [
            'description' => $dish['description'],
            'price_unit' => $dish['price'],
            'price_portion' => null,
            'individual_sale' => false,
            'weight_sale' => false,
            'image' => $imagePath,
            'video' => null,
            'order' => $order++,
            'enabled' => true,
            'highlight' => $dish['highlight'] ?? null,
        ]
    );

    $ids = $allergens->filter(function ($a) use ($dish) {
        return in_array($a->name, $dish['allergens'], true);
    })->pluck('id')->all();

    $product->allergens()->sync($ids);
}

// Mantener compatibilidad: plato demo original
$entrantes = $sections['Entrantes'];
$legacy = Product::updateOrCreate(
    ['section_id' => $entrantes->id, 'name' => 'Plato demo'],
    [
        'description' => 'Prueba del panel en local',
        'price_unit' => '12.50',
        'individual_sale' => false,
        'weight_sale' => false,
        'image' => ensureDemoImage($imgDir, 'demo-plato-demo.jpg', 99),
        'order' => 0,
        'enabled' => true,
    ]
);
$legacy->allergens()->sync(array_slice($allergenIds, 0, 2));

echo "Demo listo.\n";
echo "  Login: demo@webnu.local / demo123\n";
echo "  Negocio: {$company->name} (id={$company->id}, user_id={$user->id})\n";
echo "  Platos: " . Product::whereIn('section_id', collect($sections)->pluck('id'))->count() . " con fotos en public/img/productos/\n";
