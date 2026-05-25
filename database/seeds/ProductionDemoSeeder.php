<?php

use App\Services\Demo\DemoMenuSeeder;
use Illuminate\Database\Seeder;

/**
 * Sembrador idempotente de las 9 cartas demo en producción.
 *
 *   php artisan db:seed --class=ProductionDemoSeeder
 *
 * o, más cómodo:
 *
 *   php artisan webnu:seed-demos
 *
 * Asume que los assets (fotos/vídeos) ya están en `public/img/...` (se
 * versionan en git). No hace ninguna descarga de red.
 */
class ProductionDemoSeeder extends Seeder
{
    public function run(): void
    {
        $command = $this->command;
        $seeder = new DemoMenuSeeder(function (string $message) use ($command) {
            if ($command) {
                $command->getOutput()->writeln($message);
            }
        });

        $count = $seeder->seed();

        if ($command) {
            $command->getOutput()->writeln("Cartas demo sembradas/actualizadas: {$count}");
        }
    }
}
