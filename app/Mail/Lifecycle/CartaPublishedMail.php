<?php

namespace App\Mail\Lifecycle;

class CartaPublishedMail extends LifecycleMail
{
    protected function subjectLine(): string
    {
        return '¡Carta lista! Descarga e imprime tu QR';
    }

    protected function templateName(): string
    {
        return 'emails.lifecycle.carta-published';
    }
}
