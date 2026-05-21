<?php

/**
 * Crea rol super-admin y lo asigna a SUPER_ADMIN_EMAILS.
 * Uso: php scripts/seed-platform-roles.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

require_once __DIR__ . '/../database/seeds/PlatformRolesSeeder.php';

$seeder = new PlatformRolesSeeder();
$seeder->run();

echo "Roles de plataforma listos.\n";
