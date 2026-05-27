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

    public function detectSupportedLocaleFromRequest(Request $request): string
    {
        $supported = array_keys(config('menu_locales.supported', []));
        $detected = $this->matchLocaleFromAcceptLanguage(
            $request->header('Accept-Language'),
            $supported
        );

        return $detected ?? config('menu_locales.default', 'es');
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
        if (request()->boolean('studio_preview') || request()->boolean('onb_preview')) {
            return [$company->defaultLocale()];
        }

        $locales = $company->publicLocales();
        $user = $company->relationLoaded('user') ? $company->user : $company->user()->first();

        if ($user && ! app(UserPlanService::class)->canUseTranslation($user)) {
            return [$company->defaultLocale()];
        }

        $maxExtra = $user ? app(UserPlanService::class)->maxTranslationLocales($user) : null;
        if ($maxExtra !== null) {
            return $this->capLocalesToPlan($locales, $company->defaultLocale(), $maxExtra);
        }

        return $locales;
    }

    /**
     * @param  string[]  $locales
     * @return string[]
     */
    public function capLocalesToPlan(array $locales, string $defaultLocale, int $maxExtra): array
    {
        $extras = array_values(array_filter(
            $locales,
            fn ($locale) => $locale !== $defaultLocale
        ));

        $extras = array_slice($extras, 0, $maxExtra);

        return array_values(array_unique(array_merge([$defaultLocale], $extras)));
    }

    /** @param string[] $allowed */
    protected function preferredLocaleFromRequest(Request $request, array $allowed): ?string
    {
        if (count($allowed) <= 1) {
            return null;
        }

        return $this->matchLocaleFromAcceptLanguage($request->header('Accept-Language'), $allowed);
    }

    /**
     * @param  string[]  $allowed
     */
    protected function matchLocaleFromAcceptLanguage(?string $header, array $allowed): ?string
    {
        if (! $header || $allowed === []) {
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
