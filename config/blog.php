<?php

return [

    'default' => 'es',

    'fallback_locale' => 'en',

    'locales' => [
        'es' => ['label' => 'Español', 'hreflang' => 'es'],
        'en' => ['label' => 'English', 'hreflang' => 'en'],
        'fr' => ['label' => 'Français', 'hreflang' => 'fr'],
        'de' => ['label' => 'Deutsch', 'hreflang' => 'de'],
        'it' => ['label' => 'Italiano', 'hreflang' => 'it'],
        'pt' => ['label' => 'Português', 'hreflang' => 'pt'],
        'ca' => ['label' => 'Català', 'hreflang' => 'ca'],
    ],

    'connector' => [
        'secret' => env('CONTENT_CONNECTOR_SECRET'),
        'signature_header' => 'X-Connector-Signature',
        'signature_prefix' => '',
    ],

    'allowed_html_tags' => '<p><br><h1><h2><h3><h4><h5><h6><a><ul><ol><li><strong><em><b><i><img><blockquote><code><pre><span><div><figure><figcaption><hr>',

    /** @var list<string> */
    'default_categories' => [
        'Cartas digitales',
        'Reels y vídeo',
        'Pantallas TV',
        'Fidelización',
        'Operativa y sala',
        'Tendencias',
    ],

    'featured_image' => [
        'max_bytes' => 5 * 1024 * 1024,
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        'storage_dir' => 'img/blog',
    ],

];
