<?php

/**
 * Cartas demo curadas por plantilla + assets (fotos y reels).
 * Uso: php scripts/seed-local-demo.php
 *      php scripts/seed-local-demo.php --refresh-images
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Allergen;
use App\Company;
use App\Product;
use App\ProductTranslation;
use App\Section;
use App\Services\AllergenCatalogService;
use App\User;
use Illuminate\Support\Facades\Hash;

$keepImages = ! in_array('--refresh-images', $argv ?? [], true);

$user = User::firstOrCreate(
    ['email' => 'demo@webnu.local'],
    [
        'name' => 'Demo Local',
        'password' => Hash::make('demo123'),
        'plan' => 'plus',
    ]
);
$user->plan = 'plus';
$user->save();

/** Fotos compartidas (Pexels, uso demo). */
$dishImages = [
    'brasa-gazpacho.jpg' => 'https://images.pexels.com/photos/4871275/pexels-photo-4871275.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'brasa-croquetas.jpg' => 'https://images.pexels.com/photos/14734398/pexels-photo-14734398.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'brasa-burrata.jpg' => 'https://images.pexels.com/photos/1059905/pexels-photo-1059905.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'brasa-solomillo.jpg' => 'https://images.pexels.com/photos/769289/pexels-photo-769289.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'brasa-lubina.jpg' => 'https://images.pexels.com/photos/3298687/pexels-photo-3298687.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'brasa-arroz-setas.jpg' => 'https://images.pexels.com/photos/691114/pexels-photo-691114.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'brasa-tarta-queso.jpg' => 'https://images.pexels.com/photos/3026809/pexels-photo-3026809.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'brasa-brownie.jpg' => 'https://images.pexels.com/photos/452516/pexels-photo-452516.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'cocktail-negroni.jpg' => 'https://images.pexels.com/photos/1304540/pexels-photo-1304540.jpeg?auto=compress&cs=tinysrgb&w=800&h=1200&fit=crop',
    'cocktail-margarita.jpg' => 'https://images.pexels.com/photos/1283219/pexels-photo-1283219.jpeg?auto=compress&cs=tinysrgb&w=800&h=1200&fit=crop',
    'cocktail-mojito.jpg' => 'https://images.pexels.com/photos/1552630/pexels-photo-1552630.jpeg?auto=compress&cs=tinysrgb&w=800&h=1200&fit=crop',
    'cocktail-gintonic.jpg' => 'https://images.pexels.com/photos/616836/pexels-photo-616836.jpeg?auto=compress&cs=tinysrgb&w=800&h=1200&fit=crop',
    'cocktail-whiskey.jpg' => 'https://images.pexels.com/photos/2744719/pexels-photo-2744719.jpeg?auto=compress&cs=tinysrgb&w=800&h=1200&fit=crop',
    'limonata-casera.jpg' => 'https://images.pexels.com/photos/2789328/pexels-photo-2789328.jpeg?auto=compress&cs=tinysrgb&w=800&h=1200&fit=crop',
    'fuego-gyozas.jpg' => 'https://images.pexels.com/photos/35763726/pexels-photo-35763726.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'fuego-karaage.jpg' => 'https://images.pexels.com/photos/60616/fried-chicken-chicken-fried-crunchy-60616.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'fuego-tonkotsu.jpg' => 'https://images.pexels.com/photos/725991/pexels-photo-725991.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'fuego-buldak.jpg' => 'https://images.pexels.com/photos/725991/pexels-photo-725991.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'fuego-yakitori.jpg' => 'https://images.pexels.com/photos/5560763/pexels-photo-5560763.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'fuego-mochi.jpg' => 'https://images.pexels.com/photos/4491282/pexels-photo-4491282.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'fastfood-smash.jpg' => 'https://images.pexels.com/photos/1639562/pexels-photo-1639562.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'fastfood-bacon.jpg' => 'https://images.pexels.com/photos/156114/pexels-photo-156114.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'fastfood-fries.jpg' => 'https://images.pexels.com/photos/1581384/pexels-photo-1581384.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'fastfood-chicken.jpg' => 'https://images.pexels.com/photos/60616/fried-chicken-chicken-fried-crunchy-60616.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'fastfood-shake.jpg' => 'https://images.pexels.com/photos/103566/pexels-photo-103566.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'fastfood-combo.jpg' => 'https://images.pexels.com/photos/1199957/pexels-photo-1199957.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'pizza-margherita.jpg' => 'https://images.pexels.com/photos/1146760/pexels-photo-1146760.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'pizza-quattro.jpg' => 'https://images.pexels.com/photos/825661/pexels-photo-825661.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'pizza-diavola.jpg' => 'https://images.pexels.com/photos/1590874/pexels-photo-1590874.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
    'pizza-burrata.jpg' => 'https://images.pexels.com/photos/143133/pexels-photo-143133.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
];

