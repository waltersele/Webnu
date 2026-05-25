<?php

/**
 * Relleno sintético en cartas públicas (solo si los flags están en true).
 * En producción: dejar fill_missing_* en false para mostrar solo fotos/vídeos reales del cliente.
 * Vista previa del estudio (?studio_preview=1) puede usar sample_menu sin activar estos flags.
 */
return [
    'fill_missing_images' => false,
    'fill_missing_videos' => false,
    'fill_missing_allergens' => false,

    'sample_images' => [
        'productos/brasa-gazpacho.jpg',
        'productos/brasa-croquetas.jpg',
        'productos/brasa-burrata.jpg',
        'productos/brasa-solomillo.jpg',
        'productos/brasa-lubina.jpg',
        'productos/brasa-arroz-setas.jpg',
        'productos/brasa-tarta-queso.jpg',
        'productos/brasa-brownie.jpg',
    ],

    'sample_videos' => [
        'demo/reel-grill-chicken.mp4',
        'demo/reel-cocktail-ice.mp4',
        'demo/reel-biryani.mp4',
        'demo/reel-kitchen.mp4',
        'demo/reel-tacos.mp4',
        'demo/reel-sandwich.mp4',
    ],

    'video_every_n_products' => 2,

    'sample_menu' => [
        [
            'name' => 'Entrantes',
            'products' => [
                [
                    'name' => 'Ensalada de burrata',
                    'description' => 'Burrata fresca, tomate cherry confitado y pesto de albahaca.',
                    'price_unit' => '12,50 €',
                    'image' => 'productos/brasa-burrata.jpg',
                ],
                [
                    'name' => 'Croquetas de jamón',
                    'description' => 'Cremosas por dentro, crujientes por fuera.',
                    'price_unit' => '9,00 €',
                    'image' => 'productos/brasa-croquetas.jpg',
                ],
            ],
        ],
        [
            'name' => 'Principales',
            'products' => [
                [
                    'name' => 'Solomillo al Pedro Ximénez',
                    'description' => 'Reducción de Pedro Ximénez, patata confitada y verduras de temporada.',
                    'price_unit' => '24,50 €',
                    'image' => 'productos/brasa-solomillo.jpg',
                    'video' => 'demo/reel-grill-chicken.mp4',
                    'highlight' => 'bestseller',
                ],
                [
                    'name' => 'Lubina a la espalda',
                    'description' => 'Lubina salvaje, refrito de ajos tiernos y guindilla.',
                    'price_unit' => '22,00 €',
                    'image' => 'productos/brasa-lubina.jpg',
                ],
            ],
        ],
        [
            'name' => 'Postres',
            'products' => [
                [
                    'name' => 'Tarta de queso',
                    'description' => 'Cremosa, base de galleta y coulis de frutos rojos.',
                    'price_unit' => '7,50 €',
                    'image' => 'productos/brasa-tarta-queso.jpg',
                ],
            ],
        ],
    ],
];
