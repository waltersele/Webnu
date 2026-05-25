<?php

namespace App\Console\Commands;

use App\Company;
use App\Services\UserPlanService;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExportCompaniesInventory extends Command
{
    protected $signature = 'webnu:export-companies-inventory
                            {--output= : Ruta del CSV (por defecto storage/migration-inventory/companies-YYYYMMDD.csv)}
                            {--with-users : Incluir hoja users_companies en segundo CSV}';

    protected $description = 'Exporta inventario de negocios (slugs, URLs QR) para verificación pre/post migración.';

    public function handle(): int
    {
        if (! Schema::hasTable('companies')) {
            $this->error('Tabla companies no existe. Ejecuta migraciones primero.');

            return 1;
        }

        $dir = storage_path('migration-inventory');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $output = $this->option('output')
            ?: $dir . DIRECTORY_SEPARATOR . 'companies-' . date('Y-m-d-His') . '.csv';

        $companies = Company::query()
            ->orderBy('slug')
            ->get();

        $handle = fopen($output, 'w');
        if ($handle === false) {
            $this->error('No se pudo escribir: ' . $output);

            return 1;
        }

        fputcsv($handle, [
            'id',
            'name',
            'slug',
            'menu_type',
            'enabled',
            'template',
            'user_id',
            'public_url',
            'sections_count',
            'products_count',
            'logo_path',
            'logo_exists',
            'pdf_path',
            'pdf_exists',
        ]);

        $missingAssets = 0;
        $emptyMenus = 0;

        foreach ($companies as $company) {
            $logoPath = $company->logo ? 'img/' . ltrim($company->logo, '/') : '';
            $pdfPath = (int) $company->menu_type === 2 && $company->menu_type_2_pdf
                ? 'img/' . ltrim($company->menu_type_2_pdf, '/')
                : '';

            $logoExists = $logoPath !== '' && is_file(public_path($logoPath));
            $pdfExists = $pdfPath !== '' && is_file(public_path($pdfPath));

            if (($logoPath && ! $logoExists) || ($pdfPath && ! $pdfExists)) {
                $missingAssets++;
            }

            $sectionsCount = 0;
            $productsCount = 0;
            if ((int) $company->menu_type === 1) {
                $sectionsCount = (int) $company->sections()->count();
                $productsCount = (int) DB::table('products')
                    ->join('sections', 'sections.id', '=', 'products.section_id')
                    ->where('sections.company_id', $company->id)
                    ->count();
                if ($sectionsCount === 0 && $productsCount === 0) {
                    $emptyMenus++;
                }
            }

            fputcsv($handle, [
                $company->id,
                $company->name,
                $company->slug,
                $company->menu_type,
                $company->enabled ? '1' : '0',
                $company->template ?? '',
                $company->user_id,
                $company->publicUrl(),
                $sectionsCount,
                $productsCount,
                $logoPath,
                $logoExists ? '1' : '0',
                $pdfPath,
                $pdfExists ? '1' : '0',
            ]);
        }

        fclose($handle);

        $this->info('Exportadas ' . $companies->count() . ' cartas → ' . $output);
        if ($missingAssets > 0) {
            $this->warn($missingAssets . ' filas con logo o PDF referenciado pero ausente en public/.');
        }
        if ($emptyMenus > 0) {
            $this->warn($emptyMenus . ' cartas tipo digital (menu_type=1) sin secciones ni platos en BD.');
        }

        if ($this->option('with-users') && Schema::hasTable('users')) {
            $plans = app(UserPlanService::class);
            $usersPath = preg_replace('/\.csv$/', '-users.csv', $output);
            $payingPath = preg_replace('/\.csv$/', '-paying.csv', $output);
            $uh = fopen($usersPath, 'w');
            $ph = fopen($payingPath, 'w');
            fputcsv($uh, [
                'user_id', 'email', 'stripe_id', 'plan_db', 'plan_resolved', 'is_paying',
                'subscription_name', 'stripe_status', 'trial_ends_at',
                'company_id', 'company_name', 'slug', 'public_url',
            ]);
            fputcsv($ph, [
                'user_id', 'email', 'stripe_id', 'plan_resolved', 'subscription_name', 'stripe_status',
                'company_name', 'slug', 'public_url',
            ]);

            $payingCount = 0;

            User::query()
                ->with(['companies', 'subscriptions'])
                ->whereHas('companies')
                ->orderBy('email')
                ->chunk(100, function ($users) use ($plans, $uh, $ph, &$payingCount) {
                    foreach ($users as $user) {
                        $subscription = $user->primarySubscription();
                        $isPaying = $user->hasActiveSubscription()
                            && ($subscription || $user->onGenericTrial());
                        $subName = $subscription ? $subscription->name : '';
                        $subStatus = $subscription ? $subscription->stripe_status : '';
                        $planResolved = $plans->planKey($user);

                        foreach ($user->companies as $company) {
                            $row = [
                                $user->id,
                                $user->email,
                                $user->stripe_id ?? '',
                                $user->plan ?? '',
                                $planResolved,
                                $isPaying ? '1' : '0',
                                $subName,
                                $subStatus,
                                $user->trial_ends_at ? $user->trial_ends_at->toDateTimeString() : '',
                                $company->id,
                                $company->name,
                                $company->slug,
                                url('/carta/' . $company->slug),
                            ];
                            fputcsv($uh, $row);

                            if ($isPaying && $subscription && in_array($subStatus, ['active', 'trialing'], true)) {
                                $payingCount++;
                                fputcsv($ph, [
                                    $user->id,
                                    $user->email,
                                    $user->stripe_id ?? '',
                                    $planResolved,
                                    $subName,
                                    $subStatus,
                                    $company->name,
                                    $company->slug,
                                    url('/carta/' . $company->slug),
                                ]);
                            }
                        }
                    }
                });

            fclose($uh);
            fclose($ph);
            $this->info('Usuarios con negocio → ' . $usersPath);
            if ($payingCount > 0) {
                $this->info('Suscripciones Stripe activas → ' . $payingPath . ' (' . $payingCount . ' filas)');
            } else {
                @unlink($payingPath);
                $this->line('Sin suscripciones Stripe activas (normal si el cobro es por otros medios).');
            }
        }

        $this->line('APP_URL actual: ' . config('app.url'));

        return 0;
    }
}
