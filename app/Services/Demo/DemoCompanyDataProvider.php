<?php

namespace App\Services\Demo;

/**
 * Datos curados de las 9 cartas demo. Misma estructura que
 * `scripts/seed-local-demo.php` para producir cartas idénticas en local y prod
 * sin tocar el script local (que descarga imágenes con cURL/HTTPS).
 */
class DemoCompanyDataProvider
{
    /** @return list<array<string, mixed>> */
    public function companies(): array
    {
        return [
            $this->demoRestaurant(),
            $this->demoCocktails(),
            $this->demoFuego(),
            $this->demoJapo(),
            $this->demoFastfood(),
            $this->demoPizza(),
            $this->demoMar(),
            $this->demoElegance(),
            $this->demoAsador(),
        ];
    }

    /** @return array<string, mixed> */
    protected function demoRestaurant(): array
    {
        return [
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
            'dishes' => [
                ['section' => 'Entrantes', 'name' => 'Gazpacho andaluz', 'description' => 'Tomate pera, pepino, pimiento y aceite de oliva virgen extra de la finca.', 'price' => '7.50', 'image' => 'brasa-gazpacho.jpg', 'allergens' => ['Apio', 'Sulfitos'], 'highlight' => 'featured', 'video' => null],
                ['section' => 'Entrantes', 'name' => 'Croquetas de jamón ibérico', 'description' => 'Receta de la casa, bechamel cremosa y jamón 36 meses. Seis unidades.', 'price' => '9.50', 'image' => 'brasa-croquetas.jpg', 'allergens' => ['Gluten', 'Lácteos', 'Huevos'], 'video' => null],
                ['section' => 'Entrantes', 'name' => 'Ensalada de burrata', 'description' => 'Burrata fresca, tomate cherry confitado, pesto de albahaca y reducción balsámica.', 'price' => '12.50', 'image' => 'brasa-burrata.jpg', 'allergens' => ['Lácteos'], 'video' => null],
                ['section' => 'Principales', 'name' => 'Solomillo al Pedro Ximénez', 'description' => 'Solomillo de ternera, reducción de Pedro Ximénez, patata confitada y verduras de temporada.', 'price' => '24.50', 'image' => 'brasa-solomillo.jpg', 'allergens' => ['Sulfitos'], 'highlight' => 'bestseller', 'video' => 'steak'],
                ['section' => 'Principales', 'name' => 'Lubina a la espalda', 'description' => 'Lubina salvaje, refrito de ajos tiernos, guindilla y guarnición de verduras de la huerta.', 'price' => '22.00', 'image' => 'brasa-lubina.jpg', 'allergens' => ['Pescados'], 'video' => 'fish'],
                ['section' => 'Principales', 'name' => 'Arroz meloso de setas', 'description' => 'Arroz bomba, setas de temporada, parmesano reggiano y aceite de trufa.', 'price' => '18.00', 'image' => 'brasa-arroz-setas.jpg', 'allergens' => ['Lácteos', 'Sulfitos'], 'video' => null],
                ['section' => 'Postres', 'name' => 'Tarta de queso', 'description' => 'Estilo vasco, horneada al momento. Coulis de frutos rojos de la huerta.', 'price' => '6.50', 'image' => 'brasa-tarta-queso.jpg', 'allergens' => ['Gluten', 'Lácteos', 'Huevos'], 'highlight' => 'new', 'video' => null],
                ['section' => 'Postres', 'name' => 'Brownie con helado', 'description' => 'Chocolate 70 %, helado artesano de vainilla y crumble de avellanas.', 'price' => '7.00', 'image' => 'brasa-brownie.jpg', 'allergens' => ['Gluten', 'Lácteos', 'Huevos', 'Frutos secos'], 'video' => 'dessert'],
            ],
        ];
    }

