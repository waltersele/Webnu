<?php

namespace App\Services\Platform;

use App\PlatformSetting;

class BillingPriceResolver
{
    public function priceId(string $catalogKey): ?string
    {
        $catalog = config('billing.price_catalog.' . $catalogKey, []);
        if (! empty($catalog['setting_key'])) {
            $fromDb = PlatformSetting::getValue($catalog['setting_key']);
            if ($fromDb !== null && $fromDb !== '') {
                return trim($fromDb);
            }
        }

        $fromConfig = config('billing.stripe_prices.' . $catalogKey);

        return is_string($fromConfig) && $fromConfig !== '' ? $fromConfig : null;
    }

    public function amountCents(string $catalogKey): int
    {
        $catalog = config('billing.price_catalog.' . $catalogKey, []);
        $amountKey = $catalog['amount_setting_key'] ?? ('billing_amount_cents_' . $catalogKey);

        $fromDb = PlatformSetting::getValue($amountKey);
        if ($fromDb !== null && $fromDb !== '') {
            return max(0, (int) $fromDb);
        }

        return (int) ($catalog['amount_cents'] ?? 0);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function amountSettingKey(array $meta, string $catalogKey): string
    {
        return $meta['amount_setting_key'] ?? ('billing_amount_cents_' . $catalogKey);
    }

    public function saveAmountCents(string $catalogKey, int $cents): void
    {
        $meta = config('billing.price_catalog.' . $catalogKey, []);
        if (! is_array($meta)) {
            throw new \InvalidArgumentException('Precio desconocido: ' . $catalogKey);
        }

        PlatformSetting::setValue($this->amountSettingKey($meta, $catalogKey), (string) max(0, $cents));
    }

    public function clearPriceId(string $catalogKey): void
    {
        $meta = config('billing.price_catalog.' . $catalogKey, []);
        if (! empty($meta['setting_key'])) {
            PlatformSetting::where('key', $meta['setting_key'])->delete();
        }
    }

    public function clearProductId(string $productSettingKey): void
    {
        if ($productSettingKey) {
            PlatformSetting::where('key', $productSettingKey)->delete();
        }
    }

    /** @return array<string, string|null> */
    public function allPriceIds(): array
    {
        $ids = [];
        foreach (array_keys(config('billing.price_catalog', [])) as $key) {
            $ids[$key] = $this->priceId($key);
        }

        return $ids;
    }

    /**
     * Borra IDs de precios y productos Stripe guardados (p. ej. al cambiar de cuenta Stripe).
     */
    public function clearStripeCatalog(): void
    {
        $productKeys = [];
        foreach (config('billing.price_catalog', []) as $meta) {
            if (! empty($meta['setting_key'])) {
                PlatformSetting::where('key', $meta['setting_key'])->delete();
            }
            $productKey = $meta['product_setting_key'] ?? null;
            if ($productKey && ! in_array($productKey, $productKeys, true)) {
                $productKeys[] = $productKey;
                PlatformSetting::where('key', $productKey)->delete();
            }
        }
    }
}
