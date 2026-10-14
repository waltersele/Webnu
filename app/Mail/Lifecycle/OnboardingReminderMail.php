<?php

namespace App\Mail\Lifecycle;

class OnboardingReminderMail extends LifecycleMail
{
    protected function subjectLine(): string
    {
        return 'Te falta un paso para publicar tu carta';
    }

    protected function templateName(): string
    {
        return 'emails.lifecycle.onboarding-reminder';
    }
}
