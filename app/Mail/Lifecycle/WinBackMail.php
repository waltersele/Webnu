<?php

namespace App\Mail\Lifecycle;

class WinBackMail extends LifecycleMail
{
    protected function subjectLine(): string
    {
        return '¿Volvemos? Tu carta digital te espera';
    }

    protected function templateName(): string
    {
        return 'emails.lifecycle.win-back';
    }
}
