<?php

return [

    'default' => 'free',

    'trial_days' => 30,
    'trial_tier' => 'plus',

    'tiers' => [
        'free' => [
            'label' => 'Gratis',
            'price_label' => '0 €',
            'max_companies' => 1,
            'menu_scans' => 1,
            'menu_scans_period' => 'lifetime',
            'videos' => false,
            'menu_scan' => true,
            'tvpik' => false,
            'translation' => false,
            'translation_max_locales' => 0,
        ],
        'plus' => [
            'label' => 'Plus',
            'price_label' => '9,90 €/mes',
            'max_companies' => 5,
            'menu_scans' => 10,
            'menu_scans_period' => 'monthly',
            'videos' => true,
            'menu_scan' => true,
            'tvpik' => false,
            'translation' => true,
            'translation_max_locales' => 2,
        ],
        'unlimited' => [
            'label' => 'Ilimitado',
            'price_label' => '29,90 €/mes',
            'max_companies' => null,
            'menu_scans' => null,
            'videos' => true,
            'menu_scan' => true,
            'tvpik' => true,
            'translation' => true,
            'translation_max_locales' => null,
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
