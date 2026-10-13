<?php

namespace Tests\Feature;

use App\Mail\Subscription\TrialEndingMail;
use App\Mail\WelcomeMail;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WelcomeAndTrialReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_envia_welcome_mail(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'trial_ends_at' => now()->addDays(30),
            'trial_plan_key' => 'pro',
        ]);

        event(new Registered($user));

        Mail::assertSent(WelcomeMail::class, function (WelcomeMail $mail) use ($user) {
            return $mail->recipient->is($user);
        });
    }

    public function test_subscription_reminders_envia_trial_ending(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'trial_ends_at' => now()->addDays(3)->setTime(12, 0),
            'trial_plan_key' => 'pro',
            'plan' => 'free',
        ]);

        $this->artisan('webnu:send-subscription-reminders', ['--days' => 3])
            ->assertExitCode(0);

        Mail::assertSent(TrialEndingMail::class, function (TrialEndingMail $mail) use ($user) {
            return $mail->recipient->is($user);
        });
    }

    public function test_schedule_incluye_expire_y_reminders(): void
    {
        $events = app()->make(\Illuminate\Console\Scheduling\Schedule::class)->events();
        $commands = collect($events)->map(fn ($e) => $e->command ?? $e->description ?? '')->implode(' ');

        $this->assertStringContainsString('webnu:expire-trials', $commands);
        $this->assertStringContainsString('webnu:send-subscription-reminders', $commands);
    }
}
