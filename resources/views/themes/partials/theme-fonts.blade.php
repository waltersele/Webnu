@php
    $fontCatalog = config('company_templates.fonts', []);
    $theme = $company->resolvedThemeSettings();
    $headingKey = $theme['font_heading'] ?? config('company_templates.font_defaults.font_heading', 'playfair');
    $bodyKey = $theme['font_body'] ?? config('company_templates.font_defaults.font_body', 'inter');
    $used = collect([$headingKey, $bodyKey])
        ->map(function ($key) use ($fontCatalog) {
            return $fontCatalog[$key] ?? null;
        })
        ->filter()
        ->unique('family')
        ->values();
    $families = $used->map(function ($meta) {
        $weights = $meta['weights'] ?? '400;600;700';
        return 'family=' . urlencode($meta['family']) . ':wght@' . $weights;
    })->implode('&');
@endphp
@if($families !== '')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?{{ $families }}&display=swap" rel="stylesheet">
@endif
