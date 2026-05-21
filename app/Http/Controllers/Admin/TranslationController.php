<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Controller;
use App\Product;
use App\Section;
use App\Services\MenuLocaleService;
use App\Services\MenuTranslationService;
use App\Services\UserPlanService;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function edit(Company $company, MenuTranslationService $translations, UserPlanService $plans, MenuLocaleService $locales)
    {
        $this->authorize('update', $company);

        $user = auth()->user();
        $sections = $company->sections()->with(['products.translations', 'translations'])->orderBy('order')->get();
        $supported = config('menu_locales.supported', []);
        $defaultLocale = $company->defaultLocale();
        $enabledExtra = is_array($company->enabled_locales) ? $company->enabled_locales : [];

        return view('admin.companies.languages', [
            'company' => $company,
            'sections' => $sections,
            'supportedLocales' => $supported,
            'defaultLocale' => $defaultLocale,
            'enabledExtra' => $enabledExtra,
            'stats' => $translations->statsForCompany($company),
            'canTranslate' => $plans->canUseTranslation($user),
            'maxExtraLocales' => $plans->maxTranslationLocales($user),
            'planLabel' => $plans->tier($user)['label'] ?? 'Gratis',
            'billingUrl' => route('admin.settings'),
            'publicUrl' => route('see_menu', $company->slug),
        ]);
    }

    public function updateLocales(Request $request, Company $company, MenuTranslationService $translations, UserPlanService $plans)
    {
        $this->authorize('update', $company);

        $supported = array_keys(config('menu_locales.supported', []));
        $validated = $request->validate([
            'locales' => 'nullable|array',
            'locales.*' => 'string|in:' . implode(',', $supported),
        ]);

        $locales = $validated['locales'] ?? [];
        $translations->updateCompanyLocales($company, $request->user(), $locales);

        return redirect()
            ->route('admin.companies.languages', $company)
            ->with('flash', 'Idiomas actualizados.');
    }

    public function generate(Request $request, Company $company, MenuTranslationService $translations)
    {
        $this->authorize('update', $company);

        $supported = array_keys(config('menu_locales.supported', []));
        $validated = $request->validate([
            'locale' => 'required|string|in:' . implode(',', $supported),
        ]);

        $job = $translations->translateCompany($company, $request->user(), $validated['locale']);

        return redirect()
            ->route('admin.companies.languages', $company)
            ->with('flash', "Traducción completada ({$job->items_done} elementos) al " . strtoupper($validated['locale']) . '.');
    }

    public function updateSection(Request $request, Company $company, Section $section, MenuTranslationService $translations)
    {
        $this->authorize('update', $company);
        abort_unless((int) $section->company_id === (int) $company->id, 404);

        $supported = array_keys(config('menu_locales.supported', []));
        $validated = $request->validate([
            'locale' => 'required|string|in:' . implode(',', $supported),
            'name' => 'required|string|max:255',
        ]);

        if ($validated['locale'] === $company->defaultLocale()) {
            $section->name = $validated['name'];
            $section->save();
        } else {
            app(UserPlanService::class)->assertCanUseTranslation($request->user());
            $translations->saveSectionTranslation($section, $validated['locale'], $validated['name']);
        }

        return back()->with('flash', 'Sección guardada.');
    }

    public function updateProduct(Request $request, Company $company, Product $product, MenuTranslationService $translations)
    {
        $product->load('section');
        $this->authorize('update', $company);
        abort_unless((int) $product->section->company_id === (int) $company->id, 404);

        $supported = array_keys(config('menu_locales.supported', []));
        $validated = $request->validate([
            'locale' => 'required|string|in:' . implode(',', $supported),
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validated['locale'] === $company->defaultLocale()) {
            $product->name = $validated['name'];
            $product->description = $validated['description'] ?? null;
            $product->save();
        } else {
            app(UserPlanService::class)->assertCanUseTranslation($request->user());
            $translations->saveProductTranslation(
                $product,
                $validated['locale'],
                $validated['name'],
                $validated['description'] ?? null
            );
        }

        return back()->with('flash', 'Plato guardado.');
    }
}