    /** @return array<string, mixed> */
    protected function demoCocktails(): array
    {
        return [
            'slug' => 'demo-cocktails',
            'name' => 'Azul Coctelería',
            'chef_name' => 'Marcos Leiva',
            'template' => 'nocturne',
            'comments' => 'Coctelería de autor frente al mar. Copas a ancho completo con reels en cada creación.',
            'background_header' => null,
            'logo' => null,
            'theme_settings' => null,
            'sections' => ['Signature' => 0, 'Clásicos' => 1],
            'dishes' => [
                ['section' => 'Signature', 'name' => 'Negroni del Puerto', 'description' => 'Gin mediterráneo, vermut rojo, bitter de naranja y piel de cítricos flameada.', 'price' => '11.00', 'image' => 'cocktail-negroni.jpg', 'allergens' => ['Sulfitos'], 'highlight' => 'featured', 'video' => 'bar'],
                ['section' => 'Signature', 'name' => 'Margarita de autor', 'description' => 'Tequila reposado, triple sec, lima fresca y sal ahumada en el copo.', 'price' => '10.50', 'image' => 'cocktail-margarita.jpg', 'allergens' => [], 'highlight' => 'featured', 'video' => 'cocktail'],
                ['section' => 'Clásicos', 'name' => 'Mojito de hierbabuena', 'description' => 'Ron blanco, hierbabuena fresca, lima, azúcar de caña y soda.', 'price' => '9.00', 'image' => 'cocktail-mojito.jpg', 'allergens' => ['Sulfitos'], 'highlight' => 'featured', 'video' => 'bar_mix'],
                ['section' => 'Clásicos', 'name' => 'Gin tonic mediterráneo', 'description' => 'Gin botánico, tónica premium, romero y cáscara de pomelo.', 'price' => '9.50', 'image' => 'cocktail-gintonic.jpg', 'allergens' => ['Sulfitos'], 'highlight' => 'featured', 'video' => 'bar_night'],
                ['section' => 'Clásicos', 'name' => 'Old Fashioned', 'description' => 'Bourbon, bitter aromatic, azúcar y twist de naranja. Servido en roca.', 'price' => '11.50', 'image' => 'cocktail-whiskey.jpg', 'allergens' => [], 'highlight' => 'featured', 'video' => 'shake'],
            ],
        ];
    }

    /** @return array<string, mixed> */
    protected function demoFuego(): array
    {
        return [
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
            'dishes' => [
                ['section' => '前菜 · Entrantes', 'name' => 'Gyozas 餃子', 'description' => '🇪🇸 Empanadillas asiáticas a la plancha. Relleno de cerdo o verduras.', 'price' => '3.95', 'image' => 'fuego-gyozas.jpg', 'allergens' => ['Gluten', 'Soja'], 'highlight' => 'featured', 'video' => 'asian_fry'],
                ['section' => '前菜 · Entrantes', 'name' => 'Tori no Karaage 唐揚げ', 'description' => '🇪🇸 Pollo frito japonés. Marinado en jengibre, soja y mirin.', 'price' => '4.95', 'image' => 'fuego-karaage.jpg', 'allergens' => ['Gluten', 'Soja'], 'video' => 'fried_chicken'],
                ['section' => '前菜 · Entrantes', 'name' => 'Yakitori 焼き鳥', 'description' => '🇪🇸 Brochetas a la brasa. Pollo campero o vaca gallega madurada.', 'price' => '4.95', 'image' => 'fuego-yakitori.jpg', 'allergens' => ['Soja'], 'highlight' => 'bestseller', 'video' => 'steak'],
                ['section' => '麺 · Ramen', 'name' => 'Tonkotsu Ramen 豚骨', 'description' => '🇪🇸 Caldo de cerdo estilo Yokohama. Huevo macerado, brotes de bambú y cebollino.', 'price' => '11.95', 'image' => 'fuego-tonkotsu.jpg', 'allergens' => ['Gluten', 'Soja', 'Huevos'], 'highlight' => 'bestseller', 'video' => 'ramen'],
                ['section' => '麺 · Ramen', 'name' => 'Haek Buldak Ramen 🔥', 'description' => '🇪🇸 Ramen seco con salsa picante nuclear. Carne picada, huevo poché y verduras frescas.', 'price' => '11.95', 'image' => 'fuego-buldak.jpg', 'allergens' => ['Gluten', 'Soja', 'Huevos'], 'highlight' => 'featured', 'video' => 'ramen'],
                ['section' => '甘味 · Dulces', 'name' => 'Mochi Matcha 抹茶大福', 'description' => 'Masa gyuhi casera con mousse de matcha ceremonial y corazón de fresa.', 'price' => '5.95', 'image' => 'fuego-mochi.jpg', 'allergens' => ['Lácteos'], 'highlight' => 'new', 'video' => 'dessert'],
            ],
        ];
    }