$brandImages = [
    'demo/demo-header.jpg' => 'https://images.pexels.com/photos/262978/pexels-photo-262978.jpeg?auto=compress&cs=tinysrgb&w=1600&h=900&fit=crop',
    'demo/demo-logo.jpg' => 'https://images.pexels.com/photos/262978/pexels-photo-262978.jpeg?auto=compress&cs=tinysrgb&w=512&h=512&fit=crop',
    'demo/demo-cocktails-header.jpg' => 'https://images.pexels.com/photos/1267320/pexels-photo-1267320.jpeg?auto=compress&cs=tinysrgb&w=1600&h=900&fit=crop',
    'demo/demo-fuego-header.jpg' => 'https://images.pexels.com/photos/5560763/pexels-photo-5560763.jpeg?auto=compress&cs=tinysrgb&w=1600&h=900&fit=crop',
    'demo/demo-japo-header.jpg' => 'https://images.pexels.com/photos/35763726/pexels-photo-35763726.jpeg?auto=compress&cs=tinysrgb&w=1600&h=900&fit=crop',
    'demo/demo-fastfood-header.jpg' => 'https://images.pexels.com/photos/1639562/pexels-photo-1639562.jpeg?auto=compress&cs=tinysrgb&w=1600&h=900&fit=crop',
    'demo/demo-pizza-header.jpg' => 'https://images.pexels.com/photos/1146760/pexels-photo-1146760.jpeg?auto=compress&cs=tinysrgb&w=1600&h=900&fit=crop',
    'demo/demo-mar-header.jpg' => 'https://images.pexels.com/photos/3298687/pexels-photo-3298687.jpeg?auto=compress&cs=tinysrgb&w=1600&h=900&fit=crop',
    'demo/demo-elegance-header.jpg' => 'https://images.pexels.com/photos/1059905/pexels-photo-1059905.jpeg?auto=compress&cs=tinysrgb&w=1600&h=900&fit=crop',
    'demo/demo-asador-header.jpg' => 'https://images.pexels.com/photos/769289/pexels-photo-769289.jpeg?auto=compress&cs=tinysrgb&w=1600&h=900&fit=crop',
    'default-header.jpg' => 'https://images.pexels.com/photos/262978/pexels-photo-262978.jpeg?auto=compress&cs=tinysrgb&w=1600&h=900&fit=crop',
];

/** @return array<string, string> relative path => remote URL */
function demoVideoDownloadMap(): array
{
    $map = [];
    foreach (config('demo_media.videos', []) as $meta) {
        $map['demo/' . $meta['file']] = $meta['remote_url'];
    }

    return $map;
}

/** @param string|null $key clave semántica (steak, cocktail, …) o null */
function demoVideoPath(?string $key): ?string
{
    if ($key === null || $key === '') {
        return null;
    }

    $meta = config('demo_media.videos.' . $key);

    return $meta ? 'demo/' . $meta['file'] : null;
}

$demoVideos = demoVideoDownloadMap();

$restaurantDishes = [
    ['section' => 'Entrantes', 'name' => 'Gazpacho andaluz', 'description' => 'Tomate pera, pepino, pimiento y aceite de oliva virgen extra de la finca.', 'price' => '7.50', 'image' => 'brasa-gazpacho.jpg', 'allergens' => ['Apio', 'Sulfitos'], 'highlight' => 'featured', 'video' => null],
    ['section' => 'Entrantes', 'name' => 'Croquetas de jamón ibérico', 'description' => 'Receta de la casa, bechamel cremosa y jamón 36 meses. Seis unidades.', 'price' => '9.50', 'image' => 'brasa-croquetas.jpg', 'allergens' => ['Gluten', 'Lácteos', 'Huevos'], 'video' => null],
    ['section' => 'Entrantes', 'name' => 'Ensalada de burrata', 'description' => 'Burrata fresca, tomate cherry confitado, pesto de albahaca y reducción balsámica.', 'price' => '12.50', 'image' => 'brasa-burrata.jpg', 'allergens' => ['Lácteos'], 'video' => null],
    ['section' => 'Principales', 'name' => 'Solomillo al Pedro Ximénez', 'description' => 'Solomillo de ternera, reducción de Pedro Ximénez, patata confitada y verduras de temporada.', 'price' => '24.50', 'image' => 'brasa-solomillo.jpg', 'allergens' => ['Sulfitos'], 'highlight' => 'bestseller', 'video' => 'steak'],
    ['section' => 'Principales', 'name' => 'Lubina a la espalda', 'description' => 'Lubina salvaje, refrito de ajos tiernos, guindilla y guarnición de verduras de la huerta.', 'price' => '22.00', 'image' => 'brasa-lubina.jpg', 'allergens' => ['Pescados'], 'video' => 'fish'],
    ['section' => 'Principales', 'name' => 'Arroz meloso de setas', 'description' => 'Arroz bomba, setas de temporada, parmesano reggiano y aceite de trufa.', 'price' => '18.00', 'image' => 'brasa-arroz-setas.jpg', 'allergens' => ['Lácteos', 'Sulfitos'], 'video' => null],
    ['section' => 'Postres', 'name' => 'Tarta de queso', 'description' => 'Estilo vasco, horneada al momento. Coulis de frutos rojos de la huerta.', 'price' => '6.50', 'image' => 'brasa-tarta-queso.jpg', 'allergens' => ['Gluten', 'Lácteos', 'Huevos'], 'highlight' => 'new', 'video' => null],
    ['section' => 'Postres', 'name' => 'Brownie con helado', 'description' => 'Chocolate 70 %, helado artesano de vainilla y crumble de avellanas.', 'price' => '7.00', 'image' => 'brasa-brownie.jpg', 'allergens' => ['Gluten', 'Lácteos', 'Huevos', 'Frutos secos'], 'video' => 'dessert'],
];

