<?php

return [

    'default' => 'free',

    'trial_days' => 30,
    'trial_tier' => 'pro',

    /*
    | Valores legacy en users.plan / trial_plan_key → tier actual.
    */
    'tier_aliases' => [
        'plus' => 'pro',
        'unlimited' => 'plus',
    ],

    'tiers' => [
        'free' => [
            'label' => 'Free',
            'price_label' => '0 €',
            'price_cents' => 0,
            'max_companies' => 2,
            'max_products_per_company' => 30,
            'menu_scans' => 1,
            'menu_scans_period' => 'lifetime',
            'menu_scan' => true,
            'product_photos' => false,
            'videos' => false,
            'translation' => false,
            'translation_max_locales' => 0,
            'pdf_menu' => false,
            'show_webnu_badge' => true,
            'tvpik' => false,
            'tvpik_screens_included' => 0,
            'whatsapp_support' => false,
            'priority_support' => false,
            'contact_only' => false,
        ],
        'pro' => [
            'label' => 'Pro',
            'price_label' => '9,90 €/mes',
            'price_cents' => 990,
            'max_companies' => 5,
            'max_products_per_company' => null,
            'menu_scans' => null,
            'menu_scans_period' => null,
            'menu_scan' => true,
            'product_photos' => true,
            'videos' => true,
            'translation' => true,
            'translation_max_locales' => 3,
            'pdf_menu' => true,
            'show_webnu_badge' => false,
            'tvpik' => false,
            'tvpik_screens_included' => 0,
            'whatsapp_support' => true,
            'priority_support' => false,
            'contact_only' => false,
            'required_for' => [
                'videos' => true,
                'translation' => true,
                'product_photos' => true,
                'pdf_menu' => true,
                'multi_company' => true,
            ],
        ],
        'plus' => [
            'label' => 'Plus',
            'price_label' => '19,90 €/mes',
            'price_cents' => 1990,
            'max_companies' => null,
            'max_products_per_company' => null,
            'menu_scans' => null,
            'menu_scans_period' => null,
            'menu_scan' => true,
            'product_photos' => true,
            'videos' => true,
            'translation' => true,
            'translation_max_locales' => null,
            'pdf_menu' => true,
            'show_webnu_badge' => false,
            'tvpik' => true,
            'tvpik_screens_included' => 1,
            'whatsapp_support' => true,
            'priority_support' => true,
            'contact_only' => false,
            'required_for' => [
                'tvpik' => true,
            ],
        ],
        'franchise' => [
            'label' => 'Franquicias',
            'price_label' => 'A medida',
            'price_cents' => null,
            'max_companies' => null,
            'max_products_per_company' => null,
            'menu_scans' => null,
            'menu_scans_period' => null,
            'menu_scan' => true,
            'product_photos' => true,
            'videos' => true,
            'translation' => true,
            'translation_max_locales' => null,
            'pdf_menu' => true,
            'show_webnu_badge' => false,
            'tvpik' => true,
            'tvpik_screens_included' => null,
            'whatsapp_support' => true,
            'priority_support' => true,
            'contact_only' => true,
        ],
    ],

    'tvpik_addons' => [
        'screen_1' => [
            'label' => '1 pantalla TVPik',
            'price_label' => '5,00 €/mes',
            'price_cents' => 500,
            'screens' => 1,
        ],
        'pack_5' => [
            'label' => 'Pack 5 pantallas TVPik',
            'price_label' => '20,00 €/mes',
            'price_cents' => 2000,
            'screens' => 5,
        ],
    ],

    /*
    | Mapeo de suscripciones Stripe (nombre Cashier) → plan interno.
    */
    'subscription_map' => [
        'planqrmensual' => 'pro',
        'planqranual' => 'pro',
        'planqr_pro_mensual' => 'pro',
        'planqr_pro_anual' => 'pro',
        'planqr_plus_mensual' => 'plus',
        'planqr_plus_anual' => 'plus',
    ],

    'subscription_addon_map' => [
        'planqr_tvpik_1' => 'screen_1',
        'planqr_tvpik_pack5' => 'pack_5',
    ],

];
