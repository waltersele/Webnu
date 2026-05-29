<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Controller;
use App\Services\AccountSlugService;
use App\Services\CompanySlugService;
use App\Services\MenuLocaleService;
use App\Services\PublicPathRegistry;
use App\Services\PublicUrlRedirectService;
use Illuminate\Validation\ValidationException;
use App\Services\MenuTranslationService;
use App\Services\UserPlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class OnboardingController extends Controller
{
    protected const MAX_STEP = 6;

    public function show(Request $request, UserPlanService $plans, MenuTranslationService $translations, MenuLocaleService $locales)
    {
        $user = $request->user();
        $company = $this->primaryCompany($user);
        $this->maybeApplyBrowserDefaultLocale($company, $locales, $request);

        if ($user->hasCompletedOnboarding()) {
            return redirect()->route('admin.companies.edit', $company);
        }

        $step = max(1, min(self::MAX_STEP, (int) $request->get('step', max(1, (int) $user->onboarding_step ?: 1))));

        $companyHasIdentity = $this->companyHasIdentity($company);
        if ($step === 2 && $companyHasIdentity) {
            return redirect()->route('admin.onboarding', ['step' => 3]);
        }

        $templateAccess = $plans->templateAccessForUser($user);
        $templates = collect(config('company_templates.templates', []));
        $templatePreviewUrls = $this->templatePreviewUrls();
        $themePresets = config('company_templates.presets', []);
        $slugService = app(CompanySlugService::class);
        $accountSlugs = app(AccountSlugService::class);
        $defaultBusinessName = $accountSlugs->isPendingPlaceholder($user->slug)
            ? ''
            : ucwords(str_replace('-', ' ', (string) $user->slug));
        $defaultCompanySlug = ($slugService->isPlaceholderSlug($company->slug) || $slugService->isAutoCartaSlug($company->slug))
            ? ''
            : (string) $company->slug;

        $publicUrl = $company->publicUrl();
        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=12&data=' . urlencode($publicUrl);
        $planPresentation = $plans->planPresentation($user);

        $stepAnimationMap = [
            1 => 'welcome',
            2 => 'business-name',
            3 => 'template',
            4 => 'languages',
            5 => 'menu-scan',
            6 => 'publish',
        ];

        return view('admin.onboarding.show', [
            'user' => $user,
            'company' => $company,
            'step' => $step,
            'maxStep' => self::MAX_STEP,
            'stepAnimationMap' => $stepAnimationMap,
            'templates' => $templates,
            'templateAccess' => $templateAccess,
            'templatePreviewUrls' => $templatePreviewUrls,
            'themePresets' => $themePresets,
            'scanPeriod' => $plans->menuScanPeriod($user),
            'plan' => $plans->tier($user),
            'planPresentation' => $planPresentation,
            'scansRemaining' => $plans->menuScansRemaining($user),
            'scansUsed' => $plans->menuScansUsed($user),
            'scanLimit' => $plans->menuScanLimit($user),
            'publicUrl' => $publicUrl,
            'qrImageUrl' => $qrImageUrl,
            'menuScanUrl' => route('admin.menu-scan.create'),
            'billingUrl' => route('admin.settings'),
            'companyHasIdentity' => $companyHasIdentity,
            'defaultBusinessName' => $defaultBusinessName,
            'defaultCompanySlug' => $defaultCompanySlug,
            'supportedLocales' => config('menu_locales.supported', []),
            'defaultLocale' => $company->defaultLocale(),
            'suggestedBaseLocale' => $locales->detectSupportedLocaleFromRequest($request),
            'enabledExtra' => is_array($company->enabled_locales) ? $company->enabled_locales : [],
            'canTranslate' => $plans->canUseTranslation($user),
            'maxExtraLocales' => $plans->maxTranslationLocales($user),
            'maxPublicLocales' => $plans->maxTranslationLocales($user) !== null
                ? $plans->maxTranslationLocales($user) + 1
                : null,
        ]);
    }

    public function update(Request $request, UserPlanService $plans, MenuTranslationService $translations)
    {
        $user = $request->user();
        $company = $this->primaryCompany($user);
        $this->authorize('update', $company);

        $request->validate(['step' => 'required|integer|between:1,6']);
        $step = (int) $request->get('step');

        switch ($step) {
            case 2:
                $slugs = app(CompanySlugService::class);
                $accounts = app(AccountSlugService::class);
                $paths = app(PublicPathRegistry::class);
                $redirects = app(PublicUrlRedirectService::class);

                $data = $request->validate([
                    'business_name' => 'required|string|max:255',
                    'name' => 'required|string|max:255',
                    'company_slug' => 'required|string|max:64',
                ]);

                $user = $request->user();
                $previousCompanyPath = $company->publicPath();
                $previousUserPath = $user->slug ? 'carta/' . $user->slug : null;

                if (! $company->isPublicSlugLocked()) {
                    $companySlug = $slugs->normalize($data['company_slug']);
                    $slugError = $slugs->validateCustomSlug($companySlug, $company->id);
                    if ($slugError) {
                        throw ValidationException::withMessages(['company_slug' => [$slugError]]);
                    }
                    $company->slug = $companySlug;
                    $company->name = $data['name'];
                    $company->public_url_format = 'simple';
                } else {
                    $company->name = $data['name'];
                }

                if (! $user->onboarding_completed_at) {
                    $ownerSlug = $accounts->normalize($data['business_name']);
                    $ownerError = $accounts->validateAccountSlug($ownerSlug, $user->id);
                    if ($ownerError) {
                        throw ValidationException::withMessages(['business_name' => [$ownerError]]);
                    }
                    $user->slug = $ownerSlug;
                    $user->save();
                    $company->load('user');
                }

                $pathError = $paths->validateCompanySlug($company->slug, $company, $user->slug);
                if ($pathError) {
                    throw ValidationException::withMessages(['company_slug' => [$pathError]]);
                }

                $company->save();

                $newCompanyPath = $company->fresh()->publicPath();
                if ($previousCompanyPath && $previousCompanyPath !== $newCompanyPath) {
                    $redirects->record($previousCompanyPath, $newCompanyPath, $company->id);
                }
                if ($previousUserPath && $user->slug && $previousUserPath !== 'carta/' . $user->slug) {
                    $redirects->record($previousUserPath, 'carta/' . $user->slug, null, $user->id);
                }
                break;

            case 3:
                $allowed = $plans->hasAllTemplates($user)
                    ? array_keys(config('company_templates.templates', []))
                    : $plans->freeTemplateKeys();
                if (count($allowed) === 0) {
                    $allowed = ['basic'];
                }
                $data = $request->validate([
                    'template' => 'required|string|in:' . implode(',', $allowed),
                ]);
                $plans->assertCanUseTemplate($user, $data['template']);
                $company->template = $data['template'];
                $preset = config('company_templates.presets.' . $data['template']);
                if (is_array($preset)) {
                    $company->theme_settings = array_merge(
                        $company->resolvedThemeSettings(),
                        $preset
                    );
                }
                $company->save();
                break;

            case 4:
                $supported = array_keys(config('menu_locales.supported', []));
                $validated = $request->validate([
                    'default_locale' => 'required|string|in:' . implode(',', $supported),
                    'locales' => 'nullable|array',
                    'locales.*' => 'string|in:' . implode(',', $supported),
                    'generate_ai' => 'nullable|boolean',
                ]);

                $company->default_locale = $validated['default_locale'];
                $company->save();

                $default = $company->defaultLocale();
                $locales = array_values(array_filter(
                    $validated['locales'] ?? [],
                    fn ($locale) => $locale !== $default
                ));

                if ($plans->canUseTranslation($user)) {
                    if ($locales !== []) {
                        $plans->assertCanEnableLocales($user, count($locales));
                    }
                    $translations->updateCompanyLocales($company, $user, $locales);

                    if ($request->boolean('generate_ai') && $locales !== []) {
                        $hasProducts = $company->sections()
                            ->whereHas('products')
                            ->exists();

                        if (! $hasProducts) {
                            return redirect()
                                ->route('admin.onboarding', ['step' => 4])
                                ->withErrors(['locales' => 'Añade al menos un plato antes de usar la traducción automática.']);
                        }

                        foreach ($locales as $locale) {
                            try {
                                $translations->translateCompany($company, $user, $locale);
                            } catch (\Throwable $e) {
                                \Log::error('Onboarding AI translation failed', [
                                    'user_id' => $user->id,
                                    'company_id' => $company->id,
                                    'locale' => $locale,
                                    'error' => $e->getMessage(),
                                ]);

                                return redirect()
                                    ->route('admin.onboarding', ['step' => 4])
                                    ->withErrors(['locales' => 'No se pudo generar la traducción automática. Inténtalo de nuevo más tarde.']);
                            }
                        }
                    }
                }
                break;

            case 6:
                $company->enabled = true;
                if ($company->public_url_format === null) {
                    $company->public_url_format = 'simple';
                }
                $company->lockPublicSlug();
                $company->save();

                $user->onboarding_completed_at = now();
                $user->onboarding_step = self::MAX_STEP;
                $user->save();

                return redirect()
                    ->route('admin.sections.index')
                    ->with('flash', '¡Tu carta está publicada! Ya puedes añadir platos y compartir el QR.');
        }

        $user->onboarding_step = max((int) $user->onboarding_step, $step);
        $user->save();

        $nextStep = min(self::MAX_STEP, $step + 1);

        return redirect()->route('admin.onboarding', ['step' => $nextStep]);
    }

    public function skip(Request $request)
    {
        $user = $request->user();
        $company = $this->primaryCompany($user);
        $this->authorize('update', $company);

        $company->enabled = true;
        if ($company->public_url_format === null) {
            $company->public_url_format = 'simple';
        }
        $company->lockPublicSlug();
        $company->save();

        $user->onboarding_completed_at = now();
        $user->onboarding_step = self::MAX_STEP;
        $user->save();

        return redirect()
            ->route('admin.sections.index')
            ->with('flash', 'Puedes completar la configuración cuando quieras desde Mi negocio.');
    }

    protected function primaryCompany($user): Company
    {
        $companyId = Cookie::get('selected_company');
        $company = Company::where('user_id', $user->id)
            ->when($companyId, function ($q) use ($companyId) {
                $q->where('id', (int) $companyId);
            })
            ->first();

        if (! $company) {
            $company = Company::where('user_id', $user->id)->latest('id')->firstOrFail();
            Cookie::queue(Cookie::forever('selected_company', $company->id));
        }

        return $company;
    }

    protected function companyHasIdentity(Company $company): bool
    {
        $slugs = app(CompanySlugService::class);
        $accounts = app(AccountSlugService::class);
        $name = trim((string) $company->name);
        $user = $company->user;

        if ($name === '' || $name === 'Mi restaurante') {
            return false;
        }

        if ($slugs->isPlaceholderSlug($company->slug) || $slugs->isAutoCartaSlug($company->slug)) {
            return false;
        }

        if (! $user || $accounts->isPendingPlaceholder($user->slug)) {
            return false;
        }

        return true;
    }

    /** @return array<string, string> */
    protected function templatePreviewUrls(): array
    {
        $fallbackSlugs = [
            'basic' => 'demo',
            'nocturne' => 'demo-cocktails',
            'otaku' => 'demo-fuego',
            'japo' => 'demo-japo',
            'fastfood' => 'demo-fastfood',
            'pizza' => 'demo-pizza',
            'mar' => 'demo-mar',
            'elegance' => 'demo-elegance',
            'asador' => 'demo-asador',
            'lumiere' => 'demo-elegance',
            'bistro' => 'demo',
            'temporada' => 'demo',
            'catalogo' => 'demo',
            'pasion' => 'demo',
            'oriental' => 'demo-japo',
            'visual' => 'demo',
        ];

        $urls = [];
        foreach (config('company_templates.templates', []) as $id => $tpl) {
            $slug = $tpl['preview_slug'] ?? $fallbackSlugs[$id] ?? 'demo';
            // Demos no tienen user asociado: usamos la URL pública /{companySlug}
            $urls[$id] = route('public.company', [
                'companySlug' => $slug,
                'studio_preview' => 1,
                'onb_preview' => 1,
            ]);
        }

        return $urls;
    }

    protected function maybeApplyBrowserDefaultLocale(Company $company, MenuLocaleService $locales, Request $request): void
    {
        $fallback = config('menu_locales.default', 'es');
        $stored = $company->default_locale;
        if ($stored !== null && $stored !== '' && $stored !== $fallback) {
            return;
        }

        $detected = $locales->detectSupportedLocaleFromRequest($request);
        if ($detected === $company->defaultLocale()) {
            return;
        }

        $company->default_locale = $detected;
        $company->save();
    }
}
