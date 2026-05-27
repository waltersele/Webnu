<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Controller;
use App\Services\CompanySlugService;
use App\Services\MenuTranslationService;
use App\Services\UserPlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class OnboardingController extends Controller
{
    protected const MAX_STEP = 6;

    public function show(Request $request, UserPlanService $plans, MenuTranslationService $translations)
    {
        $user = $request->user();
        $company = $this->primaryCompany($user);

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
            'supportedLocales' => config('menu_locales.supported', []),
            'defaultLocale' => $company->defaultLocale(),
            'enabledExtra' => is_array($company->enabled_locales) ? $company->enabled_locales : [],
            'canTranslate' => $plans->canUseTranslation($user),
            'maxExtraLocales' => $plans->maxTranslationLocales($user),
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
                $data = $request->validate([
                    'name' => 'required|string|max:255',
                ]);
                $company->name = $data['name'];
                if (! $company->enabled) {
                    $company->slug = app(CompanySlugService::class)->generateFromName(
                        $data['name'],
                        $company->city,
                        $company->id,
                        optional($company->user)->resolveSlug()
                    );
                }
                $company->save();
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
                $default = $company->defaultLocale();
                $validated = $request->validate([
                    'locales' => 'nullable|array',
                    'locales.*' => 'string|in:' . implode(',', $supported),
                    'generate_ai' => 'nullable|boolean',
                ]);

                $locales = array_values(array_filter(
                    $validated['locales'] ?? [],
                    fn ($locale) => $locale !== $default
                ));

                if ($locales !== [] && $plans->canUseTranslation($user)) {
                    $plans->assertCanEnableLocales($user, count($locales));
                    $translations->updateCompanyLocales($company, $user, $locales);

                    if ($request->boolean('generate_ai')) {
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
        $name = trim((string) $company->name);

        return $name !== '' && $name !== 'Mi restaurante';
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
            // Demos no tienen user asociado: usamos la URL hub /carta/{slug}
            $urls[$id] = route('public.hub', ['slug' => $slug]);
        }

        return $urls;
    }
}
