<?php

namespace App\Mail\Subscription;

class ManualPlanExpiringMail extends SubscriptionMail
{
    protected function subjectLine(): string
    {
        return 'Tu plan de cortesía Webnu termina pronto';
    }

    protected function templateName(): string
    {
        return 'emails.subscriptions.manual-plan-expiring';
    }
}
