<?php

return [

    /*
    | Zonas con alta densidad turística (provincia o ciudad del negocio).
    */
    'tourism_provinces' => [
        'baleares',
        'illes balears',
        'las palmas',
        'santa cruz de tenerife',
        'malaga',
        'málaga',
        'barcelona',
        'girona',
        'alicante',
        'valencia',
        'cadiz',
        'cádiz',
        'granada',
        'tarragona',
        'murcia',
        'almeria',
        'almería',
        'huelva',
        'pontevedra',
        'a coruña',
        'la coruña',
    ],

    'tourism_cities' => [
        'palma',
        'ibiza',
        'marbella',
        'benidorm',
        'torremolinos',
        'salou',
        'sitges',
        'lloret',
        'santa ponça',
        'magaluf',
        'las palmas',
        'maspalomas',
        'adeje',
        'playa de las americas',
        'sant antoni',
    ],

    'home_country' => 'ES',

    /*
    | :price se sustituye por el price_label del tier indicado en price_tier.
    */
    'copy' => [
        'video' => [
            'price_tier' => 'pro',
            'title' => 'Vídeos en platos',
            'stat' => '+42%',
            'stat_caption' => 'más pedidos que con foto sola',
            'body' => 'El movimiento vende: tus clientes ven el plato real en la carta QR y en pantalla.',
            'perks' => ['Carta digital y Smart TV', 'Compresión automática lista para servir'],
            'cta' => 'Desbloquear vídeos con Pro',
        ],
        'videos' => [
            'price_tier' => 'pro',
            'title' => 'Vídeos en platos',
            'stat' => '+42%',
            'stat_caption' => 'más pedidos que con foto sola',
            'body' => 'El movimiento vende: tus clientes ven el plato real en la carta QR y en pantalla.',
            'perks' => ['Carta digital y Smart TV', 'Compresión automática lista para servir'],
            'cta' => 'Desbloquear vídeos con Pro',
        ],
        'translation' => [
            'price_tier' => 'pro',
            'title' => 'Carta para turistas',
            'stat' => 'IA',
            'stat_caption' => 'traduce tu carta al instante',
            'body' => 'Traducción con IA y selector de idioma en tu carta QR. Sin fricción para quien no habla español.',
            'perks' => ['Inglés y más idiomas', 'Selector visible en la carta'],
            'cta' => 'Activar idiomas con Pro',
        ],
        'translation_banner' => [
            'price_tier' => 'pro',
            'body' => 'Tu zona recibe muchos clientes internacionales. Ofrece la carta en varios idiomas con Pro (:price).',
        ],
        'templates' => [
            'price_tier' => 'pro',
            'title' => 'Más plantillas de carta',
            'body' => 'Cinco diseños en Free; desbloquea temáticas completas para pizza, marisquería, brunch y más.',
            'perks' => ['Estilos listos para publicar', 'Vista previa en vivo'],
            'cta' => 'Ver plantillas con Pro',
        ],
        'menu_scan' => [
            'price_tier' => 'pro',
            'title' => 'Escaneo IA de carta',
            'stat' => 'IA',
            'stat_caption' => 'ordena platos y precios por ti',
            'body' => 'Sube una foto o PDF de tu carta y deja que la IA monte secciones y platos en minutos.',
            'perks' => ['Escaneos ilimitados en Pro', 'Revisión antes de publicar'],
            'cta' => 'Desbloquear escaneo IA con Pro',
        ],
        'product_photos' => [
            'price_tier' => 'pro',
            'title' => 'Fotos en platos',
            'body' => 'Cada plato con imagen en la carta QR: más claridad, menos preguntas al camarero.',
            'perks' => ['Subida desde móvil', 'Optimización automática'],
            'cta' => 'Activar fotos con Pro',
        ],
        'pdf_menu' => [
            'price_tier' => 'pro',
            'title' => 'Carta A4 en PDF',
            'body' => 'Exporta tu carta digital a PDF imprimible, alineada con lo que ven tus clientes online.',
            'perks' => ['Listo para impresión', 'Misma carta que el QR'],
            'cta' => 'Desbloquear PDF con Pro',
        ],
        'tvpik' => [
            'price_tier' => 'plus',
            'title' => 'TVPik — carta en pantallas',
            'body' => 'Lleva tu carta Webnu a las TVs del local: menú y promos siempre actualizados.',
            'perks' => ['1 pantalla en Plus', 'Más pantallas en Pro'],
            'cta' => 'Activar TVPik con Plus',
        ],
    ],

];