$cocktailDishes = [
    ['section' => 'Signature', 'name' => 'Negroni del Puerto', 'description' => 'Gin mediterráneo, vermut rojo, bitter de naranja y piel de cítricos flameada.', 'price' => '11.00', 'image' => 'cocktail-negroni.jpg', 'allergens' => ['Sulfitos'], 'highlight' => 'featured', 'video' => 'bar'],
    ['section' => 'Signature', 'name' => 'Margarita de autor', 'description' => 'Tequila reposado, triple sec, lima fresca y sal ahumada en el copo.', 'price' => '10.50', 'image' => 'cocktail-margarita.jpg', 'allergens' => [], 'highlight' => 'featured', 'video' => 'cocktail'],
    ['section' => 'Clásicos', 'name' => 'Mojito de hierbabuena', 'description' => 'Ron blanco, hierbabuena fresca, lima, azúcar de caña y soda.', 'price' => '9.00', 'image' => 'cocktail-mojito.jpg', 'allergens' => ['Sulfitos'], 'highlight' => 'featured', 'video' => 'bar_mix'],
    ['section' => 'Clásicos', 'name' => 'Gin tonic mediterráneo', 'description' => 'Gin botánico, tónica premium, romero y cáscara de pomelo.', 'price' => '9.50', 'image' => 'cocktail-gintonic.jpg', 'allergens' => ['Sulfitos'], 'highlight' => 'featured', 'video' => 'bar_night'],
    ['section' => 'Clásicos', 'name' => 'Old Fashioned', 'description' => 'Bourbon, bitter aromatic, azúcar y twist de naranja. Servido en roca.', 'price' => '11.50', 'image' => 'cocktail-whiskey.jpg', 'allergens' => [], 'highlight' => 'featured', 'video' => 'shake'],
];

$fuegoDishes = [
    ['section' => '前菜 · Entrantes', 'name' => 'Gyozas 餃子', 'description' => '🇪🇸 Empanadillas asiáticas a la plancha. Relleno de cerdo o verduras. 📍 Manchuria, China 🇨🇳', 'price' => '3.95', 'image' => 'fuego-gyozas.jpg', 'allergens' => ['Gluten', 'Soja'], 'highlight' => 'featured', 'video' => 'asian_fry'],
    ['section' => '前菜 · Entrantes', 'name' => 'Tori no Karaage 唐揚げ', 'description' => '🇪🇸 Pollo frito japonés. Marinado en jengibre, soja y mirin. 📍 Beppu, Japón 🇯🇵', 'price' => '4.95', 'image' => 'fuego-karaage.jpg', 'allergens' => ['Gluten', 'Soja'], 'video' => 'fried_chicken'],
    ['section' => '前菜 · Entrantes', 'name' => 'Yakitori 焼き鳥', 'description' => '🇪🇸 Brochetas a la brasa. Pollo campero o vaca gallega madurada. 📍 Tokyo, Japón 🇯🇵', 'price' => '4.95', 'image' => 'fuego-yakitori.jpg', 'allergens' => ['Soja'], 'highlight' => 'bestseller', 'video' => 'steak'],
    ['section' => '麺 · Ramen', 'name' => 'Tonkotsu Ramen 豚骨', 'description' => '🇪🇸 Caldo de cerdo estilo Yokohama. Huevo macerado, brotes de bambú y cebollino. PEQUEÑO 9,95 € · GRANDE 11,95 €. 📍 Yokohama, Japón 🇯🇵', 'price' => '11.95', 'image' => 'fuego-tonkotsu.jpg', 'allergens' => ['Gluten', 'Soja', 'Huevos'], 'highlight' => 'bestseller', 'video' => 'ramen'],
    ['section' => '麺 · Ramen', 'name' => 'Haek Buldak Ramen 🔥🇰🇷', 'description' => '🇪🇸 Ramen seco con salsa picante nuclear. Carne picada, huevo poché y verduras frescas. 📍 Seúl, Corea del Sur 🇰🇷', 'price' => '11.95', 'image' => 'fuego-buldak.jpg', 'allergens' => ['Gluten', 'Soja', 'Huevos'], 'highlight' => 'featured', 'video' => 'ramen'],
    ['section' => '甘味 · Dulces', 'name' => 'Mochi Matcha 抹茶大福', 'description' => 'Masa gyuhi casera con mousse de matcha ceremonial y corazón de fresa. 📍 Receta propia ㊙️', 'price' => '5.95', 'image' => 'fuego-mochi.jpg', 'allergens' => ['Lácteos'], 'highlight' => 'new', 'video' => 'dessert'],
];

