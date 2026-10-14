<?php

namespace App\Mail\Lifecycle;

class WelcomeMail extends LifecycleMail
{
    protected function subjectLine(): string
    {
        return 'Bienvenido a Webnu — completa tu carta en 10 minutos';
    }

    protected function templateName(): string
    {
        return 'emails.lifecycle.welcome';
    }
}
