<?php

namespace App\Console\Commands;

use App\Company;
use App\Services\Lifecycle\LifecycleMailer;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SendLifecycleEmailsCommand extends Command
{
    protected $signature = 'webnu:send-lifecycle-emails {--dry-run : No envía correos, solo cuenta}';

    protected $description = 'Envía emails de activación, trial, win-back y nudges de features (ciclo de conversión).';

    public function handle(LifecycleMailer $mailer): int
    {
        if (! $mailer->tablesReady()) {
            $this->warn('Tabla user_lifecycle_emails no existe. Ejecuta migrate.');

            return 0;
        }

        $sent = 0;
        $sent += $this->activationDrips($mailer);
        $sent += $this->trialReminders($mailer);
        $sent += $this->winBacks($mailer);
        $sent += $this->featureNudges($mailer);

        $this->info("Lifecycle emails enviados: {$sent}");

        return 0;
    }

    protected function activationDrips(LifecycleMailer $mailer): int
    {
        $sent = 0;

        // T+1 onboarding incompleto
        User::query()
            ->whereNull('onboarding_completed_at')
            ->whereBetween('created_at', [now()->subDays(2)->startOfDay(), now()->subDay()->endOfDay()])
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($mailer, &$sent) {
                foreach ($users as $user) {
                    if ($this->option('dry-run')) {
                        $sent++;
                        continue;
                    }
                    if ($mailer->sendOnboardingReminder($user)) {
                        $sent++;
                    }
                }
            });

        // T+2 carta enabled=false
        User::query()
            ->whereNotNull('onboarding_completed_at')
            ->whereBetween('created_at', [now()->subDays(3)->startOfDay(), now()->subDays(2)->endOfDay()])
            ->whereHas('companies', function ($q) {
                $q->where('enabled', false);
            })
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($mailer, &$sent) {
                foreach ($users as $user) {
                    if ($this->option('dry-run')) {
                        $sent++;
                        continue;
                    }
                    if ($mailer->sendCartaInactive($user)) {
                        $sent++;
                    }
                }
            });

        // T+1 post-publish sin vistas
        if (Schema::hasColumn('companies', 'menu_views')) {
            Company::query()
                ->where('enabled', true)
                ->where(function ($q) {
                    $q->whereNull('menu_views')->orWhere('menu_views', 0);
                })
                ->where('updated_at', '<=', now()->subDay())
                ->where('updated_at', '>=', now()->subDays(14))
                ->with('user')
                ->orderBy('id')
                ->chunkById(100, function ($companies) use ($mailer, &$sent) {
                    foreach ($companies as $company) {
                        $user = $company->user;
                        if (! $user) {
                            continue;
                        }
                        if ($this->option('dry-run')) {
                            $sent++;
                            continue;
                        }
                        if ($mailer->sendQrOnTable($user, $company)) {
                            $sent++;
                        }
                    }
                });
        }

        return $sent;
    }

    protected function trialReminders(LifecycleMailer $mailer): int
    {
        $sent = 0;

        foreach ([7, 1] as $days) {
            $rangeStart = now()->startOfDay()->addDays($days);
            $rangeEnd = (clone $rangeStart)->endOfDay();

            User::query()
                ->whereNotNull('trial_ends_at')
                ->whereBetween('trial_ends_at', [$rangeStart, $rangeEnd])
                ->whereDoesntHave('subscriptions', function ($s) {
                    $s->whereIn('stripe_status', ['active', 'trialing'])
                        ->where(function ($s2) {
                            $s2->whereNull('ends_at')->orWhere('ends_at', '>', now());
                        });
                })
                ->orderBy('id')
                ->chunkById(100, function ($users) use ($mailer, $days, &$sent) {
                    foreach ($users as $user) {
                        if ($this->option('dry-run')) {
                            $sent++;
                            continue;
                        }
                        if ($mailer->sendTrialEnding($user, $days)) {
                            $sent++;
                        }
                    }
                });
        }

        // Trials ya expirados (hoy) — email de expiración
        User::query()
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [now()->subDay()->startOfDay(), now()->endOfDay()])
            ->where('plan', 'free')
            ->whereDoesntHave('subscriptions', function ($s) {
                $s->whereIn('stripe_status', ['active', 'trialing']);
            })
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($mailer, &$sent) {
                foreach ($users as $user) {
                    if ($this->option('dry-run')) {
                        $sent++;
                        continue;
                    }
                    if ($mailer->sendTrialExpired($user)) {
                        $sent++;
                    }
                }
            });

        return $sent;
    }

    protected function winBacks(LifecycleMailer $mailer): int
    {
        $sent = 0;

        foreach ([3, 14] as $daysAfter) {
            $day = now()->subDays($daysAfter);

            User::query()
                ->where('plan', 'free')
                ->whereNotNull('trial_ends_at')
                ->whereBetween('trial_ends_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
                ->whereDoesntHave('subscriptions', function ($s) {
                    $s->whereIn('stripe_status', ['active', 'trialing']);
                })
                ->orderBy('id')
                ->chunkById(100, function ($users) use ($mailer, $daysAfter, &$sent) {
                    foreach ($users as $user) {
                        if ($this->option('dry-run')) {
                            $sent++;
                            continue;
                        }
                        if ($mailer->sendWinBack($user, $daysAfter)) {
                            $sent++;
                        }
                    }
                });
        }

        return $sent;
    }

    protected function featureNudges(LifecycleMailer $mailer): int
    {
        $sent = 0;

        User::query()
            ->where(function ($q) {
                $q->whereNotNull('trial_ends_at')->where('trial_ends_at', '>', now())
                    ->orWhereHas('subscriptions', function ($s) {
                        $s->whereIn('stripe_status', ['active', 'trialing'])
                            ->where(function ($s2) {
                                $s2->whereNull('ends_at')->orWhere('ends_at', '>', now());
                            });
                    });
            })
            ->whereHas('companies', function ($q) {
                $q->where('enabled', true);
            })
            ->orderBy('id')
            ->chunkById(50, function ($users) use ($mailer, &$sent) {
                foreach ($users as $user) {
                    if ($this->option('dry-run')) {
                        continue;
                    }
                    if ($mailer->sendFeatureNudgeIfDue($user)) {
                        $sent++;
                    }
                }
            });

        return $sent;
    }
}
