<?php

/**
 * Migra users.plan legacy: plus → pro, unlimited → plus.
 * Uso: php scripts/migrate-plan-keys.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$toPro = DB::table('users')->where('plan', 'plus')->update(['plan' => 'pro']);
$toPlus = DB::table('users')->where('plan', 'unlimited')->update(['plan' => 'plus']);
$trialPro = DB::table('users')->where('trial_plan_key', 'plus')->update(['trial_plan_key' => 'pro']);
$trialPlus = DB::table('users')->where('trial_plan_key', 'unlimited')->update(['trial_plan_key' => 'plus']);

echo "users.plan plus → pro: {$toPro}\n";
echo "users.plan unlimited → plus: {$toPlus}\n";
echo "users.trial_plan_key plus → pro: {$trialPro}\n";
echo "users.trial_plan_key unlimited → plus: {$trialPlus}\n";
echo "Done.\n";
