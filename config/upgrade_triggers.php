<?php

return [

    /*
    | Zonas con alta densidad turística (provincia o ciudad del negocio).
    | Comparación sin acentos, insensible a mayúsculas.
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

    /*
    | País del admin (cabeceras CDN / proxy). Si no es ES, sugerimos idiomas.
    */
    'home_country' => 'ES',

    'copy' => [
        'video' => [
            'title' => 'Vídeos en platos',
            'body' => 'Un plato con vídeo vende hasta un 42% más que uno con foto. Desbloquea los vídeos de comida en el plan Plus por :price.',
            'cta' => 'Desbloquear vídeos con Plus',
        ],
        'translation' => [
            'title' => 'Carta para turistas',
            'body' => 'En zonas turísticas, muchos comensales no leen el menú en español. Activa inglés y más idiomas en tu carta QR (traducción IA + selector) con el plan Plus por :price.',
            'cta' => 'Activar idiomas con Plus',
        ],
        'translation_banner' => 'Tu zona recibe muchos clientes internacionales. Ofrece la carta en varios idiomas con Plus (:price).',
    ],

];