$japoDishes = [
    ['section' => '前菜 · Entradas', 'name' => 'Edamame 枝豆', 'description' => 'Vainas de soja al vapor con sal de mar. 📍 Kyoto, Japón 🇯🇵', 'price' => '4.50', 'image' => 'fuego-gyozas.jpg', 'allergens' => ['Soja'], 'video' => null],
    ['section' => '前菜 · Entradas', 'name' => 'Sashimi del día 刺身', 'description' => 'Selección de pescado fresco del mercado. Wasabi y jengibre encurtido. 📍 Tsukiji, Japón 🇯🇵', 'price' => '14.50', 'image' => 'brasa-lubina.jpg', 'allergens' => ['Pescados', 'Soja'], 'highlight' => 'featured', 'video' => 'fish'],
    ['section' => '丼 · Arroz', 'name' => 'Gyudon 牛丼', 'description' => 'Bol de arroz con ternera, cebolla y salsa dashi. Huevo poché y shichimi. 📍 Tokio, Japón 🇯🇵', 'price' => '8.50', 'image' => 'brasa-arroz-setas.jpg', 'allergens' => ['Gluten', 'Soja', 'Huevos'], 'highlight' => 'bestseller', 'video' => 'pasta_rice'],
    ['section' => '温 · Caliente', 'name' => 'Miso Ramen 味噌', 'description' => 'Caldo tonkotsu con pasta de miso, chashu, repollo chino y huevo macerado. 📍 Hokkaido, Japón 🇯🇵', 'price' => '11.95', 'image' => 'fuego-tonkotsu.jpg', 'allergens' => ['Gluten', 'Soja', 'Huevos'], 'video' => 'ramen'],
    ['section' => '甘 · Dulce', 'name' => 'Mochi de té verde', 'description' => 'Daifuku casero con matcha ceremonial. 📍 Receta propia ㊙️', 'price' => '5.50', 'image' => 'fuego-mochi.jpg', 'allergens' => ['Lácteos'], 'video' => 'dessert'],
];

$pizzaDishes = [
    ['section' => 'Pizzas clásicas', 'name' => 'Margherita DOP', 'description' => 'Tomate San Marzano, mozzarella fior di latte, albahaca fresca y AOVE.', 'price' => '10.50', 'image' => 'pizza-margherita.jpg', 'allergens' => ['Gluten', 'Lácteos'], 'highlight' => 'bestseller', 'video' => 'pizza'],
    ['section' => 'Pizzas clásicas', 'name' => 'Diavola piccante', 'description' => 'Salami picante, mozzarella y aceite de chile.', 'price' => '12.50', 'image' => 'pizza-diavola.jpg', 'allergens' => ['Gluten', 'Lácteos'], 'highlight' => 'featured', 'video' => 'pizza'],
    ['section' => 'Especiales', 'name' => 'Quattro formaggi', 'description' => 'Mozzarella, gorgonzola, parmesano y fontina.', 'price' => '13.90', 'image' => 'pizza-quattro.jpg', 'allergens' => ['Gluten', 'Lácteos'], 'video' => 'pizza'],
    ['section' => 'Especiales', 'name' => 'Prosciutto e rúcula', 'description' => 'Base blanca, jamón curado, rúcula y parmesano en láminas.', 'price' => '14.50', 'image' => 'pizza-burrata.jpg', 'allergens' => ['Gluten', 'Lácteos'], 'highlight' => 'new', 'video' => null],
    ['section' => 'Entrantes', 'name' => 'Focaccia al rosmarino', 'description' => 'Pan artesano con sal en escamas y romero.', 'price' => '5.50', 'image' => 'brasa-burrata.jpg', 'allergens' => ['Gluten'], 'video' => null],
    ['section' => 'Bebidas', 'name' => 'Limonata casera', 'description' => 'Limón siciliano, menta y agua con gas.', 'price' => '3.50', 'image' => 'limonata-casera.jpg', 'allergens' => [], 'video' => null],
];