    /** @return array<string, mixed> */
    protected function demoJapo(): array
    {
        return [
            'slug' => 'demo-japo',
            'name' => 'Sakura House',
            'chef_name' => 'Kyoto · 京都',
            'template' => 'japo',
            'comments' => 'Cocina japonesa clásica: rojo lacado, negro y oro.',
            'background_header' => 'demo/demo-japo-header.jpg',
            'logo' => 'demo/demo-logo.jpg',
            'sections' => ['前菜 · Entradas' => 0, '丼 · Arroz' => 1, '温 · Caliente' => 2, '甘 · Dulce' => 3],
            'dishes' => [
                ['section' => '前菜 · Entradas', 'name' => 'Edamame 枝豆', 'description' => 'Vainas de soja al vapor con sal de mar.', 'price' => '4.50', 'image' => 'fuego-gyozas.jpg', 'allergens' => ['Soja'], 'video' => null],
                ['section' => '前菜 · Entradas', 'name' => 'Sashimi del día 刺身', 'description' => 'Selección de pescado fresco del mercado. Wasabi y jengibre encurtido.', 'price' => '14.50', 'image' => 'brasa-lubina.jpg', 'allergens' => ['Pescados', 'Soja'], 'highlight' => 'featured', 'video' => 'fish'],
                ['section' => '丼 · Arroz', 'name' => 'Gyudon 牛丼', 'description' => 'Bol de arroz con ternera, cebolla y salsa dashi. Huevo poché y shichimi.', 'price' => '8.50', 'image' => 'brasa-arroz-setas.jpg', 'allergens' => ['Gluten', 'Soja', 'Huevos'], 'highlight' => 'bestseller', 'video' => 'pasta_rice'],
                ['section' => '温 · Caliente', 'name' => 'Miso Ramen 味噌', 'description' => 'Caldo tonkotsu con pasta de miso, chashu, repollo chino y huevo macerado.', 'price' => '11.95', 'image' => 'fuego-tonkotsu.jpg', 'allergens' => ['Gluten', 'Soja', 'Huevos'], 'video' => 'ramen'],
                ['section' => '甘 · Dulce', 'name' => 'Mochi de té verde', 'description' => 'Daifuku casero con matcha ceremonial.', 'price' => '5.50', 'image' => 'fuego-mochi.jpg', 'allergens' => ['Lácteos'], 'video' => 'dessert'],
            ],
        ];
    }

    /** @return array<string, mixed> */
    protected function demoFastfood(): array
    {
        return [
            'slug' => 'demo-fastfood',
            'name' => 'Burger & Go',
            'chef_name' => 'Smash · 24h',
            'template' => 'fastfood',
            'comments' => 'Smash burgers, combos y extras. Listo en minutos.',
            'background_header' => 'demo/demo-fastfood-header.jpg',
            'logo' => 'demo/demo-logo.jpg',
            'sections' => ['Combos' => 0, 'Burgers' => 1, 'Extras' => 2, 'Bebidas' => 3],
            'dishes' => [
                ['section' => 'Combos', 'name' => 'Menú Smash', 'description' => 'Smash burger clásico + patatas crujientes + refresco 33 cl.', 'price' => '11.90', 'image' => 'fastfood-combo.jpg', 'allergens' => ['Gluten', 'Lácteos'], 'highlight' => 'bestseller', 'video' => 'burger'],
                ['section' => 'Combos', 'name' => 'Menú Crispy', 'description' => 'Pollo crujiente + patatas + salsa a elegir.', 'price' => '10.90', 'image' => 'fastfood-chicken.jpg', 'allergens' => ['Gluten', 'Huevos'], 'highlight' => 'featured', 'video' => 'fried_chicken'],
                ['section' => 'Burgers', 'name' => 'Double Smash', 'description' => 'Doble carne smash, cheddar fundido, pepinillos y salsa house.', 'price' => '8.90', 'image' => 'fastfood-smash.jpg', 'allergens' => ['Gluten', 'Lácteos'], 'highlight' => 'bestseller', 'video' => 'burger'],
                ['section' => 'Burgers', 'name' => 'BBQ Bacon', 'description' => 'Bacon crujiente, cebolla caramelizada y salsa BBQ ahumada.', 'price' => '9.90', 'image' => 'fastfood-bacon.jpg', 'allergens' => ['Gluten', 'Lácteos'], 'video' => 'burger'],
                ['section' => 'Extras', 'name' => 'Patatas deluxe', 'description' => 'Patatas fritas con piel, sal ahumada y alioli.', 'price' => '3.50', 'image' => 'fastfood-fries.jpg', 'allergens' => ['Huevos'], 'video' => null],
                ['section' => 'Extras', 'name' => 'Nuggets x6', 'description' => 'Pollo crujiente. Elige BBQ, honey mustard o spicy.', 'price' => '5.50', 'image' => 'fastfood-chicken.jpg', 'allergens' => ['Gluten'], 'video' => 'fried_chicken'],
                ['section' => 'Bebidas', 'name' => 'Milkshake vainilla', 'description' => 'Batido cremoso con helado artesano.', 'price' => '4.20', 'image' => 'fastfood-shake.jpg', 'allergens' => ['Lácteos'], 'video' => 'shake'],
            ],
        ];
    }

