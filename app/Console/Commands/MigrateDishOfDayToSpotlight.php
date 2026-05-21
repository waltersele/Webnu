<?php

namespace App\Console\Commands;

use App\Company;
use App\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class MigrateDishOfDayToSpotlight extends Command
{
    protected $signature = 'webnu:migrate-dish-of-day-to-spotlight
                            {--dry-run : Mostrar cambios sin guardar}';

    protected $description = 'Copia dish_of_day_product_id a daily_spotlight antes de eliminar columnas antiguas (solo MySQL/producción).';

    public function handle(): int
    {
        if (! Schema::hasColumn('companies', 'dish_of_day_product_id')) {
            $this->info('No existe la columna dish_of_day_product_id; no hay nada que migrar.');

            return 0;
        }

        if (! Schema::hasColumn('companies', 'daily_spotlight')) {
            $this->error('Ejecuta primero las migraciones que añaden daily_spotlight (php artisan migrate).');

            return 1;
        }

        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;

        Company::query()
            ->whereNotNull('dish_of_day_product_id')
            ->where(function ($q) {
                $q->whereNull('daily_spotlight')->orWhere('daily_spotlight', '');
            })
            ->orderBy('id')
            ->chunkById(50, function ($companies) use ($dryRun, &$updated) {
                foreach ($companies as $company) {
                    $product = Product::find($company->dish_of_day_product_id);
                    if (! $product) {
                        $this->warn("Empresa #{$company->id}: producto {$company->dish_of_day_product_id} no encontrado.");

                        continue;
                    }

                    $name = trim((string) $product->name);
                    if ($name === '') {
                        continue;
                    }

                    $price = $product->price_unit ?? $product->price_portion ?? null;
                    $priceStr = $price !== null && $price !== '' ? (string) $price : null;

                    $this->line("Empresa #{$company->id} ({$company->name}): «{$name}»" . ($priceStr ? " — {$priceStr}" : ''));

                    if (! $dryRun) {
                        $company->daily_spotlight = $name;
                        if ($priceStr !== null && trim($company->daily_spotlight_price ?? '') === '') {
                            $company->daily_spotlight_price = $priceStr;
                        }
                        $company->save();
                    }

                    $updated++;
                }
            });

        $this->info($dryRun
            ? "Dry-run: {$updated} empresa(s) se actualizarían."
            : "Migradas {$updated} empresa(s) a daily_spotlight.");

        return 0;
    }
}
