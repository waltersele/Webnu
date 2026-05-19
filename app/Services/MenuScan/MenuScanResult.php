<?php

namespace App\Services\MenuScan;

class MenuScanResult
{
    /** @var string */
    public $provider;

    /** @var bool */
    public $fallbackUsed = false;

    /** @var array<int, array{name: string, products: array<int, array<string, mixed>>}> */
    public $sections = [];

    /** @var string|null */
    public $errorMessage;

    /** @var string|null Códigos: quota_exceeded, auth_error, etc. */
    public $errorCode;

    /** @var array<int, string> */
    public $warnings = [];

    public static function fromSections(array $sections, string $provider, bool $fallbackUsed = false): self
    {
        $result = new self();
        $result->sections = self::normalizeSections($sections);
        $result->provider = $provider;
        $result->fallbackUsed = $fallbackUsed;

        return $result;
    }

    public static function failed(string $message, string $provider = 'none', ?string $errorCode = null): self
    {
        $result = new self();
        $result->provider = $provider;
        $result->errorMessage = $message;
        $result->errorCode = $errorCode;
        $result->sections = [];

        return $result;
    }

    public function isQuotaExceeded(): bool
    {
        return $this->errorCode === 'quota_exceeded';
    }

    public function isTransientFailure(): bool
    {
        return in_array($this->errorCode, ['server_error', 'quota_exceeded'], true);
    }

    public function isSuccess(): bool
    {
        return $this->errorMessage === null && count($this->sections) > 0;
    }

    public function toParsedMenu(): array
    {
        return ['sections' => $this->sections];
    }

    /**
     * @param mixed $sections
     * @return array<int, array{name: string, products: array<int, array<string, mixed>>}>
     */
    public static function normalizeSections($sections): array
    {
        if (! is_array($sections)) {
            return [];
        }

        $normalized = [];
        foreach ($sections as $section) {
            if (! is_array($section)) {
                continue;
            }
            $name = trim((string) ($section['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $products = [];
            foreach ($section['products'] ?? [] as $product) {
                if (! is_array($product)) {
                    continue;
                }
                $productName = trim((string) ($product['name'] ?? ''));
                if ($productName === '') {
                    continue;
                }
                $priceUnit = self::normalizePrice($product['price_unit'] ?? $product['price'] ?? '');
                $pricePortion = isset($product['price_portion']) && $product['price_portion'] !== ''
                    ? self::normalizePrice($product['price_portion'])
                    : null;
                $rawAllergens = $product['allergens'] ?? [];
                if (is_string($rawAllergens)) {
                    $rawAllergens = preg_split('/\s*,\s*/', $rawAllergens) ?: [];
                }
                $allergens = [];
                foreach ($rawAllergens as $allergenName) {
                    $allergenName = trim((string) $allergenName);
                    if ($allergenName !== '') {
                        $allergens[] = $allergenName;
                    }
                }

                $products[] = [
                    'name' => $productName,
                    'description' => trim((string) ($product['description'] ?? '')) ?: null,
                    'price_unit' => $priceUnit,
                    'price_portion' => $pricePortion,
                    'allergens' => array_values(array_unique($allergens)),
                ];
            }
            if (count($products) > 0) {
                $normalized[] = [
                    'name' => $name,
                    'products' => $products,
                ];
            }
        }

        return $normalized;
    }

    public static function normalizePrice($value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        $value = str_replace(['€', 'EUR', 'eur'], '', $value);
        $value = trim($value);
        $value = preg_replace('/\s+/', '', $value) ?? $value;
        if (preg_match('/^\d+,\d{2}$/', $value)) {
            return $value;
        }
        if (preg_match('/^\d+\.\d{2}$/', $value)) {
            return str_replace('.', ',', $value);
        }
        if (preg_match('/^\d+$/', $value)) {
            return $value . ',00';
        }

        return $value;
    }
}
