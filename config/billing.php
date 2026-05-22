<?php

return [

    /*
    | Precios Stripe (sin IVA en producto). Crear en Dashboard y copiar IDs al .env.
    */
    'stripe_prices' => [
        'pro_monthly' => env('STRIPE_PRICE_PRO_MONTHLY', env('STRIPE_PRICE_MONTHLY', 'price_1Gt02YHiccjFLKWy8NvH4JCU')),
        'pro_yearly' => env('STRIPE_PRICE_PRO_YEARLY', env('STRIPE_PRICE_YEARLY', 'price_1GstoTHiccjFLKWy98LcWWqu')),
        'plus_monthly' => env('STRIPE_PRICE_PLUS_MONTHLY', ''),
        'plus_yearly' => env('STRIPE_PRICE_PLUS_YEARLY', ''),
        'tvpik_screen_1' => env('STRIPE_PRICE_TVPIK_1', ''),
        'tvpik_pack_5' => env('STRIPE_PRICE_TVPIK_PACK5', ''),
    ],

    'subscription_names' => [
        'pro_monthly' => 'planqr_pro_mensual',
        'pro_yearly' => 'planqr_pro_anual',
        'plus_monthly' => 'planqr_plus_mensual',
        'plus_yearly' => 'planqr_plus_anual',
        'tvpik_screen_1' => 'planqr_tvpik_1',
        'tvpik_pack_5' => 'planqr_tvpik_pack5',
        // Legacy (mapean a pro en config/plans.php)
        'monthly' => 'planqrmensual',
        'yearly' => 'planqranual',
    ],

    'display' => [
        'pro_monthly' => '9,90 €/mes · sin IVA',
        'pro_yearly' => '99 €/año · sin IVA',
        'plus_monthly' => '19,90 €/mes · sin IVA',
        'plus_yearly' => '199 €/año · sin IVA',
        'tvpik_screen_1' => '5,00 €/mes · sin IVA',
        'tvpik_pack_5' => '20,00 €/mes · sin IVA',
    ],

    /*
    | Catálogo para crear precios en Stripe desde /admin/platform/billing.
    | setting_key → platform_settings; si está vacío, se usa stripe_prices (env).
    */
    'price_catalog' => [
        'pro_monthly' => [
            'label' => 'Pro · mensual',
            'product_name' => 'Webnu Pro',
            'product_setting_key' => 'stripe_product_pro',
            'amount_cents' => 990,
            'interval' => 'month',
            'setting_key' => 'stripe_price_pro_monthly',
        ],
        'pro_yearly' => [
            'label' => 'Pro · anual',
            'product_name' => 'Webnu Pro',
            'product_setting_key' => 'stripe_product_pro',
            'amount_cents' => 9900,
            'interval' => 'year',
            'setting_key' => 'stripe_price_pro_yearly',
        ],
        'plus_monthly' => [
            'label' => 'Plus · mensual',
            'product_name' => 'Webnu Plus',
            'product_setting_key' => 'stripe_product_plus',
            'amount_cents' => 1990,
            'interval' => 'month',
            'setting_key' => 'stripe_price_plus_monthly',
        ],
        'plus_yearly' => [
            'label' => 'Plus · anual',
            'product_name' => 'Webnu Plus',
            'product_setting_key' => 'stripe_product_plus',
            'amount_cents' => 19900,
            'interval' => 'year',
            'setting_key' => 'stripe_price_plus_yearly',
        ],
        'tvpik_screen_1' => [
            'label' => 'TVPik · 1 pantalla',
            'product_name' => 'Webnu TVPik',
            'product_setting_key' => 'stripe_product_tvpik',
            'amount_cents' => 500,
            'interval' => 'month',
            'setting_key' => 'stripe_price_tvpik_screen_1',
        ],
        'tvpik_pack_5' => [
            'label' => 'TVPik · pack 5 pantallas',
            'product_name' => 'Webnu TVPik',
            'product_setting_key' => 'stripe_product_tvpik',
            'amount_cents' => 2000,
            'interval' => 'month',
            'setting_key' => 'stripe_price_tvpik_pack_5',
        ],
    ],

];
