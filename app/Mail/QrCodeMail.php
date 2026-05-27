<?php

namespace App\Mail;

use App\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QrCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public Company $company;
    public int $copies;
    protected string $pdfBinary;
    protected string $pdfFilename;

    public function __construct(Company $company, string $pdfBinary, string $pdfFilename, int $copies = 1)
    {
        $this->company = $company;
        $this->pdfBinary = $pdfBinary;
        $this->pdfFilename = $pdfFilename;
        $this->copies = $copies;
    }

    public function build()
    {
        $subject = 'Tu QR de Webnu — ' . $this->company->name;
        if ($this->copies > 1) {
            $subject .= ' (' . $this->copies . ' por hoja)';
        }

        return $this->subject($subject)
            ->view('emails.qr-code')
            ->with([
                'company' => $this->company,
                'copies' => $this->copies,
                'publicUrl' => $this->company->publicUrl(),
            ])
            ->attachData($this->pdfBinary, $this->pdfFilename, [
                'mime' => 'application/pdf',
            ]);
    }
}
