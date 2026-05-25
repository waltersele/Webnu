<?php

namespace App\Mail\Subscription;

class PaymentFailedMail extends SubscriptionMail
{
    protected function subjectLine(): string
    {
        return 'No hemos podido cobrar tu suscripción de Webnu';
    }

    protected function templateName(): string
    {
        return 'emails.subscriptions.payment-failed';
    }
}
