<?php

namespace App\Mail\Subscription;

class PaymentSucceededMail extends SubscriptionMail
{
    protected function subjectLine(): string
    {
        return 'Recibo de tu suscripción Webnu';
    }

    protected function templateName(): string
    {
        return 'emails.subscriptions.payment-succeeded';
    }
}
