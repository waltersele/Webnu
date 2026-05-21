<?php

return [

    'api_url' => rtrim(env('TVPIK_API_URL', ''), '/'),

    'app_key' => env('TVPIK_APP_KEY', env('DIGITAL_SIGNAGE_APP_KEY')),

    'web_app_url' => rtrim(env('TVPIK_WEB_URL', 'https://tvpik.es'), '/'),

    'timeout' => (int) env('TVPIK_API_TIMEOUT', 15),

    /*
    | Rutas relativas a api_url (tvpik-api). Ajustar si el contrato cambia.
    */
    'paths' => [
        'screens' => '/integrations/webnu/screens',
        'galleries' => '/integrations/webnu/galleries',
        'publish' => '/integrations/webnu/publish',
        'connect' => '/integrations/webnu/connect',
    ],

    'stub_screens' => env('TVPIK_STUB_SCREENS', false),

    /*
    | Modo reproductor (HDMI / Cast de pestaña): la TV muestra la URL en bucle
    | y consulta sync.json; tú sigues controlando la carta desde el panel Webnu.
    */
    'player_poll_seconds' => (int) env('TVPIK_PLAYER_POLL_SECONDS', 30),

];
