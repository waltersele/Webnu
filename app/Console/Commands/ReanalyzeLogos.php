<?php

namespace App\Console\Commands;

use App\Company;
use App\Services\LogoColorAnalyzer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ReanalyzeLogos extends Command
{
    protected $signature = 'webnu:logos:reanalyze
                            {--only-missing : Solo procesa logos sin variante guardada}
                            {--dry-run : Muestra los cambios sin escribir}';

    protected $description = 'Recalcula los metadatos del logo (luminancia, color dominante, variante chip) para todas las empresas con logo.';

    public function handle(LogoColorAnalyzer $analyzer): int
    {
        if (! Schema::hasColumn('companies', 'logo_chip_variant')) {
            $this->error('Falta la columna logo_chip_variant. Ejecuta primero php artisan migrate.');
            return 1;
        }

        $onlyMissing = (bool) $this->option('only-missing');
        $dryRun = (bool) $this->option('dry-run');

        $query = Company::query()
            ->whereNotNull('logo')
            ->where('logo', '!=', '');

        if ($onlyMissing) {
            $query->whereNull('logo_chip_variant');
        }

        $total = $query->count();
        if ($total === 0) {
            $this->info('No hay empresas con logo que procesar.');
            return 0;
        }

        $this->info(($dryRun ? '[dry-run] ' : '') . "Procesando {$total} empresa(s)...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated = 0;
        $skipped = 0;

        $query->orderBy('id')->chunkById(50, function ($companies) use ($analyzer, $dryRun, $bar, &$updated, &$skipped) {
            foreach ($companies as $company) {
                $path = public_path('img/' . $company->logo);
                if (! is_file($path)) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $analysis = $analyzer->analyze($path);

                if (! $dryRun) {
                    $company->logo_luminance    = $analysis['luminance'];
                    $company->logo_has_solid_bg = $analysis['has_solid_bg'];
                    $company->logo_dominant_hex = $analysis['dominant_hex'];
                    $company->logo_chip_variant = $analysis['chip_variant'];
                    $company->save();
                }

                $updated++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        $this->info(($dryRun ? '[dry-run] ' : '') . "Procesadas {$updated}, omitidas {$skipped} (fichero no encontrado).");

        return 0;
    }
}
