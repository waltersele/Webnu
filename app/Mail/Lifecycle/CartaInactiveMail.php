<?php

namespace App\Mail\Lifecycle;

class CartaInactiveMail extends LifecycleMail
{
    protected function subjectLine(): string
    {
        return 'Tu carta aún no está visible al escanear el QR';
    }

    protected function templateName(): string
    {
        return 'emails.lifecycle.carta-inactive';
    }
}
