<?php

namespace App\Console\Commands;

use App\PlatformSetting;
use App\Services\Platform\MeasurementSettingsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DeployCheckCommand extends Command
{
    protected $signature = 'webnu:deploy-check';

    protected $description = 'Comprueba requisitos mínimos de despliegue (entorno, DB, cola, integraciones, SEO)';

    public function handle(MeasurementSettingsService $measurement): int
    {
        $ok = true;

        if (blank(config('app.key'))) {
            $this->error('APP_KEY no configurada. Ejecuta php artisan key:generate');
            $ok = false;
        } else {
            $this->info('APP_KEY: OK');
        }

        if (config('app.env') === 'production' && config('app.debug')) {
            $this->error('APP_DEBUG debe ser false en producción');
            $ok = false;
        } else {
            $this->info('APP_DEBUG: OK');
        }

        if (config('app.env') === 'production' && ! str_starts_with((string) config('app.url'), 'https://')) {
            $this->warn('APP_URL debería usar https:// en producción');
        }

        try {
            DB::connection()->getPdo();
            $driver = config('database.default');
            $this->info("Base de datos ({$driver}): OK");
        } catch (\Throwable $e) {
            $this->error('Base de datos: ' . $e->getMessage());
            $ok = false;
        }

        try {
            if (! Schema::hasTable('jobs')) {
                $this->error('Tabla jobs ausente. Ejecuta php artisan migrate');
                $ok = false;
            } else {
                $this->info('Cola (tabla jobs): OK');
            }
        } catch (\Throwable $e) {
            $this->error('No se pudo comprobar la tabla jobs: ' . $e->getMessage());
            $ok = false;
        }

        $queueDefault = (string) config('queue.default');
        if ($queueDefault === 'sync') {
            $this->info('Cola: sync (sin worker; OK en hosting sin queue:work)');
        } elseif ($queueDefault !== 'database' && config('app.env') === 'production') {
            $this->warn('QUEUE_CONNECTION debería ser database o sync en producción (actual: ' . $queueDefault . ')');
        } else {
            $this->info('Cola (driver ' . $queueDefault . '): OK');
        }

        try {
            if (Schema::hasTable('jobs') && $queueDefault !== 'sync') {
                $pendingJobs = (int) DB::table('jobs')->count();
                if ($pendingJobs > 50) {
                    $this->warn("Cola con {$pendingJobs} trabajo(s) pendiente(s). Revisa queue:work / cron.");
                } elseif ($pendingJobs > 0) {
                    $this->warn("Cola con {$pendingJobs} trabajo(s) pendiente(s).");
                }
            }
        } catch (\Throwable $e) {
            $this->warn('No se pudo comprobar la cola: ' . $e->getMessage());
        }

        $this->info('Schedule: asegúrate de cron `* * * * * php artisan schedule:run` (expire-trials, reminders, blog).');
        $this->line('  php artisan schedule:list');

        $pwaIcons = [
            public_path('img/pwa/icon-192.png'),
            public_path('img/pwa/icon-512.png'),
            public_path('img/pwa/icon-512-maskable.png'),
        ];

        foreach ($pwaIcons as $iconPath) {
            if (! is_file($iconPath) || filesize($iconPath) === 0) {
                $this->warn('Icono PWA ausente o vacío: ' . basename($iconPath));
            } else {
                $this->info('Icono PWA: ' . basename($iconPath));
            }
        }

        try {
            if (! PlatformSetting::hasStripeSecret() && config('app.env') === 'production') {
                $this->warn('Stripe secret no configurado (admin o .env)');
            }

            if (! PlatformSetting::hasGeminiApiKey()) {
                $this->warn('Gemini API key no configurada (escaneo de carta desactivado)');
            }
        } catch (\Throwable $e) {
            $this->warn('No se pudo comprobar integraciones en platform_settings: ' . $e->getMessage());
        }

        try {
            if ($measurement->isEnabled() && ! $measurement->hasConsentableTools()) {
                $this->warn('Medición activada pero sin GA4/GTM/Clarity configurado');
            }
        } catch (\Throwable $e) {
            $this->warn('No se pudo comprobar medición: ' . $e->getMessage());
        }

        $robotsPath = public_path('robots.txt');
        if (! is_file($robotsPath)) {
            $this->warn('public/robots.txt ausente (existe ruta Laravel /robots.txt como alternativa)');
        } else {
            $robots = (string) file_get_contents($robotsPath);
            if (strpos($robots, 'Disallow: /admin') === false) {
                $this->warn('robots.txt no bloquea /admin');
            } else {
                $this->info('robots.txt: OK');
            }
        }

        if (! class_exists(\App\Http\Controllers\SitemapController::class)) {
            $this->warn('SitemapController no encontrado');
        } else {
            $this->info('Sitemap: ruta /sitemap.xml registrada');
        }

        if ($ok) {
            $this->info('Deploy check completado.');

            return self::SUCCESS;
        }

        $this->error('Deploy check falló con errores críticos.');

        return self::FAILURE;
    }
}