$fastfoodDishes = [
    ['section' => 'Combos', 'name' => 'Menú Smash', 'description' => 'Smash burger clásico + patatas crujientes + refresco 33 cl.', 'price' => '11.90', 'image' => 'fastfood-combo.jpg', 'allergens' => ['Gluten', 'Lácteos'], 'highlight' => 'bestseller', 'video' => 'burger'],
    ['section' => 'Combos', 'name' => 'Menú Crispy', 'description' => 'Pollo crujiente + patatas + salsa a elegir.', 'price' => '10.90', 'image' => 'fastfood-chicken.jpg', 'allergens' => ['Gluten', 'Huevos'], 'highlight' => 'featured', 'video' => 'fried_chicken'],
    ['section' => 'Burgers', 'name' => 'Double Smash', 'description' => 'Doble carne smash, cheddar fundido, pepinillos y salsa house.', 'price' => '8.90', 'image' => 'fastfood-smash.jpg', 'allergens' => ['Gluten', 'Lácteos'], 'highlight' => 'bestseller', 'video' => 'burger'],
    ['section' => 'Burgers', 'name' => 'BBQ Bacon', 'description' => 'Bacon crujiente, cebolla caramelizada y salsa BBQ ahumada.', 'price' => '9.90', 'image' => 'fastfood-bacon.jpg', 'allergens' => ['Gluten', 'Lácteos'], 'video' => 'burger'],
    ['section' => 'Extras', 'name' => 'Patatas deluxe', 'description' => 'Patatas fritas con piel, sal ahumada y alioli.', 'price' => '3.50', 'image' => 'fastfood-fries.jpg', 'allergens' => ['Huevos'], 'video' => null],
    ['section' => 'Extras', 'name' => 'Nuggets x6', 'description' => 'Pollo crujiente. Elige BBQ, honey mustard o spicy.', 'price' => '5.50', 'image' => 'fastfood-chicken.jpg', 'allergens' => ['Gluten'], 'video' => 'fried_chicken'],
    ['section' => 'Bebidas', 'name' => 'Milkshake vainilla', 'description' => 'Batido cremoso con helado artesano. También chocolate o fresa.', 'price' => '4.20', 'image' => 'fastfood-shake.jpg', 'allergens' => ['Lácteos'], 'video' => 'shake'],
];

$marDishes = [
    ['section' => 'Del mar', 'name' => 'Lubina a la espalda', 'description' => 'Lubina salvaje, refrito de ajos tiernos y guindilla.', 'price' => '22.00', 'image' => 'brasa-lubina.jpg', 'allergens' => ['Pescados'], 'highlight' => 'bestseller', 'video' => 'fish'],
    ['section' => 'Del mar', 'name' => 'Gambas al ajillo', 'description' => 'Gambas rojas de Vinaròs, ajo confitado y guindilla.', 'price' => '16.50', 'image' => 'brasa-lubina.jpg', 'allergens' => ['Crustáceos'], 'highlight' => 'featured', 'video' => 'fish'],
    ['section' => 'Del mar', 'name' => 'Arroz meloso de mar', 'description' => 'Arroz bomba con caldo de pescado, sepia y alioli.', 'price' => '19.00', 'image' => 'brasa-arroz-setas.jpg', 'allergens' => ['Pescados', 'Crustáceos', 'Gluten'], 'video' => 'pasta_rice'],
    ['section' => 'Para compartir', 'name' => 'Gazpacho de tomate', 'description' => 'Tomate pera, pepino y aceite de oliva virgen extra.', 'price' => '7.50', 'image' => 'brasa-gazpacho.jpg', 'allergens' => ['Apio'], 'video' => null],
    ['section' => 'Postres', 'name' => 'Tarta de limón', 'description' => 'Merengue suave y crema de limón de la huerta.', 'price' => '6.50', 'image' => 'brasa-tarta-queso.jpg', 'allergens' => ['Gluten', 'Lácteos', 'Huevos'], 'video' => null],
];

$eleganceDishes = [
    ['section' => 'Entrantes', 'name' => 'Ensalada de burrata', 'description' => 'Burrata fresca, tomate cherry confitado y pesto de albahaca.', 'price' => '12.50', 'image' => 'brasa-burrata.jpg', 'allergens' => ['Lácteos'], 'highlight' => 'featured', 'video' => null],
    ['section' => 'Entrantes', 'name' => 'Croquetas de jamón', 'description' => 'Bechamel cremosa y jamón ibérico 36 meses.', 'price' => '9.50', 'image' => 'brasa-croquetas.jpg', 'allergens' => ['Gluten', 'Lácteos', 'Huevos'], 'video' => 'asian_fry'],
    ['section' => 'Principales', 'name' => 'Solomillo al Pedro Ximénez', 'description' => 'Solomillo de ternera, reducción de Pedro Ximénez y patata confitada.', 'price' => '24.50', 'image' => 'brasa-solomillo.jpg', 'allergens' => ['Sulfitos'], 'highlight' => 'bestseller', 'video' => 'steak'],
    ['section' => 'Principales', 'name' => 'Lubina salvaje', 'description' => 'Verduras de temporada y emulsión de azafrán.', 'price' => '22.00', 'image' => 'brasa-lubina.jpg', 'allergens' => ['Pescados'], 'video' => 'fish'],
    ['section' => 'Postres', 'name' => 'Tarta de queso', 'description' => 'Estilo vasco, horneada al momento. Coulis de frutos rojos.', 'price' => '6.50', 'image' => 'brasa-tarta-queso.jpg', 'allergens' => ['Gluten', 'Lácteos', 'Huevos'], 'highlight' => 'new', 'video' => null],
];

