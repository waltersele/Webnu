<?php

namespace App\Mail\Subscription;

class SubscriptionCanceledMail extends SubscriptionMail
{
    protected function subjectLine(): string
    {
        return 'Tu suscripción de Webnu ha sido cancelada';
    }

    protected function templateName(): string
    {
        return 'emails.subscriptions.canceled';
    }
}
