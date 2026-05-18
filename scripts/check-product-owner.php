<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$p = App\Product::with('section.company')->find(1);
$demo = App\User::where('email', 'demo@webnu.local')->first();

if (!$p) {
    echo "Product 1 not found\n";
    exit(0);
}

echo "product={$p->name}\n";
echo "section_id={$p->section_id}\n";
echo "company_id=" . ($p->section ? $p->section->company_id : 'null') . "\n";
echo "owner_user_id=" . ($p->section && $p->section->company ? $p->section->company->user_id : 'null') . "\n";
echo "demo_user_id=" . ($demo ? $demo->id : 'null') . "\n";
