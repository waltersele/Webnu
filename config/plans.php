<?php

return [

    'default' => 'free',

    'tiers' => [
        'free' => [
            'label' => 'Gratis',
            'price_label' => '0 €',
            'max_companies' => 1,
            'menu_scans' => 5,
            'videos' => false,
            'menu_scan' => true,
            'tvpik' => false,
        ],
        'plus' => [
            'label' => 'Plus',
            'price_label' => '9,90 €/mes',
            'max_companies' => 5,
            'menu_scans' => null,
            'videos' => true,
            'menu_scan' => true,
            'tvpik' => false,
        ],
        'unlimited' => [
            'label' => 'Ilimitado',
            'price_label' => '29,90 €/mes',
            'max_companies' => null,
            'menu_scans' => null,
            'videos' => true,
            'menu_scan' => true,
            'tvpik' => true,
        ],
    ],

    /*
    | Mapeo de suscripciones Stripe (nombre Cashier) → plan interno.
    | Ajusta cuando tengas los price IDs de Plus e Ilimitado en Stripe.
    */
    'subscription_map' => [
        'planqrmensual' => 'plus',
        'planqranual' => 'plus',
        // 'planqr_plus_mensual' => 'plus',
        // 'planqr_unlimited_mensual' => 'unlimited',
    ],

];
