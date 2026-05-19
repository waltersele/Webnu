<?php

namespace App\Services\MenuScan;

class MenuLineParser
{
    /**
     * @return array<int, array{name: string, products: array<int, array<string, mixed>>}>
     */
    public static function parse(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $sections = [];
        $currentSection = 'Carta';
        $currentProducts = [];

        foreach ($lines as $rawLine) {
            $line = trim($rawLine);
            if ($line === '' || strlen($line) < 2) {
                continue;
            }

            if (self::looksLikeSectionHeader($line)) {
                if (count($currentProducts) > 0) {
                    $sections[] = [
                        'name' => $currentSection,
                        'products' => $currentProducts,
                    ];
                    $currentProducts = [];
                }
                $currentSection = $line;
                continue;
            }

            $parsed = self::parseProductLine($line);
            if ($parsed !== null) {
                $currentProducts[] = $parsed;
            }
        }

        if (count($currentProducts) > 0) {
            $sections[] = [
                'name' => $currentSection,
                'products' => $currentProducts,
            ];
        }

        return MenuScanResult::normalizeSections($sections);
    }

    protected static function looksLikeSectionHeader(string $line): bool
    {
        if (preg_match('/\d+[,.]\d{2}/', $line)) {
            return false;
        }
        if (strlen($line) > 60) {
            return false;
        }
        if (preg_match('/^[A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑ\s\-&]{2,}$/u', $line)) {
            return true;
        }

        return false;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function parseProductLine(string $line)
    {
        if (! preg_match('/(\d+[,.]\d{2})\s*€?|\b(\d+[,.]\d{2})\s*$/u', $line, $m)) {
            return null;
        }
        $priceRaw = $m[1] ?? $m[2] ?? '';
        $name = trim(preg_replace('/\s*\d+[,.]\d{2}\s*€?\s*$/u', '', $line) ?? $line);
        if ($name === '' || strlen($name) < 2) {
            return null;
        }

        return [
            'name' => $name,
            'description' => null,
            'price_unit' => MenuScanResult::normalizePrice($priceRaw),
            'price_portion' => null,
        ];
    }
}
