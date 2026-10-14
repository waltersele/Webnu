<?php

namespace App\Mail\Lifecycle;

class FeatureNudgeMail extends LifecycleMail
{
    protected function subjectLine(): string
    {
        return (string) ($this->context['subject'] ?? 'Una función de Webnu que aún no has usado');
    }

    protected function templateName(): string
    {
        return 'emails.lifecycle.feature-nudge';
    }
}
