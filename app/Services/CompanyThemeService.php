<?php

namespace App\Services;

use App\Company;
use Illuminate\Http\Request;

class CompanyThemeService
{
    public function normalizeFromRequest(Request $request): array
    {
        $keys = array_keys(config('company_templates.color_keys', []));
        $normalized = [];

        foreach ($keys as $key) {
            $value = $request->input('theme_' . $key);
            if ($value && preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
                $normalized[$key] = strtolower($value);
            }
        }

        $allowedFonts = array_keys(config('company_templates.fonts', []));
        foreach (array_keys(config('company_templates.font_keys', [])) as $fontKey) {
            $value = $request->input('theme_' . $fontKey);
            if ($value && in_array($value, $allowedFonts, true)) {
                $normalized[$fontKey] = $value;
            }
        }

        $mode = $request->input('theme_surface_mode');
        if (is_string($mode) && in_array($mode, ['dark', 'light'], true)) {
            $normalized['surface_mode'] = $mode;
            $template = $this->resolveTemplate($request->input('template'));
            $modePalette = config('company_templates.surface_modes.' . $template . '.' . $mode, []);
            if (is_array($modePalette) && $modePalette !== []) {
                // Colores del modo como base; los hex del request (ya en $normalized) ganan.
                $normalized = array_merge($modePalette, $normalized);
            }
        }

        return $normalized;
    }

    public function resolveTemplate(?string $template): string
    {
        $allowed = array_keys(config('company_templates.templates', []));

        if ($template && in_array($template, $allowed, true)) {
            return $template;
        }

        return 'lumiere';
    }

    public function applyDesign(Company $company, string $template, array $themeOverrides): void
    {
        $company->template = $this->resolveTemplate($template);

        $existing = is_array($company->theme_settings) ? $company->theme_settings : [];
        $company->theme_settings = array_merge($existing, $themeOverrides);
        $company->save();
    }

    /**
     * @return array<string, string>
     */
    public function presentColorKeys(): array
    {
        $all = config('company_templates.color_keys', []);

        return array_intersect_key($all, array_flip(['primary', 'accent', 'background', 'text']));
    }
}
