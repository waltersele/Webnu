<?php

/**
 * Falla si encuentra caracteres emoji en cartas QR (themes, CSS, seed demo).
 * Uso: php scripts/lint-no-emoji-themes.php
 */

$root = dirname(__DIR__);

$scanRoots = [
    $root . '/resources/views/themes',
    $root . '/public/css/themes',
    $root . '/scripts/seed-local-demo.php',
    $root . '/app/Services/Demo',
];

$emojiPattern = '/[\x{1F300}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{FE00}-\x{FE0F}\x{1F1E6}-\x{1F1FF}]{1,}/u';

$extensions = ['php', 'blade.php', 'css'];

/** @return list<string> */
function lintCollectFiles(string $path, array $extensions): array
{
    if (is_file($path)) {
        return [$path];
    }

    if (! is_dir($path)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }
        $name = $file->getFilename();
        foreach ($extensions as $ext) {
            if ($ext === 'blade.php' && str_ends_with($name, '.blade.php')) {
                $files[] = $file->getPathname();
                break;
            }
            if ($ext !== 'blade.php' && str_ends_with($name, '.' . $ext)) {
                $files[] = $file->getPathname();
                break;
            }
        }
    }

    sort($files);

    return $files;
}

$violations = [];

foreach ($scanRoots as $scanRoot) {
    foreach (lintCollectFiles($scanRoot, $extensions) as $file) {
        $contents = @file_get_contents($file);
        if ($contents === false || $contents === '') {
            continue;
        }

        if (! preg_match_all($emojiPattern, $contents, $matches, PREG_OFFSET_CAPTURE)) {
            continue;
        }

        $seen = [];
        foreach ($matches[0] as [$char, $offset]) {
            $key = $char;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $line = substr_count(substr($contents, 0, $offset), "\n") + 1;
            $relative = str_replace('\\', '/', substr($file, strlen($root) + 1));
            $violations[] = sprintf('%s:%d  emoji %s', $relative, $line, json_encode($char, JSON_UNESCAPED_UNICODE));
        }
    }
}

if ($violations === []) {
    echo "OK — sin emojis en themes, CSS de carta, seed demo y DemoCompanyDataProvider.\n";
    exit(0);
}

echo "ERROR — emojis detectados en carta QR:\n";
foreach ($violations as $violation) {
    echo "  · {$violation}\n";
}
echo "\nRegla: solo iconos SVG lineales, nunca emoji Unicode.\n";
exit(1);
