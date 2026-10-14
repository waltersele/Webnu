<?php

return [

    'default' => 'es',

    'fallback_locale' => 'en',

    'cookie_name' => 'webnu_landing_lang',

    'locales' => [
        'es' => ['label' => 'Español', 'native' => 'Español', 'hreflang' => 'es', 'flag' => 'es'],
        'en' => ['label' => 'English', 'native' => 'English', 'hreflang' => 'en', 'flag' => 'gb'],
        'fr' => ['label' => 'Français', 'native' => 'Français', 'hreflang' => 'fr', 'flag' => 'fr'],
        'de' => ['label' => 'Deutsch', 'native' => 'Deutsch', 'hreflang' => 'de', 'flag' => 'de'],
        'it' => ['label' => 'Italiano', 'native' => 'Italiano', 'hreflang' => 'it', 'flag' => 'it'],
        'ca' => ['label' => 'Català', 'native' => 'Català', 'hreflang' => 'ca', 'flag' => 'es-ct'],
    ],

    'template_showcase_keys' => ['pasion', 'lumiere', 'nocturne'],

    'pricing_order' => ['free', 'pro', 'plus', 'franchise'],

    'pricing_highlight' => 'plus',

    'franchise_contact_email' => env('WEBNU_FRANCHISE_EMAIL', 'hola@webnu.es'),

    'template_demo_urls' => [
        'pasion' => '/carta/demo',
        'lumiere' => '/carta/demo?tpl=lumiere',
        'nocturne' => '/carta/demo-cocktails',
        'ambre' => '/carta/demo?tpl=ambre',
        'swipe' => '/carta/demo?tpl=swipe',
        'rail' => '/carta/demo?tpl=rail',
        'grid' => '/carta/demo?tpl=grid',
        'deck' => '/carta/demo?tpl=deck',
        'harbor' => '/carta/demo?tpl=harbor',
        'temporada' => '/carta/demo?tpl=temporada',
        'catalogo' => '/carta/demo?tpl=catalogo',
        'mar' => '/carta/demo-mar',
        'otaku' => '/carta/demo-fuego',
        'japo' => '/carta/demo-japo',
        'fastfood' => '/carta/demo-fastfood',
        'pizza' => '/carta/demo-pizza',
        'elegance' => '/carta/demo-elegance',
        'asador' => '/carta/demo-asador',
        'saffron' => '/carta/demo?tpl=saffron',
        'maison' => '/carta/demo-maison',
    ],

];
