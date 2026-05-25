<?php

namespace App\Listeners;

use App\Mail\Subscription\PaymentFailedMail;
use App\Mail\Subscription\PaymentSucceededMail;
use App\Mail\Subscription\SubscriptionCanceledMail;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Events\WebhookHandled;

/**
 * Envía emails a clientes cuando Stripe notifica eventos de suscripción.
 * Cashier procesa el webhook primero, nosotros solo despachamos correos.
 */
class StripeWebhookSubscriptionListener
{
    public function handle(WebhookHandled $event): void
    {
        $payload = $event->payload;
        $type = $payload['type'] ?? null;
        if (! is_string($type) || $type === '') {
            return;
        }

        try {
            switch ($type) {
                case 'invoice.payment_failed':
                    $this->onPaymentFailed($payload);
                    break;
                case 'invoice.payment_succeeded':
                    $this->onPaymentSucceeded($payload);
                    break;
                case 'customer.subscription.deleted':
                case 'customer.subscription.updated':
                    $this->onSubscriptionUpdate($payload, $type);
                    break;
            }
        } catch (\Throwable $e) {
            Log::warning('StripeWebhookSubscriptionListener failed', [
                'type' => $type,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function onPaymentFailed(array $payload): void
    {
        $object = $payload['data']['object'] ?? [];
        $stripeCustomerId = $object['customer'] ?? null;
        $user = $this->resolveUser($stripeCustomerId);
        if (! $user) {
            return;
        }

        $context = [
            'invoice_url' => $object['hosted_invoice_url'] ?? null,
            'amount_formatted' => $this->formatAmount($object['amount_due'] ?? null, $object['currency'] ?? null),
        ];

        Mail::send(new PaymentFailedMail($user, $context));
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function onPaymentSucceeded(array $payload): void
    {
        $object = $payload['data']['object'] ?? [];

        if (($object['billing_reason'] ?? '') === 'subscription_create') {
            return;
        }

        $stripeCustomerId = $object['customer'] ?? null;
        $user = $this->resolveUser($stripeCustomerId);
        if (! $user) {
            return;
        }

        $context = [
            'invoice_url' => $object['hosted_invoice_url'] ?? null,
            'amount_formatted' => $this->formatAmount($object['amount_paid'] ?? null, $object['currency'] ?? null),
        ];

        Mail::send(new PaymentSucceededMail($user, $context));
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function onSubscriptionUpdate(array $payload, string $type): void
    {
        $object = $payload['data']['object'] ?? [];
        $status = $object['status'] ?? null;
        $cancelAtPeriodEnd = ! empty($object['cancel_at_period_end']);

        $shouldNotify = $type === 'customer.subscription.deleted'
            || $status === 'canceled'
            || $cancelAtPeriodEnd;

        if (! $shouldNotify) {
            return;
        }

        $stripeCustomerId = $object['customer'] ?? null;
        $user = $this->resolveUser($stripeCustomerId);
        if (! $user) {
            return;
        }

        $endsAt = null;
        if (! empty($object['current_period_end'])) {
            $endsAt = \Illuminate\Support\Carbon::createFromTimestamp((int) $object['current_period_end'])->toIso8601String();
        } elseif (! empty($object['canceled_at'])) {
            $endsAt = \Illuminate\Support\Carbon::createFromTimestamp((int) $object['canceled_at'])->toIso8601String();
        }

        Mail::send(new SubscriptionCanceledMail($user, ['ends_at' => $endsAt]));
    }

    protected function resolveUser(?string $stripeCustomerId): ?User
    {
        if (! is_string($stripeCustomerId) || $stripeCustomerId === '') {
            return null;
        }

        return User::where('stripe_id', $stripeCustomerId)->first();
    }

    protected function formatAmount($amount, ?string $currency): ?string
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        $value = ((int) $amount) / 100;
        $symbol = strtoupper($currency ?? 'EUR') === 'EUR' ? '€' : strtoupper($currency ?? '');

        return number_format($value, 2, ',', '.') . ' ' . $symbol;
    }
}
