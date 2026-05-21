<?php

return [
    'mail' => [
        'mailer' => env('MAIL_MAILER', 'smtp'),
        'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
        'port' => env('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'from_address' => env('MAIL_FROM_ADDRESS', 'info@webnu.es'),
        'from_name' => env('MAIL_FROM_NAME', 'Webnu'),
    ],

    'contact' => [
        'leads_email' => 'hello@webnu.es',
        'suggestions_email' => 'hello@webnu.es',
        'public_email' => 'hello@webnu.es',
    ],

    /*
    | Rutas relativas a public/ — kit de marca Webnu.
    | isotipo: solo símbolo · logo: wordmark completo
    */
    'brand' => [
        'isotipo' => 'adminlte/img/isotipo-color.png',
        'logo' => 'adminlte/img/logo-color.png',
        'favicon' => 'adminlte/img/isotipo-color.png',
        'isotipo_color' => 'adminlte/img/isotipo-color.png',
        'isotipo_white' => 'adminlte/img/isotipo-white.png',
        'isotipo_black' => 'adminlte/img/isotipo-black.png',
        'logo_color' => 'adminlte/img/logo-color.png',
        'logo_white' => 'adminlte/img/logo-white.png',
        'logo_black' => 'adminlte/img/logo-black.png',
    ],

    /*
    | Emails con acceso al panel /admin/platform (además del rol super-admin).
    */
    'super_admin_emails' => array_values(array_filter(array_map('trim', explode(',', (string) env('SUPER_ADMIN_EMAILS', ''))))),

    /*
    | MRR estimado por suscripción Stripe (nombres en config/billing.php).
    | Mensual: planqrmensual · Anual: planqranual
    */
    'mrr' => [
        'monthly_eur' => (float) env('PLATFORM_MRR_MONTHLY_EUR', 9.90),
        'yearly_eur' => (float) env('PLATFORM_MRR_YEARLY_EUR', 99),
    ],

    'stripe_dashboard_customer_url' => env(
        'STRIPE_DASHBOARD_CUSTOMER_URL',
        'https://dashboard.stripe.com/test/customers'
    ),
];
