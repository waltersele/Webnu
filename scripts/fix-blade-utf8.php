<?php

/**
 * Corrige textos con codificación rota en vistas Blade del admin.
 * Uso: php scripts/fix-blade-utf8.php
 */

$files = glob(__DIR__ . '/../resources/views/admin/**/*.blade.php') ?: [];
$files = array_merge($files, glob(__DIR__ . '/../resources/views/admin/**/**/*.blade.php') ?: []);
$files = array_unique($files);

$replacements = [
    'secci?n' => 'sección',
    'Secci?n' => 'Sección',
    '?Est?s' => '¿Estás',
    'A?adir' => 'Añadir',
    'A?n' => 'Aún',
    'aqu?' => 'aquí',
    'v?lido' => 'válido',
    'v?deo' => 'vídeo',
    'c?digo' => 'código',
    'num?rico' => 'numérico',
    'seccin' => 'sección',
    'Ests seguro' => '¿Estás seguro',
    'alrgenos' => 'alérgenos',
    'Seleccionar alrgenos' => 'Seleccionar alérgenos',
    'cmo' => 'cómo',
    'ver' => 'verá',
    'pblica' => 'pública',
    'diseo' => 'diseño',
    'Bsica' => 'Básica',
    'muestralo' => 'muéstralo',
    'sustituir' => 'sustituirá',
    'cuadrcula' => 'cuadrícula',
    'est vaca' => 'está vacía',
    'Principales' => 'Principales…',
    'aade' => 'añade',
];

foreach ($files as $path) {
    if (! is_readable($path)) {
        echo "Skip (missing): $path\n";
        continue;
    }
    $content = file_get_contents($path);
    $original = $content;
    foreach ($replacements as $from => $to) {
        $content = str_replace($from, $to, $content);
    }
    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "Fixed: $path\n";
    } else {
        echo "OK: $path\n";
    }
}
