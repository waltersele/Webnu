<?php

namespace App\Mail\Lifecycle;

class TrialExpiredMail extends LifecycleMail
{
    protected function subjectLine(): string
    {
        return 'Tu prueba terminó — reactiva Pro en un clic';
    }

    protected function templateName(): string
    {
        return 'emails.lifecycle.trial-expired';
    }
}
