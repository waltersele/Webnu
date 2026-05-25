<?php

namespace App\Mail\Subscription;

class TrialEndingMail extends SubscriptionMail
{
    protected function subjectLine(): string
    {
        return 'Tu prueba gratis de Webnu termina pronto';
    }

    protected function templateName(): string
    {
        return 'emails.subscriptions.trial-ending';
    }
}
