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
            $amountCents = $this->resolver->amountCents($key);
            $rows[$key] = array_merge($meta, [
                'key' => $key,
                'price_id' => $priceId,
                'configured' => $priceId !== null && $priceId !== '',
                'amount_cents' => $amountCents,
                'amount_eur' => number_format($amountCents / 100, 2, '.', ''),
                'display_amount' => number_format($amountCents / 100, 2, ',', '.') . ' €',
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
    public function createPrice(string $catalogKey, bool $force = false): array
    {
        $this->ensureStripe();

        $meta = config('billing.price_catalog.' . $catalogKey);
        if (! is_array($meta)) {
            throw new \InvalidArgumentException('Precio desconocido: ' . $catalogKey);
        }

        $existing = $this->resolver->priceId($catalogKey);
        if ($existing && ! $force) {
            return [
                'price_id' => $existing,
                'product_id' => PlatformSetting::getValue($meta['product_setting_key'] ?? '') ?? '',
            ];
        }

        if ($force) {
            $this->resolver->clearPriceId($catalogKey);
        }

        $productId = $this->resolveOrCreateProduct($meta);
        $amountCents = $this->resolver->amountCents($catalogKey);
        if ($amountCents <= 0) {
            throw new \InvalidArgumentException('El importe debe ser mayor que 0.');
        }

        $price = Price::create([
            'product' => $productId,
            'unit_amount' => $amountCents,
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

    public function recreatePrice(string $catalogKey): array
    {
        return $this->createPrice($catalogKey, true);
    }

    public function saveAmountCents(string $catalogKey, int $cents): void
    {
        $this->resolver->saveAmountCents($catalogKey, $cents);
    }

    public function clearStripeCatalog(): void
    {
        $this->resolver->clearStripeCatalog();
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

    /**
     * @param  array<string, mixed>  $meta
     */
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
            throw new \RuntimeException('Configura las claves de Stripe en Plataforma → Configuración → Integraciones.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));
    }
}