$asadorDishes = [
    ['section' => 'De la brasa', 'name' => 'Chuletón madurado', 'description' => 'Ternera gallega 45 días, sal en escamas y chimichurri.', 'price' => '32.00', 'image' => 'brasa-solomillo.jpg', 'allergens' => [], 'highlight' => 'bestseller', 'video' => 'steak'],
    ['section' => 'De la brasa', 'name' => 'Entrecot a la brasa', 'description' => 'Corte grueso, marcado al carbón y mantequilla de hierbas.', 'price' => '26.50', 'image' => 'brasa-solomillo.jpg', 'allergens' => ['Lácteos'], 'highlight' => 'featured', 'video' => 'steak'],
    ['section' => 'De la brasa', 'name' => 'Morcilla de Burgos', 'description' => 'A la plancha con piquillo asado.', 'price' => '8.50', 'image' => 'brasa-croquetas.jpg', 'allergens' => [], 'video' => 'steak'],
    ['section' => 'Guarniciones', 'name' => 'Pimientos de Padrón', 'description' => 'Sal gorda y aceite de oliva.', 'price' => '7.00', 'image' => 'brasa-gazpacho.jpg', 'allergens' => [], 'video' => null],
    ['section' => 'Guarniciones', 'name' => 'Patata confitada', 'description' => 'Patata baby, ajo y romero.', 'price' => '5.50', 'image' => 'brasa-arroz-setas.jpg', 'allergens' => [], 'video' => null],
];

$demoCompanies = [
    [
        'slug' => 'demo',
        'name' => 'La Brasa del Puerto',
        'chef_name' => 'Ana García',
        'template' => 'basic',
        'comments' => 'Cocina mediterránea con brasa a la vista y pescado del día en el puerto.',
        'background_header' => 'demo/demo-header.jpg',
        'logo' => 'demo/demo-logo.jpg',
        'theme_settings' => null,
        'enabled_locales' => ['en'],
        'sections' => ['Entrantes' => 0, 'Principales' => 1, 'Postres' => 2],
        'dishes' => $restaurantDishes,
    ],
    [
        'slug' => 'demo-cocktails',
        'name' => 'Azul Coctelería',
        'chef_name' => 'Marcos Leiva',
        'template' => 'nocturne',
        'comments' => 'Coctelería de autor frente al mar. Copas a ancho completo con reels en cada creación.',
        'background_header' => null,
        'logo' => null,
        'theme_settings' => null,
        'sections' => ['Signature' => 0, 'Clásicos' => 1],
        'dishes' => $cocktailDishes,
    ],
    [
        'slug' => 'demo-fuego',
        'name' => 'Fuego Otaku',
        'chef_name' => 'Alicante · 炎',
        'template' => 'otaku',
        'comments' => 'Ramen, brasa viva y estética otaku. Naranja neón, kanji y caldo intenso.',
        'background_header' => 'demo/demo-fuego-header.jpg',
        'logo' => 'demo/demo-logo.jpg',
        'theme_settings' => [
            'primary' => '#ff5500',
            'accent' => '#ffb800',
            'background' => '#0a0a0a',
            'surface' => '#141414',
            'text' => '#ffffff',
            'text_muted' => '#ff9944',
            'font_heading' => 'bebas_neue',
            'font_body' => 'noto_sans_jp',
        ],
        'sections' => ['前菜 · Entrantes' => 0, '麺 · Ramen' => 1, '甘味 · Dulces' => 2],
        'dishes' => $fuegoDishes,
    ],
    [
        'slug' => 'demo-japo',
        'name' => 'Sakura House',
        'chef_name' => 'Kyoto · 京都',
        'template' => 'japo',
        'comments' => 'Cocina japonesa clásica: rojo lacado, negro y oro.',
        'background_header' => 'demo/demo-japo-header.jpg',
        'logo' => 'demo/demo-logo.jpg',
        'theme_settings' => null,
        'sections' => ['前菜 · Entradas' => 0, '丼 · Arroz' => 1, '温 · Caliente' => 2, '甘 · Dulce' => 3],
        'dishes' => $japoDishes,
    ],
    [
        'slug' => 'demo-fastfood',
        'name' => 'Burger & Go',
        'chef_name' => 'Smash · 24h',
        'template' => 'fastfood',
        'comments' => 'Smash burgers, combos y extras. Listo en minutos.',
        'background_header' => 'demo/demo-fastfood-header.jpg',
        'logo' => 'demo/demo-logo.jpg',
        'theme_settings' => null,
        'sections' => ['Combos' => 0, 'Burgers' => 1, 'Extras' => 2, 'Bebidas' => 3],
        'dishes' => $fastfoodDishes,
    ],
    [
        'slug' => 'demo-pizza',
        'name' => 'Forno Napoli',
        'chef_name' => 'Masa madre · horno de leña',
        'template' => 'pizza',
        'comments' => 'Pizzería napolitana: masa 48 h, tomate italiano y mozzarella di bufala.',
        'background_header' => 'demo/demo-pizza-header.jpg',
        'logo' => 'demo/demo-logo.jpg',
        'theme_settings' => null,
        'sections' => ['Pizzas clásicas' => 0, 'Especiales' => 1, 'Entrantes' => 2, 'Bebidas' => 3],
        'dishes' => $pizzaDishes,
    ],
    [
        'slug' => 'demo-mar',
        'name' => 'Marisquería Costa',
        'chef_name' => 'Puerto · Alicante',
        'template' => 'mar',
        'comments' => 'Pescado del día, arroces y brisa mediterránea.',
        'background_header' => 'demo/demo-mar-header.jpg',
        'logo' => 'demo/demo-logo.jpg',
        'theme_settings' => null,
        'sections' => ['Del mar' => 0, 'Para compartir' => 1, 'Postres' => 2],
        'dishes' => $marDishes,
    ],
    [
        'slug' => 'demo-elegance',
        'name' => 'Le Jardin',
        'chef_name' => 'Chef Élise Martin',
        'template' => 'elegance',
        'comments' => 'Fine dining con espacio, serif y acentos dorados.',
        'background_header' => 'demo/demo-elegance-header.jpg',
        'logo' => 'demo/demo-logo.jpg',
        'theme_settings' => null,
        'sections' => ['Entrantes' => 0, 'Principales' => 1, 'Postres' => 2],
        'dishes' => $eleganceDishes,
    ],
    [
        'slug' => 'demo-asador',
        'name' => 'Brasa & Carbón',
        'chef_name' => 'Asador tradicional',
        'template' => 'asador',
        'comments' => 'Carnes a la brasa, carbón vivo y guarniciones de la huerta.',
        'background_header' => 'demo/demo-asador-header.jpg',
        'logo' => 'demo/demo-logo.jpg',
        'theme_settings' => null,
        'sections' => ['De la brasa' => 0, 'Guarniciones' => 1],
        'dishes' => $asadorDishes,
    ],
];

