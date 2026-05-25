<?php

namespace App\Console\Commands;

use App\Mail\Subscription\ManualPlanExpiringMail;
use App\Mail\Subscription\TrialEndingMail;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class SendSubscriptionRemindersCommand extends Command
{
    protected $signature = 'webnu:send-subscription-reminders
        {--days=3 : A cuántos días vista del fin enviar el aviso}
        {--dry-run : No envía correos, solo cuenta}';

    protected $description = 'Avisa por email a clientes con trial o plan manual que termina en los próximos N días.';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $sent = 0;
        $sent += $this->remindTrials($days);
        $sent += $this->remindManualPlans($days);

        $this->info("Recordatorios procesados: {$sent}");

        return 0;
    }

    protected function remindTrials(int $days): int
    {
        $rangeStart = now()->startOfDay()->addDays($days);
        $rangeEnd = (clone $rangeStart)->endOfDay();

        $query = User::query()
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [$rangeStart, $rangeEnd])
            ->whereDoesntHave('subscriptions', function ($s) {
                $s->whereIn('stripe_status', ['active', 'trialing'])
                    ->where(function ($s2) {
                        $s2->whereNull('ends_at')->orWhere('ends_at', '>', now());
                    });
            });

        $count = (clone $query)->count();
        if ($this->option('dry-run')) {
            $this->info("Trials a avisar: {$count}");

            return $count;
        }

        $sent = 0;
        $query->orderBy('id')->chunkById(200, function ($users) use (&$sent) {
            foreach ($users as $user) {
                try {
                    Mail::send(new TrialEndingMail($user));
                    $sent++;
                } catch (\Throwable $e) {
                    $this->warn('Trial reminder fallo a ' . $user->email . ': ' . $e->getMessage());
                }
            }
        });

        return $sent;
    }

    protected function remindManualPlans(int $days): int
    {
        if (! Schema::hasColumn('users', 'manual_plan_until')) {
            return 0;
        }

        $rangeStart = now()->startOfDay()->addDays($days);
        $rangeEnd = (clone $rangeStart)->endOfDay();

        $query = User::query()
            ->whereNotNull('manual_plan_until')
            ->whereNotNull('manual_plan_key')
            ->whereBetween('manual_plan_until', [$rangeStart, $rangeEnd]);

        $count = (clone $query)->count();
        if ($this->option('dry-run')) {
            $this->info("Planes manuales a avisar: {$count}");

            return $count;
        }

        $sent = 0;
        $query->orderBy('id')->chunkById(200, function ($users) use (&$sent) {
            foreach ($users as $user) {
                try {
                    Mail::send(new ManualPlanExpiringMail($user));
                    $sent++;
                } catch (\Throwable $e) {
                    $this->warn('Manual plan reminder fallo a ' . $user->email . ': ' . $e->getMessage());
                }
            }
        });

        return $sent;
    }
}
