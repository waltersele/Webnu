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

    /** @return array<string, string|null> */
    public function allPriceIds(): array
    {
        $ids = [];
        foreach (array_keys(config('billing.price_catalog', [])) as $key) {
            $ids[$key] = $this->priceId($key);
        }

        return $ids;
    }
}
