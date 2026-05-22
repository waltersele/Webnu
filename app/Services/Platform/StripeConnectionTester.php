<?php

namespace App\Services\Platform;

use Stripe\Stripe;
use Stripe\StripeClient;

class StripeConnectionTester
{
    /**
     * @return array{ok: bool, message: string}
     */
    public function test(?string $secret = null): array
    {
        $secret = trim($secret ?? (string) config('services.stripe.secret'));
        if ($secret === '') {
            return ['ok' => false, 'message' => 'Indica la clave secreta de Stripe (sk_…).'];
        }

        try {
            Stripe::setApiKey($secret);
            $client = new StripeClient($secret);
            $account = $client->accounts->retrieve();
            $name = $account->settings->dashboard->display_name ?? $account->email ?? $account->id;

            return [
                'ok' => true,
                'message' => 'Conexión con Stripe correcta. Cuenta: ' . ($name ?: $account->id),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Error al conectar con Stripe: ' . $e->getMessage()];
        }
    }
}
