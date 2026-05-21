<?php

/**
 * Auditoría HTTP de cartas públicas (sin Artisan).
 * Uso: php scripts/audit-menu-urls.php --base=https://webnu.es
 *      php scripts/audit-menu-urls.php --base=http://127.0.0.1:8000 --csv=storage/migration-inventory/companies-....csv
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$argv = $argv ?? [];
$base = 'https://webnu.es';
$csv = null;
$legacy = in_array('--legacy', $argv, true);

foreach ($argv as $i => $arg) {
    if (strpos($arg, '--base=') === 0) {
        $base = substr($arg, 7);
    }
    if (strpos($arg, '--csv=') === 0) {
        $csv = substr($arg, 6);
    }
}

$params = ['--base' => rtrim($base, '/')];
if ($csv) {
    $params['--csv'] = $csv;
}
if ($legacy) {
    $params['--legacy'] = true;
}

$exit = Illuminate\Support\Facades\Artisan::call('webnu:audit-public-menus', $params);
echo Artisan::output();
exit($exit);
