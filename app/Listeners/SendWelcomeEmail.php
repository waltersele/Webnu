<?php

namespace App\Listeners;

use App\Mail\WelcomeMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail
{
    public function handle(Registered $event): void
    {
        try {
            Mail::send(new WelcomeMail($event->user));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