$imgRoot = public_path('img');

function downloadCuratedAsset(string $imgRoot, string $relativePath, string $url, bool $keepExisting, int $minBytes = 8000): ?string
{
    $path = $imgRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    $dir = dirname($path);

    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    if ($keepExisting && is_file($path) && filesize($path) > $minBytes) {
        return $relativePath;
    }

    $forceRefresh = ! $keepExisting;

    $ctx = stream_context_create([
        'http' => [
            'timeout' => 60,
            'user_agent' => 'WebnuLocalSeeder/3.0',
            'header' => "Accept: */*\r\n",
        ],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);

    $caPem = dirname(__DIR__) . '/resources/certs/cacert.pem';
    if (is_file($caPem)) {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 60,
                'user_agent' => 'WebnuLocalSeeder/3.0',
                'header' => "Accept: */*\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'cafile' => $caPem,
            ],
        ]);
    }

    $data = @file_get_contents($url, false, $ctx);

    if (! $data || strlen($data) < $minBytes) {
        echo "  Aviso: no se pudo descargar {$relativePath}\n";

        if ($forceRefresh && is_file($path)) {
            @unlink($path);
        }

        return null;
    }

    file_put_contents($path, $data);

    return $relativePath;
}

function seedDemoCompany(array $config, User $user, $allergens): Company
{
    $company = Company::firstOrCreate(
        ['slug' => $config['slug']],
        [
            'name' => $config['name'],
            'chef_name' => $config['chef_name'],
            'address' => 'Muelle Poniente, 12',
            'postal_code' => '03001',
            'city' => 'Alicante',
            'province' => 'Alicante',
            'country' => 'España',
            'phone' => '965214087',
            'mobile_phone' => '665214087',
            'email' => 'reservas@labrasadelpuerto.es',
            'web' => 'https://labrasadelpuerto.es',
            'whatsapp' => '34665214087',
            'menu_type' => 1,
            'enabled' => true,
            'user_id' => $user->id,
            'reservation' => 1,
            'template' => $config['template'],
            'comments' => $config['comments'],
            'schedule' => "Mar–Dom 13:00–16:00 · 20:00–23:30\nLunes cerrado",
            'instagram' => 'labrasadelpuerto',
        ]
    );

    $company->user_id = $user->id;
    $company->name = $config['name'];
    $company->chef_name = $config['chef_name'];
    $company->template = $config['template'];
    $company->comments = $config['comments'];
    $company->background_header = $config['background_header'];
    $company->logo = $config['logo'];
    $company->theme_settings = $config['theme_settings'];
    $company->enabled = true;
    if (isset($config['enabled_locales'])) {
        $company->enabled_locales = $config['enabled_locales'];
        $company->default_locale = $config['default_locale'] ?? 'es';
    }
    $company->save();

    $existingSectionIds = Section::where('company_id', $company->id)->pluck('id');
    if ($existingSectionIds->isNotEmpty()) {
        Product::whereIn('section_id', $existingSectionIds)->each(function (Product $product) {
            ProductTranslation::where('product_id', $product->id)->delete();
            $product->allergens()->detach();
            $product->delete();
        });
        Section::where('company_id', $company->id)->delete();
    }

    $sections = [];
    foreach ($config['sections'] as $name => $order) {
        $sections[$name] = Section::create([
            'company_id' => $company->id,
            'name' => $name,
            'order' => $order,
            'enabled' => 1,
        ]);
    }

    $validProductNames = [];
    $sectionOrders = [];

    foreach ($config['dishes'] as $dish) {
        $section = $sections[$dish['section']];
        $order = $sectionOrders[$dish['section']] ?? 0;

        $product = Product::create(
            [
                'section_id' => $section->id,
                'name' => $dish['name'],
                'description' => $dish['description'],
                'price_unit' => $dish['price'],
                'price_portion' => null,
                'individual_sale' => false,
                'weight_sale' => false,
                'image' => 'productos/' . $dish['image'],
                'video' => demoVideoPath($dish['video'] ?? null),
                'order' => $order,
                'enabled' => true,
                'highlight' => $dish['highlight'] ?? null,
            ]
        );

        $ids = $allergens->filter(function ($a) use ($dish) {
            return in_array($a->name, $dish['allergens'] ?? [], true);
        })->pluck('id')->all();

        $product->allergens()->sync($ids);
        $sectionOrders[$dish['section']] = $order + 1;

        if (($config['slug'] ?? '') === 'demo' && in_array('en', $config['enabled_locales'] ?? [], true)) {
            $en = demoEnglishForProduct($dish['name'], $dish['description']);
            if ($en) {
                ProductTranslation::updateOrCreate(
                    ['product_id' => $product->id, 'locale' => 'en'],
                    ['name' => $en['name'], 'description' => $en['description'], 'source' => 'manual']
                );
            }
        }
    }

    return $company;
}

