<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class ExpireTrialsCommand extends Command
{
    protected $signature = 'webnu:expire-trials {--dry-run : Solo muestra cuántos usuarios se actualizarían}';

    protected $description = 'Transiciona cuentas con trial caducado al plan free sin borrar datos.';

    public function handle(): int
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
            $this->info("Usuarios a actualizar: {$count}");

            return 0;
        }

        $updated = 0;
        $query->orderBy('id')->chunkById(500, function ($users) use (&$updated) {
            $ids = $users->pluck('id')->all();
            $updated += User::whereIn('id', $ids)->update([
                'plan' => 'free',
                'trial_plan_key' => null,
            ]);
        });

        $this->info("Trials expirados procesados: {$updated} (candidatos: {$count})");

        return 0;
    }
}
