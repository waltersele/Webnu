<?php

namespace App\Services;

use App\Company;
use App\Menu;
use App\PublicSlugRedirect;
use App\User;

class PublicPathRegistry
{
    protected CompanySlugService $companySlugs;

    public function __construct(CompanySlugService $companySlugs)
    {
        $this->companySlugs = $companySlugs;
    }

    public function normalizePath(string $path): string
    {
        return PublicSlugRedirect::normalizePath($path);
    }

    public function companyPath(Company $company): string
    {
        if ($company->usesSimplePublicUrl() && $company->slug) {
            return 'carta/' . $company->slug;
        }

        $owner = optional($company->user)->slug;
        if ($owner && $company->slug) {
            return 'carta/' . $owner . '/' . $company->slug;
        }

        return 'carta/' . ($company->slug ?? '');
    }

    public function userHubPath(User $user): string
    {
        return 'carta/' . $user->slug;
    }

    public function menuPath(Menu $menu): string
    {
        $menu->loadMissing('company.user');
        $company = $menu->company;
        if (! $company || ! $menu->slug) {
            return '';
        }

        if ($company->usesSimplePublicUrl() && $company->slug) {
            return 'carta/' . $company->slug . '/menu/' . $menu->slug;
        }

        $owner = optional($company->user)->slug;
        if ($owner && $company->slug) {
            return 'carta/' . $owner . '/' . $company->slug . '/menu/' . $menu->slug;
        }

        return '';
    }

    /**
     * @param array{company_id?:int,user_id?:int,menu_id?:int} $except
     */
    public function isPathAvailable(string $path, array $except = []): bool
    {
        $path = $this->normalizePath($path);
        if ($path === '') {
            return false;
        }

        if (PublicSlugRedirect::where('from_path', $path)->exists()) {
            return false;
        }

        foreach (Company::with('user')->cursor() as $company) {
            if (isset($except['company_id']) && (int) $except['company_id'] === (int) $company->id) {
                continue;
            }
            if ($this->companyPath($company) === $path) {
                return false;
            }
        }

        foreach (User::whereNotNull('slug')->cursor() as $user) {
            if (isset($except['user_id']) && (int) $except['user_id'] === (int) $user->id) {
                continue;
            }
            if ($this->userHubPath($user) === $path) {
                return false;
            }
        }

        foreach (Menu::with('company.user')->whereNotNull('slug')->cursor() as $menu) {
            if (isset($except['menu_id']) && (int) $except['menu_id'] === (int) $menu->id) {
                continue;
            }
            $menuPath = $this->menuPath($menu);
            if ($menuPath !== '' && $menuPath === $path) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array{company_id?:int,user_id?:int,menu_id?:int} $except
     */
    public function validatePathAvailable(string $path, array $except = []): ?string
    {
        if (! $this->isPathAvailable($path, $except)) {
            return 'Esa URL ya está en uso. Prueba con otra variante.';
        }

        return null;
    }

    public function validateCompanySlug(string $slug, Company $company, ?string $ownerSlug = null): ?string
    {
        $slugError = $this->companySlugs->validateCustomSlug($slug, $company->id);
        if ($slugError) {
            return $slugError;
        }

        $company->slug = $slug;
        $path = $this->companyPath($company);

        return $this->validatePathAvailable($path, ['company_id' => $company->id]);
    }
}
