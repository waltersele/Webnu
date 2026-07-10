<?php

return [

    'default' => 'es',

    'fallback_locale' => 'en',

    'locales' => [
        'es' => ['label' => 'Español', 'hreflang' => 'es'],
        'en' => ['label' => 'English', 'hreflang' => 'en'],
        'fr' => ['label' => 'Français', 'hreflang' => 'fr'],
    ],

    'connector' => [
        'secret' => env('CONTENT_CONNECTOR_SECRET'),
        'signature_header' => 'X-Connector-Signature',
        // Legacy: el middleware acepta hex crudo (Sonartop) o prefijo sha256=
        'signature_prefix' => '',
    ],

    'allowed_html_tags' => '<p><br><h1><h2><h3><h4><h5><h6><a><ul><ol><li><strong><em><b><i><img><blockquote><code><pre><span><div><figure><figcaption><hr>',

];
