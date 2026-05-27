<?php

namespace App\Http\Controllers\Concerns;

use App\Company;
use App\Services\UserPlanService;

trait BuildsCompanyStudioPayload
{
    /**
     * Build the studio payload (templates, presets, theme settings, preview url,
     * etc.) used by both Mi negocio (/admin/companies/{id}/edit) and the
     * Personalizacion tab inside /admin/sections.
     *
     * @return array<string,mixed>
     */
    protected function studioPayload(Company $company, UserPlanService $plans): array
    {
        $user = auth()->user();
        $templates = config('company_templates.templates', []);
        $templateLabels = collect($templates)->mapWithKeys(function ($meta, $key) {
            return [$key => $meta['label'] ?? $key];
        })->all();

        $hasMenuProducts = $company->sections()->whereHas('products')->exists();

        return [
            'templates'          => $templates,
            'templateAccess'     => $plans->templateAccessForUser($user),
            'colorKeys'          => config('company_templates.color_keys', []),
            'fontKeys'           => config('company_templates.font_keys', []),
            'fonts'              => config('company_templates.fonts', []),
            'themeSettings'      => $company->resolvedThemeSettings(),
            'themePresets'       => config('company_templates.presets', []),
            'templateLabels'     => $templateLabels,
            'previewUrl'         => $company->publicUrl(['studio_preview' => 1]),
            'previewUsesSamples' => ! $hasMenuProducts,
        ];
    }
}
