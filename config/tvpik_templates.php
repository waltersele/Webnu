<?php

return [

    'default' => 'menu',

    'layouts' => ['menu', 'spotlight', 'featured', 'video'],

    'templates' => [
        'menu' => [
            'key' => 'menu',
            'label' => 'Carta completa',
            'description' => 'Secciones y platos con precios, legible en pantalla grande.',
            'layout' => 'menu',
            'view' => 'tv.templates.menu',
            'duration_hint' => 'Rotación automática de secciones',
            'rotate_seconds' => 12,
            'show_header' => true,
            'icon' => 'ti-layout-grid',
            'thumbnail' => 'img/tvpik/previews/menu.svg',
        ],
        'spotlight' => [
            'key' => 'spotlight',
            'label' => 'Plato del día',
            'description' => 'Especial de hoy destacado con precio y platos recomendados.',
            'layout' => 'spotlight',
            'view' => 'tv.templates.spotlight',
            'duration_hint' => 'Ideal para entrada o cartelería principal',
            'rotate_seconds' => 0,
            'show_header' => true,
            'icon' => 'ti-star',
            'thumbnail' => 'img/tvpik/previews/spotlight.svg',
        ],
        'featured' => [
            'key' => 'featured',
            'label' => 'Destacados',
            'description' => 'Carrusel de platos marcados como destacados o con foto.',
            'layout' => 'featured',
            'view' => 'tv.templates.featured',
            'duration_hint' => 'Cambio automático cada pocos segundos',
            'rotate_seconds' => 8,
            'show_header' => true,
            'icon' => 'ti-photo',
            'thumbnail' => 'img/tvpik/previews/featured.svg',
        ],
        'video' => [
            'key' => 'video',
            'label' => 'Vídeos de platos',
            'description' => 'Reels y vídeos cortos de productos con vídeo.',
            'layout' => 'video',
            'view' => 'tv.templates.video',
            'duration_hint' => 'Reproduce en bucle los vídeos disponibles',
            'rotate_seconds' => 15,
            'show_header' => false,
            'icon' => 'ti-player-play',
            'thumbnail' => 'img/tvpik/previews/video.svg',
        ],
    ],

];
