<?php

namespace App\Console\Commands;

use App\Company;
use App\Services\BannerImageAnalyzer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ReanalyzeHeaders extends Command
{
    protected $signature = 'webnu:headers:reanalyze
                            {--only-missing : Solo procesa cabeceras sin metadatos}
                            {--dry-run : Muestra los cambios sin escribir}';

    protected $description = 'Recalcula metadatos del banner (luminancia, overlay) para empresas con imagen de cabecera.';

    public function handle(BannerImageAnalyzer $analyzer): int
    {
        if (! Schema::hasColumn('companies', 'header_luminance')) {
            $this->error('Faltan columnas de cabecera. Ejecuta primero php artisan migrate.');

            return 1;
        }

        $onlyMissing = (bool) $this->option('only-missing');
        $dryRun = (bool) $this->option('dry-run');

        $query = Company::query()
            ->whereNotNull('background_header')
            ->where('background_header', '!=', '');

        if ($onlyMissing) {
            $query->whereNull('header_luminance');
        }

        $total = $query->count();
        if ($total === 0) {
            $this->info('No hay empresas con banner que procesar.');

            return 0;
        }

        $this->info(($dryRun ? '[dry-run] ' : '') . "Procesando {$total} empresa(s)...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated = 0;
        $skipped = 0;

        $query->orderBy('id')->chunkById(50, function ($companies) use ($analyzer, $dryRun, $bar, &$updated, &$skipped) {
            foreach ($companies as $company) {
                $path = public_path('img/' . $company->background_header);
                if (! is_file($path)) {
                    $skipped++;
                    $bar->advance();

                    continue;
                }

                $crop = is_array($company->header_crop) ? $company->header_crop : null;
                $analysis = $analyzer->analyze($path, $crop);

                if (! $dryRun) {
                    $company->header_luminance = $analysis['luminance'];
                    $company->header_overlay_mode = $analysis['overlay_mode'];
                    $company->header_overlay_strength = $analysis['overlay_strength'];
                    $company->header_dominant_hex = $analysis['dominant_hex'];
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
