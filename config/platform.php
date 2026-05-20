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
        'leads_email' => 'info@webnu.es',
        'suggestions_email' => 'hola@webnu.es',
        'public_email' => 'hola@webnu.es',
    ],
];
