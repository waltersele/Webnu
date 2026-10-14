<?php

namespace App\Mail\Lifecycle;

class SubscriptionStartedMail extends LifecycleMail
{
    protected function subjectLine(): string
    {
        return 'Suscripción activada — gracias por confiar en Webnu';
    }

    protected function templateName(): string
    {
        return 'emails.lifecycle.subscription-started';
    }
}
