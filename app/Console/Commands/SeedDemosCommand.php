<?php

namespace App\Console\Commands;

use App\Services\Demo\DemoMenuSeeder;
use Illuminate\Console\Command;

class SeedDemosCommand extends Command
{
    protected $signature = 'webnu:seed-demos
        {--owner-email=demo@webnu.es : Email propietario de las cartas demo}
        {--owner-password=demo123 : Contraseña inicial si hay que crear el usuario}';

    protected $description = 'Siembra/actualiza las 9 cartas demo (idempotente).';

    public function handle(): int
    {
        $seeder = new DemoMenuSeeder(function (string $message) {
            $this->line($message);
        });

        $count = $seeder->seed(
            (string) $this->option('owner-email'),
            (string) $this->option('owner-password')
        );

        $this->info("Cartas demo sembradas/actualizadas: {$count}");

        return 0;
    }
}
