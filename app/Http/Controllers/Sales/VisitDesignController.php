<?php

namespace App\Http\Controllers\Sales;

use App\Company;
use App\Http\Controllers\Controller;
use App\Services\CompanyThemeService;
use App\Services\Sales\SalesLeadService;
use Illuminate\Http\Request;

class VisitDesignController extends Controller
{
    public function show(Company $company, SalesLeadService $leads, CompanyThemeService $themes)
    {
        $visit = $leads->findActiveLeadFor(auth()->user(), $company->id);
        $this->authorize('update', $visit);

        $productCount = $visit->sections()->withCount('products')->get()->sum('products_count');
        if ($productCount < 1) {
            return redirect()
                ->route('sales.visit.show', $visit->id)
                ->withErrors(['present' => 'Importa la carta antes de presentarla.']);
        }

        $templateList = config('company_templates.templates', []);
        $presets = config('company_templates.presets', []);
        $colorKeys = $themes->presentColorKeys();
        $resolved = $visit->resolvedThemeSettings();

        return view('sales.visit.present', [
            'visit' => $visit,
            'templates' => $templateList,
            'presets' => $presets,
            'colorKeys' => $colorKeys,
            'resolvedTheme' => $resolved,
            'previewUrl' => route('see_menu', $visit->slug),
        ]);
    }

    public function update(Request $request, Company $company, SalesLeadService $leads, CompanyThemeService $themes)
    {
        $visit = $leads->findActiveLeadFor(auth()->user(), $company->id);
        $this->authorize('update', $visit);

        $data = $request->validate([
            'template' => 'required|string',
        ]);

        $themeOverrides = $themes->normalizeFromRequest($request);
        $themes->applyDesign($visit, $data['template'], $themeOverrides);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()
            ->route('sales.visit.present', $visit->id)
            ->with('flash', 'Diseño guardado.');
    }
}
