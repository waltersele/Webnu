<?php

namespace App\Services;

use App\Company;
use Illuminate\Http\Request;

class MenuLocaleService
{
    public function resolveMenuLocale(Request $request, Company $company): string
    {
        $default = $company->defaultLocale();
        $allowed = $this->publicLocalesForCompany($company);

        if ($request->filled('lang')) {
            $requested = strtolower((string) $request->query('lang'));
            if (in_array($requested, $allowed, true)) {
                return $requested;
            }
        }

        if (! $request->has('lang') && ! $request->boolean('studio_preview') && ! $request->boolean('sales_demo')) {
            $preferred = $this->preferredLocaleFromRequest($request, $allowed);
            if ($preferred) {
                return $preferred;
            }
        }

        return $default;
    }

    public function localeMeta(string $locale): array
    {
        $supported = config('menu_locales.supported', []);

        return $supported[$locale] ?? [
            'label' => strtoupper($locale),
            'native' => strtoupper($locale),
            'flag' => strtoupper($locale),
        ];
    }

    public function uiLabel(string $key, string $locale): string
    {
        $labels = config('menu_locales.ui.' . $locale, []);
        if (! empty($labels[$key])) {
            return $labels[$key];
        }

        return config('menu_locales.ui.es.' . $key, $key);
    }

    public function allergenLabel(string $slug, string $locale): string
    {
        $labels = config('allergen_labels.' . $slug, []);
        if (! empty($labels[$locale])) {
            return $labels[$locale];
        }

        $catalog = config('allergens.catalog.' . $slug, []);

        return $catalog['name'] ?? $slug;
    }

    public function menuUrl(Company $company, string $locale, array $query = []): string
    {
        $query = array_merge($query, ['lang' => $locale]);

        return $company->publicUrl($query);
    }

    /** @return string[] */
    public function publicLocalesForCompany(Company $company): array
    {
        $locales = $company->publicLocales();
        $user = $company->relationLoaded('user') ? $company->user : $company->user()->first();

        if ($user && ! app(UserPlanService::class)->canUseTranslation($user)) {
            return [$company->defaultLocale()];
        }

        return $locales;
    }

    /** @param string[] $allowed */
    protected function preferredLocaleFromRequest(Request $request, array $allowed): ?string
    {
        if (count($allowed) <= 1) {
            return null;
        }

        $header = $request->header('Accept-Language');
        if (! $header) {
            return null;
        }

        $candidates = [];
        foreach (explode(',', $header) as $part) {
            $part = trim(explode(';', $part)[0]);
            if ($part === '') {
                continue;
            }
            $primary = strtolower(substr($part, 0, 2));
            $candidates[] = strtolower($part);
            $candidates[] = $primary;
        }

        foreach ($candidates as $candidate) {
            if (in_array($candidate, $allowed, true)) {
                return $candidate;
            }
        }

        return null;
    }
}
