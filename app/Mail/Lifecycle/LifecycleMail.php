<?php

namespace App\Mail\Lifecycle;

use App\PlatformSetting;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

abstract class LifecycleMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var User */
    public $recipient;

    /** @var array<string, mixed> */
    public $context;

    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(User $recipient, array $context = [])
    {
        $this->recipient = $recipient;
        $this->context = $context;
    }

    abstract protected function subjectLine(): string;

    abstract protected function templateName(): string;

    public function build()
    {
        $fromAddress = PlatformSetting::mailFromAddress();
        $fromName = PlatformSetting::mailFromName();

        return $this
            ->from($fromAddress, $fromName)
            ->to($this->recipient->email, $this->recipient->name ?: $this->recipient->email)
            ->subject($this->subjectLine())
            ->view($this->templateName())
            ->with([
                'recipient' => $this->recipient,
                'logoUrl' => PlatformSetting::brandUrl('logo'),
                'panelUrl' => url('/admin'),
                'billingUrl' => url('/admin/billing'),
                'supportEmail' => PlatformSetting::contactPublicEmail(),
                'context' => $this->context,
                'ctaUrl' => $this->context['cta_url'] ?? url('/admin'),
                'ctaLabel' => $this->context['cta_label'] ?? 'Ir al panel',
            ]);
    }
}
