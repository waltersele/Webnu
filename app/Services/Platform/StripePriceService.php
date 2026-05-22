<?php

namespace App\Services\Platform;

use App\PlatformSetting;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;

class StripePriceService
{
    protected BillingPriceResolver $resolver;

    public function __construct(BillingPriceResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function catalogStatus(): array
    {
        $rows = [];
        foreach (config('billing.price_catalog', []) as $key => $meta) {
            $priceId = $this->resolver->priceId($key);
            $rows[$key] = array_merge($meta, [
                'key' => $key,
                'price_id' => $priceId,
                'configured' => $priceId !== null && $priceId !== '',
                'display_amount' => number_format(($meta['amount_cents'] ?? 0) / 100, 2, ',', '.') . ' €',
                'subscription_name' => config('billing.subscription_names.' . $key),
            ]);
        }

        return $rows;
    }

    public function stripeConfigured(): bool
    {
        $secret = config('services.stripe.secret');

        return is_string($secret) && $secret !== '';
    }

    /**
     * @return array{price_id: string, product_id: string}
     */
    public function createPrice(string $catalogKey): array
    {
        $this->ensureStripe();

        $meta = config('billing.price_catalog.' . $catalogKey);
        if (! is_array($meta)) {
            throw new \InvalidArgumentException('Precio desconocido: ' . $catalogKey);
        }

        $existing = $this->resolver->priceId($catalogKey);
        if ($existing) {
            return ['price_id' => $existing, 'product_id' => PlatformSetting::getValue($meta['product_setting_key'] ?? '') ?? ''];
        }

        $productId = $this->resolveOrCreateProduct($meta);
        $price = Price::create([
            'product' => $productId,
            'unit_amount' => (int) $meta['amount_cents'],
            'currency' => 'eur',
            'recurring' => [
                'interval' => $meta['interval'] ?? 'month',
            ],
            'metadata' => [
                'webnu_catalog_key' => $catalogKey,
            ],
        ]);

        PlatformSetting::setValue($meta['setting_key'], $price->id);

        return [
            'price_id' => $price->id,
            'product_id' => $productId,
        ];
    }

    /**
     * @return array<string, array{ok: bool, price_id?: string, error?: string}>
     */
    public function createAllMissing(): array
    {
        $results = [];
        foreach (array_keys(config('billing.price_catalog', [])) as $key) {
            if ($this->resolver->priceId($key)) {
                $results[$key] = ['ok' => true, 'price_id' => $this->resolver->priceId($key), 'skipped' => true];

                continue;
            }

            try {
                $created = $this->createPrice($key);
                $results[$key] = ['ok' => true, 'price_id' => $created['price_id']];
            } catch (\Throwable $e) {
                $results[$key] = ['ok' => false, 'error' => $e->getMessage()];
            }
        }

        return $results;
    }

    public function savePriceId(string $catalogKey, string $priceId): void
    {
        $meta = config('billing.price_catalog.' . $catalogKey);
        if (! is_array($meta) || empty($meta['setting_key'])) {
            throw new \InvalidArgumentException('Precio desconocido: ' . $catalogKey);
        }

        $priceId = trim($priceId);
        if ($priceId === '' || ! preg_match('/^price_/', $priceId)) {
            throw new \InvalidArgumentException('ID de precio Stripe no válido.');
        }

        PlatformSetting::setValue($meta['setting_key'], $priceId);
    }

    protected function resolveOrCreateProduct(array $meta): string
    {
        $productSettingKey = $meta['product_setting_key'] ?? null;
        if ($productSettingKey) {
            $stored = PlatformSetting::getValue($productSettingKey);
            if ($stored) {
                return $stored;
            }
        }

        $product = Product::create([
            'name' => $meta['product_name'] ?? 'Webnu',
            'metadata' => [
                'webnu_product' => $productSettingKey ?? 'webnu',
            ],
        ]);

        if ($productSettingKey) {
            PlatformSetting::setValue($productSettingKey, $product->id);
        }

        return $product->id;
    }

    protected function ensureStripe(): void
    {
        if (! $this->stripeConfigured()) {
            throw new \RuntimeException('Configura STRIPE_SECRET en .env para crear precios.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));
    }
}