    /** @return array<string, mixed> */
    protected function demoPizza(): array
    {
        return [
            'slug' => 'demo-pizza',
            'name' => 'Forno Napoli',
            'chef_name' => 'Masa madre · horno de leña',
            'template' => 'pizza',
            'comments' => 'Pizzería napolitana: masa 48 h, tomate italiano y mozzarella di bufala.',
            'background_header' => 'demo/demo-pizza-header.jpg',
            'logo' => 'demo/demo-logo.jpg',
            'sections' => ['Pizzas clásicas' => 0, 'Especiales' => 1, 'Entrantes' => 2, 'Bebidas' => 3],
            'dishes' => [
                ['section' => 'Pizzas clásicas', 'name' => 'Margherita DOP', 'description' => 'Tomate San Marzano, mozzarella fior di latte, albahaca fresca y AOVE.', 'price' => '10.50', 'image' => 'pizza-margherita.jpg', 'allergens' => ['Gluten', 'Lácteos'], 'highlight' => 'bestseller', 'video' => 'pizza'],
                ['section' => 'Pizzas clásicas', 'name' => 'Diavola piccante', 'description' => 'Salami picante, mozzarella y aceite de chile.', 'price' => '12.50', 'image' => 'pizza-diavola.jpg', 'allergens' => ['Gluten', 'Lácteos'], 'highlight' => 'featured', 'video' => 'pizza'],
                ['section' => 'Especiales', 'name' => 'Quattro formaggi', 'description' => 'Mozzarella, gorgonzola, parmesano y fontina.', 'price' => '13.90', 'image' => 'pizza-quattro.jpg', 'allergens' => ['Gluten', 'Lácteos'], 'video' => 'pizza'],
                ['section' => 'Especiales', 'name' => 'Prosciutto e rúcula', 'description' => 'Base blanca, jamón curado, rúcula y parmesano en láminas.', 'price' => '14.50', 'image' => 'pizza-burrata.jpg', 'allergens' => ['Gluten', 'Lácteos'], 'highlight' => 'new', 'video' => null],
                ['section' => 'Entrantes', 'name' => 'Focaccia al rosmarino', 'description' => 'Pan artesano con sal en escamas y romero.', 'price' => '5.50', 'image' => 'brasa-burrata.jpg', 'allergens' => ['Gluten'], 'video' => null],
                ['section' => 'Bebidas', 'name' => 'Limonata casera', 'description' => 'Limón siciliano, menta y agua con gas.', 'price' => '3.50', 'image' => 'limonata-casera.jpg', 'allergens' => [], 'video' => null],
            ],
        ];
    }

    /** @return array<string, mixed> */
    protected function demoMar(): array
    {
        return [
            'slug' => 'demo-mar',
            'name' => 'Marisquería Costa',
            'chef_name' => 'Puerto · Alicante',
            'template' => 'mar',
            'comments' => 'Pescado del día, arroces y brisa mediterránea.',
            'background_header' => 'demo/demo-mar-header.jpg',
            'logo' => 'demo/demo-logo.jpg',
            'sections' => ['Del mar' => 0, 'Para compartir' => 1, 'Postres' => 2],
            'dishes' => [
                ['section' => 'Del mar', 'name' => 'Lubina a la espalda', 'description' => 'Lubina salvaje, refrito de ajos tiernos y guindilla.', 'price' => '22.00', 'image' => 'brasa-lubina.jpg', 'allergens' => ['Pescados'], 'highlight' => 'bestseller', 'video' => 'fish'],
                ['section' => 'Del mar', 'name' => 'Gambas al ajillo', 'description' => 'Gambas rojas de Vinaròs, ajo confitado y guindilla.', 'price' => '16.50', 'image' => 'brasa-lubina.jpg', 'allergens' => ['Crustáceos'], 'highlight' => 'featured', 'video' => 'fish'],
                ['section' => 'Del mar', 'name' => 'Arroz meloso de mar', 'description' => 'Arroz bomba con caldo de pescado, sepia y alioli.', 'price' => '19.00', 'image' => 'brasa-arroz-setas.jpg', 'allergens' => ['Pescados', 'Crustáceos', 'Gluten'], 'video' => 'pasta_rice'],
                ['section' => 'Para compartir', 'name' => 'Gazpacho de tomate', 'description' => 'Tomate pera, pepino y aceite de oliva virgen extra.', 'price' => '7.50', 'image' => 'brasa-gazpacho.jpg', 'allergens' => ['Apio'], 'video' => null],
                ['section' => 'Postres', 'name' => 'Tarta de limón', 'description' => 'Merengue suave y crema de limón de la huerta.', 'price' => '6.50', 'image' => 'brasa-tarta-queso.jpg', 'allergens' => ['Gluten', 'Lácteos', 'Huevos'], 'video' => null],
            ],
        ];
    }