/** @return array{name: string, description: string}|null */
function demoEnglishForProduct(string $nameEs, string $descEs): ?array
{
    $map = [
        'Gazpacho andaluz' => ['Andalusian gazpacho', 'Pear tomato, cucumber, pepper and extra virgin olive oil.'],
        'Croquetas de jamón ibérico' => ['Iberian ham croquettes', 'House recipe with 36-month cured ham. Six pieces.'],
        'Ensalada de burrata' => ['Burrata salad', 'Fresh burrata, confit cherry tomato and basil pesto.'],
        'Solomillo al Pedro Ximénez' => ['Beef tenderloin PX', 'Beef tenderloin, Pedro Ximénez reduction and confit potato.'],
        'Lubina a la espalda' => ['Sea bass a la espalda', 'Wild sea bass, garlic and seasonal vegetables.'],
        'Arroz meloso de setas' => ['Mushroom rice', 'Bomba rice, seasonal mushrooms and parmesan.'],
        'Tarta de queso' => ['Cheesecake', 'Basque-style, baked to order. Red berry coulis.'],
        'Brownie con helado' => ['Brownie & ice cream', '70% chocolate, vanilla ice cream and hazelnut crumble.'],
    ];

    if (! isset($map[$nameEs])) {
        return null;
    }

    return ['name' => $map[$nameEs][0], 'description' => $map[$nameEs][1]];
}

echo "Descargando assets demo...\n";

foreach ($brandImages as $relative => $url) {
    downloadCuratedAsset($imgRoot, $relative, $url, $keepImages);
}

foreach ($dishImages as $filename => $url) {
    downloadCuratedAsset($imgRoot, 'productos/' . $filename, $url, $keepImages);
}

foreach ($demoVideos as $relative => $url) {
    downloadCuratedAsset($imgRoot, $relative, $url, $keepImages, 50000);
}

app(AllergenCatalogService::class)->sync();
$allergens = Allergen::orderBy('name')->get();

echo "Sembrando cartas demo...\n";

foreach ($demoCompanies as $config) {
    echo "\n→ {$config['name']} (/carta/{$config['slug']})\n";
    $company = seedDemoCompany($config, $user, $allergens);
    $count = Product::whereIn('section_id', $company->sections()->pluck('id'))->count();
    $reels = Product::whereIn('section_id', $company->sections()->pluck('id'))->whereNotNull('video')->count();
    echo "  Platos: {$count} · Reels: {$reels} · Plantilla: {$company->template}\n";
}

echo "\nCartas demo listas.\n";
echo "  Login: demo@webnu.local / demo123\n";
foreach ($demoCompanies as $config) {
    echo "  {$config['name']} ({$config['template']}): http://127.0.0.1:8000/carta/{$config['slug']}\n";
}

echo "\n--- Auditoría vídeo (demo → plato → clave → archivo) ---\n";
foreach ($demoCompanies as $config) {
    echo "\n[{$config['slug']}]\n";
    foreach ($config['dishes'] as $dish) {
        $key = $dish['video'] ?? null;
        if ($key === null) {
            continue;
        }
        $path = demoVideoPath($key);
        $file = $path ? basename($path) : '?';
        echo "  · {$dish['name']} → {$key} → {$file}\n";
    }
}
