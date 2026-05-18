<?php

return [

    'stripe_prices' => [
        'monthly' => env('STRIPE_PRICE_MONTHLY', 'price_1Gt02YHiccjFLKWy8NvH4JCU'),
        'yearly' => env('STRIPE_PRICE_YEARLY', 'price_1GstoTHiccjFLKWy98LcWWqu'),
    ],

    'subscription_names' => [
        'monthly' => 'planqrmensual',
        'yearly' => 'planqranual',
    ],

];