    /** @return array<string, mixed> */
    protected function demoElegance(): array
    {
        return [
            'slug' => 'demo-elegance',
            'name' => 'Le Jardin',
            'chef_name' => 'Chef Élise Martin',
            'template' => 'elegance',
            'comments' => 'Fine dining con espacio, serif y acentos dorados.',
            'background_header' => 'demo/demo-elegance-header.jpg',
            'logo' => 'demo/demo-logo.jpg',
            'sections' => ['Entrantes' => 0, 'Principales' => 1, 'Postres' => 2],
            'dishes' => [
                ['section' => 'Entrantes', 'name' => 'Ensalada de burrata', 'description' => 'Burrata fresca, tomate cherry confitado y pesto de albahaca.', 'price' => '12.50', 'image' => 'brasa-burrata.jpg', 'allergens' => ['Lácteos'], 'highlight' => 'featured', 'video' => null],
                ['section' => 'Entrantes', 'name' => 'Croquetas de jamón', 'description' => 'Bechamel cremosa y jamón ibérico 36 meses.', 'price' => '9.50', 'image' => 'brasa-croquetas.jpg', 'allergens' => ['Gluten', 'Lácteos', 'Huevos'], 'video' => 'asian_fry'],
                ['section' => 'Principales', 'name' => 'Solomillo al Pedro Ximénez', 'description' => 'Solomillo de ternera, reducción de Pedro Ximénez y patata confitada.', 'price' => '24.50', 'image' => 'brasa-solomillo.jpg', 'allergens' => ['Sulfitos'], 'highlight' => 'bestseller', 'video' => 'steak'],
                ['section' => 'Principales', 'name' => 'Lubina salvaje', 'description' => 'Verduras de temporada y emulsión de azafrán.', 'price' => '22.00', 'image' => 'brasa-lubina.jpg', 'allergens' => ['Pescados'], 'video' => 'fish'],
                ['section' => 'Postres', 'name' => 'Tarta de queso', 'description' => 'Estilo vasco, horneada al momento. Coulis de frutos rojos.', 'price' => '6.50', 'image' => 'brasa-tarta-queso.jpg', 'allergens' => ['Gluten', 'Lácteos', 'Huevos'], 'highlight' => 'new', 'video' => null],
            ],
        ];
    }

    /** @return array<string, mixed> */
    protected function demoAsador(): array
    {
        return [
            'slug' => 'demo-asador',
            'name' => 'Brasa & Carbón',
            'chef_name' => 'Asador tradicional',
            'template' => 'asador',
            'comments' => 'Carnes a la brasa, carbón vivo y guarniciones de la huerta.',
            'background_header' => 'demo/demo-asador-header.jpg',
            'logo' => 'demo/demo-logo.jpg',
            'sections' => ['De la brasa' => 0, 'Guarniciones' => 1],
            'dishes' => [
                ['section' => 'De la brasa', 'name' => 'Chuletón madurado', 'description' => 'Ternera gallega 45 días, sal en escamas y chimichurri.', 'price' => '32.00', 'image' => 'brasa-solomillo.jpg', 'allergens' => [], 'highlight' => 'bestseller', 'video' => 'steak'],
                ['section' => 'De la brasa', 'name' => 'Entrecot a la brasa', 'description' => 'Corte grueso, marcado al carbón y mantequilla de hierbas.', 'price' => '26.50', 'image' => 'brasa-solomillo.jpg', 'allergens' => ['Lácteos'], 'highlight' => 'featured', 'video' => 'steak'],
                ['section' => 'De la brasa', 'name' => 'Morcilla de Burgos', 'description' => 'A la plancha con piquillo asado.', 'price' => '8.50', 'image' => 'brasa-croquetas.jpg', 'allergens' => [], 'video' => 'steak'],
                ['section' => 'Guarniciones', 'name' => 'Pimientos de Padrón', 'description' => 'Sal gorda y aceite de oliva.', 'price' => '7.00', 'image' => 'brasa-gazpacho.jpg', 'allergens' => [], 'video' => null],
                ['section' => 'Guarniciones', 'name' => 'Patata confitada', 'description' => 'Patata baby, ajo y romero.', 'price' => '5.50', 'image' => 'brasa-arroz-setas.jpg', 'allergens' => [], 'video' => null],
            ],
        ];
    }
}
