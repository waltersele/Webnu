<?php

namespace App\Mail\Lifecycle;

class QrOnTableMail extends LifecycleMail
{
    protected function subjectLine(): string
    {
        return 'Pon el QR en mesa o barra para recibir visitas';
    }

    protected function templateName(): string
    {
        return 'emails.lifecycle.qr-on-table';
    }
}
