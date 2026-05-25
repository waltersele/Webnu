<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ExpireTrialsCommand extends Command
{
    protected $signature = 'webnu:expire-trials {--dry-run : Solo muestra cuántos usuarios se actualizarían}';

    protected $description = 'Transiciona trials caducados al plan free y limpia planes manuales (cortesía) caducados.';

    public function handle(): int
    {
        $trialsCount = $this->expireTrials();
        $manualCount = $this->expireManualPlans();

        $this->info("Trials expirados procesados: {$trialsCount}");
        $this->info("Planes manuales caducados procesados: {$manualCount}");

        return 0;
    }

    protected function expireTrials(): int
    {
        $query = User::query()
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->whereDoesntHave('subscriptions', function ($s) {
                $s->whereIn('stripe_status', ['active', 'trialing'])
                    ->where(function ($s2) {
                        $s2->whereNull('ends_at')->orWhere('ends_at', '>', now());
                    });
            })
            ->where(function ($q) {
                $q->where('plan', '!=', 'free')
                    ->orWhereNotNull('trial_plan_key');
            });

        $count = (clone $query)->count();

        if ($this->option('dry-run')) {
            return $count;
        }

        $updated = 0;
        $query->orderBy('id')->chunkById(500, function ($users) use (&$updated) {
            $ids = $users->pluck('id')->all();
            $updated += User::whereIn('id', $ids)->update([
                'plan' => 'free',
                'trial_plan_key' => null,
            ]);
        });

        return $updated;
    }

    protected function expireManualPlans(): int
    {
        if (! Schema::hasColumn('users', 'manual_plan_until') || ! Schema::hasColumn('users', 'manual_plan_key')) {
            return 0;
        }

        $query = User::query()
            ->whereNotNull('manual_plan_until')
            ->where('manual_plan_until', '<', now())
            ->whereNotNull('manual_plan_key');

        $count = (clone $query)->count();

        if ($this->option('dry-run')) {
            return $count;
        }

        $updated = 0;
        $query->orderBy('id')->chunkById(500, function ($users) use (&$updated) {
            $ids = $users->pluck('id')->all();
            $updated += User::whereIn('id', $ids)->update([
                'manual_plan_key' => null,
                'manual_plan_until' => null,
                'manual_plan_note' => null,
            ]);
        });

        return $updated;
    }
}
