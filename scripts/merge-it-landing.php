<?php

$root = dirname(__DIR__);
$itLines = file($root . '/resources/lang/it/landing.php');
$frLines = file($root . '/resources/lang/fr/landing.php');

$merged = array_merge(array_slice($itLines, 0, 71), array_slice($frLines, 71));
file_put_contents($root . '/resources/lang/it/landing.php', implode('', $merged));

echo "Merged IT header + FR body (" . count($merged) . " lines)\n";
