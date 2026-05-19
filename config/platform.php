<?php

return [

    'super_admin_emails' => array_values(array_filter(array_map('trim', explode(',', env('SUPER_ADMIN_EMAILS', ''))))),

    'subscription_names' => config('billing.subscription_names'),

    'grace_days' => (int) env('PLATFORM_GRACE_DAYS', 0),

    'mrr' => [
        'monthly_eur' => 10,
        'yearly_eur' => 100,
    ],

    'stripe_dashboard_customer_url' => 'https://dashboard.stripe.com/customers/',

];
