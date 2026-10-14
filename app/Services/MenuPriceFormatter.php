<?php

namespace App\Services;

/**
 * Formatea precios de carta con símbolo euro UTF-8 seguro.
 * Evita literales € corruptos (encoding) en Blade.
 */
class MenuPriceFormatter
{
    public const EURO = "\u{20AC}";

    public function format($product): ?string
    {
        if (! is_object($product)) {
            return null;
        }

        if (! empty($product->weight_sale) && $this->hasAmount($product->price_unit ?? null)) {
            $label = $this->amount((string) $product->price_unit) . ' ' . self::EURO;
            if (! empty($product->weight_unit_label)) {
                $label .= ' / ' . $product->weight_unit_label;
            }

            return $label;
        }

        if (! empty($product->individual_sale) && $this->hasAmount($product->price_portion ?? null)) {
            return $this->amount((string) $product->price_portion) . ' ' . self::EURO;
        }

        if ($this->hasAmount($product->price_unit ?? null)) {
            return $this->amount((string) $product->price_unit) . ' ' . self::EURO;
        }

        if ($this->hasAmount($product->price_portion ?? null)) {
            return $this->amount((string) $product->price_portion) . ' ' . self::EURO;
        }

        return null;
    }

    public function formatAmount(?string $raw): ?string
    {
        if (! $this->hasAmount($raw)) {
            return null;
        }

        return $this->amount((string) $raw) . ' ' . self::EURO;
    }

    protected function hasAmount($value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return is_numeric(str_replace(',', '.', (string) $value));
    }

    protected function amount(string $raw): string
    {
        $normalized = str_replace(',', '.', trim($raw));

        return number_format((float) $normalized, 2, ',', '.');
    }
}
