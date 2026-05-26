<?php

/**
 * Clientes de ejemplo para probar el panel plataforma y el bloqueo por suscripción.
 * Uso: php scripts/seed-platform-demo.php
 * Contraseña de todos los usuarios demo: demo123
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

require_once __DIR__ . '/../database/seeders/PlatformRolesSeeder.php';

use App\Company;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$password = Hash::make('demo123');
$monthlyName = config('billing.subscription_names.monthly');
$yearlyName = config('billing.subscription_names.yearly');
$monthlyPrice = config('billing.stripe_prices.monthly');
$yearlyPrice = config('billing.stripe_prices.yearly');

$clients = [
    [
        'email' => 'demo@webnu.local',
        'name' => 'Admin Demo (superadmin)',
        'stripe_id' => 'cus_demo_admin',
        'card_brand' => 'visa',
        'card_last_four' => '4242',
        'company' => ['slug' => 'demo', 'name' => 'La Brasa del Puerto'],
        'subscription' => ['name' => $monthlyName, 'stripe_status' => 'active', 'stripe_id' => 'sub_demo_admin'],
    ],
    [
        'email' => 'maria@webnu.local',
        'name' => 'María García',
        'stripe_id' => 'cus_demo_maria',
        'card_brand' => 'visa',
        'card_last_four' => '1111',
        'company' => ['slug' => 'casa-maria', 'name' => 'Casa María'],
        'subscription' => ['name' => $monthlyName, 'stripe_status' => 'active', 'stripe_id' => 'sub_demo_maria'],
    ],
    [
        'email' => 'jose@webnu.local',
        'name' => 'José Martínez',
        'stripe_id' => 'cus_demo_jose',
        'card_brand' => 'mastercard',
        'card_last_four' => '2222',
        'companies' => [
            ['slug' => 'taberna-jose', 'name' => 'Taberna de José'],
            ['slug' => 'jose-mar', 'name' => 'José Mar (playa)'],
        ],
        'subscription' => ['name' => $yearlyName, 'stripe_status' => 'active', 'stripe_id' => 'sub_demo_jose', 'stripe_plan' => $yearlyPrice],
    ],
    [
        'email' => 'ana@webnu.local',
        'name' => 'Ana López',
        'stripe_id' => 'cus_demo_ana',
        'card_brand' => 'visa',
        'card_last_four' => '3333',
        'trial_ends_at' => now()->addDays(14),
        'company' => ['slug' => 'brunch-ana', 'name' => 'Brunch Ana'],
        'subscription' => ['name' => $monthlyName, 'stripe_status' => 'trialing', 'stripe_id' => 'sub_demo_ana', 'trial_ends_at' => now()->addDays(14)],
    ],
    [
        'email' => 'luis@webnu.local',
        'name' => 'Luis Fernández',
        'stripe_id' => 'cus_demo_luis',
        'card_brand' => 'visa',
        'card_last_four' => '4444',
        'company' => ['slug' => 'meson-luis', 'name' => 'Mesón Luis'],
        'subscription' => ['name' => $monthlyName, 'stripe_status' => 'past_due', 'stripe_id' => 'sub_demo_luis'],
    ],
    [
        'email' => 'carmen@webnu.local',
        'name' => 'Carmen Ruiz',
        'stripe_id' => 'cus_demo_carmen',
        'card_brand' => 'amex',
        'card_last_four' => '5555',
        'company' => ['slug' => 'vinos-carmen', 'name' => 'Vinos Carmen'],
        'subscription' => [
            'name' => $monthlyName,
            'stripe_status' => 'active',
            'stripe_id' => 'sub_demo_carmen',
            'ends_at' => now()->addDays(20),
        ],
    ],
    [
        'email' => 'pablo@webnu.local',
        'name' => 'Pablo Sin Plan',
        'stripe_id' => null,
        'company' => ['slug' => 'pablo-pendiente', 'name' => 'Bar Pablo (sin pagar)'],
        'subscription' => null,
    ],
];

foreach ($clients as $data) {
    $user = User::updateOrCreate(
        ['email' => $data['email']],
        [
            'name' => $data['name'],
            'password' => $password,
            'stripe_id' => $data['stripe_id'] ?? null,
            'card_brand' => $data['card_brand'] ?? null,
            'card_last_four' => $data['card_last_four'] ?? null,
            'trial_ends_at' => $data['trial_ends_at'] ?? null,
        ]
    );

    $companies = $data['companies'] ?? (isset($data['company']) ? [$data['company']] : []);
    foreach ($companies as $c) {
        Company::updateOrCreate(
            ['slug' => $c['slug']],
            [
                'name' => $c['name'],
                'user_id' => $user->id,
                'enabled' => true,
                'menu_type' => 1,
                'template' => 'basic',
                'chef_name' => '',
                'address' => 'Calle Demo 1',
                'postal_code' => '03001',
                'city' => 'Alicante',
                'province' => 'Alicante',
                'country' => 'España',
                'reservation' => 0,
            ]
        );
    }

    DB::table('subscriptions')->where('user_id', $user->id)->delete();

    if (! empty($data['subscription'])) {
        $sub = $data['subscription'];
        DB::table('subscriptions')->insert([
            'user_id' => $user->id,
            'name' => $sub['name'],
            'stripe_id' => $sub['stripe_id'],
            'stripe_status' => $sub['stripe_status'],
            'stripe_plan' => $sub['stripe_plan'] ?? $monthlyPrice,
            'quantity' => 1,
            'trial_ends_at' => $sub['trial_ends_at'] ?? null,
            'ends_at' => $sub['ends_at'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

if (! is_array(config('platform.super_admin_emails')) || count(config('platform.super_admin_emails')) === 0) {
    putenv('SUPER_ADMIN_EMAILS=demo@webnu.local');
    $_ENV['SUPER_ADMIN_EMAILS'] = 'demo@webnu.local';
    config(['platform.super_admin_emails' => ['demo@webnu.local']]);
}

$seeder = new \Database\Seeders\PlatformRolesSeeder();
$seeder->run();

$salesRep = User::updateOrCreate(
    ['email' => 'comercial@webnu.local'],
    [
        'name' => 'Comercial Demo',
        'password' => $password,
        'plan' => 'free',
    ]
);
if (! $salesRep->hasRole('sales-rep')) {
    $salesRep->assignRole('sales-rep');
}
if ($salesRep->hasRole('super-admin')) {
    $salesRep->removeRole('super-admin');
}

echo "\n";
echo "=== Datos de prueba — panel plataforma ===\n\n";
echo "URL:     http://127.0.0.1:8000/admin\n";
echo "Login:   demo@webnu.local / demo123  (superadmin → menú Plataforma)\n\n";
echo "Comercial (portal visitas): comercial@webnu.local / demo123\n";
echo "  Login:     http://127.0.0.1:8000/comercial/login\n";
echo "  Gestión:   http://127.0.0.1:8000/admin/platform/comercial\n\n";
echo "Otros clientes (contraseña demo123 para todos):\n";
echo "  maria@webnu.local   — suscripción ACTIVA (mensual)\n";
echo "  jose@webnu.local    — suscripción ACTIVA (anual), 2 negocios\n";
echo "  ana@webnu.local     — en PRUEBA (14 días)\n";
echo "  luis@webnu.local    — IMPAGO (past_due) → redirige a /admin/billing\n";
echo "  carmen@webnu.local  — cancelación programada (fin en 20 días)\n";
echo "  pablo@webnu.local   — SIN suscripción → solo /admin/billing\n\n";
echo "Plataforma: http://127.0.0.1:8000/admin/platform\n";
echo "Clientes:   http://127.0.0.1:8000/admin/platform/users\n\n";
echo "Ver docs/CREDENCIALES-DEMO-LOCAL.md\n\n";
