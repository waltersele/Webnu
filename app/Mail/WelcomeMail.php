<?php

namespace App\Mail;

use App\Mail\Subscription\SubscriptionMail;

class WelcomeMail extends SubscriptionMail
{
    protected function subjectLine(): string
    {
        $days = (int) config('plans.trial_days', 30);

        return "Bienvenido a Webnu — {$days} días de Pro gratis";
    }

    protected function templateName(): string
    {
        return 'emails.welcome';
    }
}
