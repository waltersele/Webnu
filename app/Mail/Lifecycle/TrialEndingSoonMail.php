<?php

namespace App\Mail\Lifecycle;

class TrialEndingSoonMail extends LifecycleMail
{
    protected function subjectLine(): string
    {
        $days = (int) ($this->context['days_left'] ?? 7);

        return $days <= 1
            ? 'Tu prueba de Webnu termina mañana'
            : "Tu prueba de Webnu termina en {$days} días";
    }

    protected function templateName(): string
    {
        return 'emails.lifecycle.trial-ending-soon';
    }
}
