<?php

namespace App\Console\Commands;

use App\MenuPreRegistration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PurgeStalePreAltaCommand extends Command
{
    protected $signature = 'webnu:purge-stale-pre-alta
                            {--dry-run : Solo listar registros y archivos}
                            {--limit=200 : Máximo de registros por ejecución}';

    protected $description = 'Purga pre-altas no reclamadas caducadas (archivos primero, luego BD).';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $dryRun = (bool) $this->option('dry-run');

        $records = MenuPreRegistration::query()
            ->where('status', MenuPreRegistration::STATUS_PENDING)
            ->where('expires_at', '<', now())
            ->orderBy('expires_at')
            ->limit($limit)
            ->get();

        if ($records->isEmpty()) {
            $this->info('No hay pre-altas pendientes caducadas.');

            return 0;
        }

        $disk = Storage::disk('pre_alta');
        $purged = 0;

        foreach ($records as $registration) {
            $this->line("Registro #{$registration->id} ({$registration->public_slug})");

            $manifest = $registration->media_manifest ?? [];
            foreach ($manifest as $relativePath) {
                $absolute = $disk->path($relativePath);
                if (is_file($absolute)) {
                    if ($dryRun) {
                        $this->line("  [dry-run] borraría archivo: {$relativePath}");
                    } else {
                        @unlink($absolute);
                    }
                }
            }

            $dir = $disk->path((string) $registration->id);
            if (is_dir($dir)) {
                if ($dryRun) {
                    $this->line("  [dry-run] borraría directorio: {$dir}");
                } else {
                    File::deleteDirectory($dir);
                }
            }

            if (! $dryRun) {
                $registration->status = MenuPreRegistration::STATUS_PURGED;
                $registration->menu_json = null;
                $registration->media_manifest = null;
                $registration->save();
            }

            $purged++;
        }

        $this->info(($dryRun ? 'Simulados' : 'Purgados') . ": {$purged} registro(s).");

        return 0;
    }
}
