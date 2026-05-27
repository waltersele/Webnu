<?php

namespace App\Console\Commands;

use App\Company;
use App\Services\AccountSlugService;
use App\Services\CompanySlugService;
use App\Services\PublicUrlRedirectService;
use App\User;
use Illuminate\Console\Command;

class RepairPublicUrlCommand extends Command
{
    protected $signature = 'webnu:repair-public-url
                            {email : Email del usuario}
                            {--business-slug= : Nuevo slug de negocio (users.slug)}
                            {--company-slug= : Nuevo slug de carta (companies.slug)}
                            {--reset-onboarding : Vuelve al paso 2 del onboarding}';

    protected $description = 'Repara URLs públicas: redirects 301 desde paths antiguos y opcionalmente nuevos slugs.';

    public function handle(
        AccountSlugService $accounts,
        CompanySlugService $companySlugs,
        PublicUrlRedirectService $redirects
    ): int {
        $user = User::where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error('Usuario no encontrado.');

            return 1;
        }

        $company = $user->companies()->orderBy('id')->first();
        if (! $company) {
            $this->error('El usuario no tiene cartas.');

            return 1;
        }

        $oldPaths = array_unique(array_filter([
            $company->publicPath(),
            'carta/' . $user->slug . '/' . $company->slug,
            'carta/' . $company->slug,
        ]));

        if ($this->option('reset-onboarding')) {
            $user->onboarding_completed_at = null;
            $user->onboarding_step = 1;
            $user->save();
            $this->info('Onboarding reiniciado (paso 1).');
        }

        if ($businessSlug = $this->option('business-slug')) {
            $businessSlug = $accounts->normalize($businessSlug);
            $err = $accounts->validateAccountSlug($businessSlug, $user->id);
            if ($err) {
                $this->error($err);

                return 1;
            }
            $prev = 'carta/' . $user->slug;
            $user->slug = $businessSlug;
            $user->save();
            $redirects->record($prev, 'carta/' . $user->slug, null, $user->id);
            $this->info('users.slug → ' . $user->slug);
        }

        if ($companySlug = $this->option('company-slug')) {
            $companySlug = $companySlugs->normalize($companySlug);
            $err = $companySlugs->validateCustomSlug($companySlug, $company->id);
            if ($err) {
                $this->error($err);

                return 1;
            }
            $company->slug = $companySlug;
            $company->public_url_format = 'simple';
            $company->save();
            $this->info('companies.slug → ' . $company->slug);
        }

        $company->refresh();
        $user->refresh();

        $newPath = $company->publicPath();
        foreach ($oldPaths as $old) {
            if ($old && $old !== $newPath) {
                $redirects->record($old, $newPath, $company->id);
                $this->line("301: {$old} → {$newPath}");
            }
        }

        $this->info('URL canónica: ' . url($newPath));

        return 0;
    }
}
