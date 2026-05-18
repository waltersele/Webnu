<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Clave de la app de digital signage
    |--------------------------------------------------------------------------
    |
    | Si defines DIGITAL_SIGNAGE_APP_KEY, todas las peticiones a /api/signage/*
    | deben enviar la cabecera X-Digital-Signage-Key con ese valor.
    | Déjalo vacío para no exigirla (solo token de usuario).
    |
    */
    'app_key' => env('DIGITAL_SIGNAGE_APP_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Sincronización
    |--------------------------------------------------------------------------
    */
    'only_enabled' => env('DIGITAL_SIGNAGE_ONLY_ENABLED', true),

    'api_version' => '1.0',

];
