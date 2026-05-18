<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\User::where('email', 'demo@webnu.local')->first();

if (!$user) {
    echo "No existe demo@webnu.local. Ejecuta: php scripts/seed-local-demo.php\n";
    exit(1);
}

$fixed = App\Company::where('slug', 'demo')->update(['user_id' => $user->id]);
App\Company::where('user_id', '!=', $user->id)->update(['user_id' => $user->id]);

echo "Usuario demo id={$user->id}\n";
foreach (App\Company::all() as $company) {
    echo "  Company id={$company->id} slug={$company->slug} user_id={$company->user_id}\n";
}

echo "Listo. Vuelve a iniciar sesión si seguía el error.\n";
