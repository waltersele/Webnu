<?php

namespace App\Console\Commands;

use App\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class AuditPublicMenus extends Command
{
    protected $signature = 'webnu:audit-public-menus
                            {--base= : URL base (por defecto APP_URL)}
                            {--csv= : CSV de inventario (columna slug); si no, lee todas las companies}
                            {--legacy : Probar redirects legacy del .htaccess de producción}
                            {--timeout=15 : Segundos por petición}';

    protected $description = 'Comprueba HTTP de cada /carta/{slug} y opcionalmente redirects legacy (lista post-migración).';

    /** @var array<int, array{from: string, to: string}> */
    protected const LEGACY_REDIRECTS = [
        ['/cerveceria-el-parque-san-vicente-del-raspeig/', 'https://webnu.es/carta/cerveceria-el-parque'],
        ['/hejiahuan-l-g-j-sl-2/', 'https://webnu.es/carta/bar-and-sushi'],
        ['/hejiahuan-l-g-j-sl/', 'https://webnu.es/carta/bar-and-sushi'],
        ['/hela2-jose-luis-gisbert-s-l/', 'https://webnu.es/carta/la-ibense'],
        ['/los-casanueva/', 'https://webnu.es/carta/los-casanueva'],
        ['/wanyuan/', 'https://webnu.es/carta/restaurante-wan-yuan'],
        ['/wanyuan-2/', 'https://webnu.es/carta/restaurante-wan-yuan'],
        ['/xiaohe-xu/', 'https://webnu.es/carta/restaurante-abc'],
        ['/pasionxlacarne', 'https://webnu.es/carta/pasionxlacarne'],
    ];

    public function handle(): int
    {
        $base = rtrim($this->option('base') ?: config('app.url'), '/');
        $timeout = (int) $this->option('timeout');

        $slugs = $this->resolveSlugs();
        if ($slugs === null) {
            return 1;
        }

        $this->info('Auditando ' . count($slugs) . ' cartas en ' . $base);

        $failures = [];
        $ok = 0;

        foreach ($slugs as $slug) {
            $url = $base . '/carta/' . $slug;
            try {
                $response = Http::timeout($timeout)
                    ->withOptions(['allow_redirects' => true])
                    ->get($url);
                $status = $response->status();
            } catch (\Throwable $e) {
                $status = 0;
                $failures[] = [$slug, $status, $e->getMessage()];
                continue;
            }

            if ($status >= 200 && $status < 400) {
                $ok++;
                $this->line("  <info>OK</info> {$status} {$slug}");
            } else {
                $failures[] = [$slug, $status, ''];
                $this->line("  <error>FAIL</error> {$status} {$slug}");
            }
        }

        if ($this->option('legacy')) {
            $this->line('');
            $this->info('Redirects legacy (.htaccess):');
            foreach (self::LEGACY_REDIRECTS as [$from, $expectedTo]) {
                $legacyUrl = $base . $from;
                try {
                    $response = Http::timeout($timeout)
                        ->withOptions(['allow_redirects' => false])
                        ->get($legacyUrl);
                    $status = $response->status();
                    $location = $response->header('Location') ?? '';
                } catch (\Throwable $e) {
                    $status = 0;
                    $location = $e->getMessage();
                }

                $path = parse_url($expectedTo, PHP_URL_PATH) ?: '';
                $match = $status >= 300 && $status < 400
                    && (strpos($location, $expectedTo) !== false || ($path !== '' && strpos($location, $path) !== false));

                if ($match) {
                    $this->line("  <info>OK</info> {$status} {$from} → {$location}");
                } else {
                    $failures[] = ['legacy:' . $from, $status, $location ?: 'sin Location'];
                    $this->line("  <error>FAIL</error> {$status} {$from} (esperado → {$expectedTo})");
                }
            }
        }

        $reportPath = storage_path('migration-inventory/audit-' . date('Y-m-d-His') . '.txt');
        if (! is_dir(dirname($reportPath))) {
            mkdir(dirname($reportPath), 0755, true);
        }
        file_put_contents(
            $reportPath,
            "base={$base}\nok={$ok}\nfail=" . count($failures) . "\n\n" . print_r($failures, true)
        );
        $this->line('');
        $this->info("Resumen: {$ok} OK, " . count($failures) . ' fallos. Informe: ' . $reportPath);

        return count($failures) > 0 ? 1 : 0;
    }

    /**
     * @return list<string>|null
     */
    protected function resolveSlugs(): ?array
    {
        $csv = $this->option('csv');
        if ($csv) {
            if (! is_file($csv)) {
                $this->error('CSV no encontrado: ' . $csv);

                return null;
            }
            $handle = fopen($csv, 'r');
            $header = fgetcsv($handle);
            $slugIndex = array_search('slug', $header ?: [], true);
            if ($slugIndex === false) {
                $this->error('El CSV debe tener columna "slug".');

                return null;
            }
            $slugs = [];
            while (($row = fgetcsv($handle)) !== false) {
                if (! empty($row[$slugIndex])) {
                    $slugs[] = $row[$slugIndex];
                }
            }
            fclose($handle);

            return array_values(array_unique($slugs));
        }

        if (! Schema::hasTable('companies')) {
            $this->error('Sin tabla companies y sin --csv.');

            return null;
        }

        return Company::query()->orderBy('slug')->pluck('slug')->all();
    }
}
