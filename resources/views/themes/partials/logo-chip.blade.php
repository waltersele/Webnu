@php
    $logoUrl = $logoUrl ?? null;
    $isFallback = false;
    if (! $logoUrl && isset($company)) {
        if (! empty($company->logo)) {
            $logoUrl = asset('img/' . $company->logo);
        } elseif (! empty($fallbackUrl)) {
            $logoUrl = $fallbackUrl;
            $isFallback = true;
        }
    }

    $hasStoredVariant = isset($company) && ! empty($company->logo_chip_variant);
    $variant = $hasStoredVariant ? $company->logo_chip_variant : 'glass';

    $shape   = $shape ?? 'rounded'; // 'rounded' | 'circle'
    $altText = $altText ?? (isset($company) ? $company->name : '');

    $autoContrast = ($hasStoredVariant && ! $isFallback) ? 'off' : 'on';

    $chipClasses = 'wn-menu-hero__logo-chip wn-menu-hero__logo-chip--bg-' . $variant;
    if ($shape === 'circle') {
        $chipClasses .= ' wn-menu-hero__logo-chip--circle';
    }
@endphp
@if($logoUrl)
    <div class="{{ $chipClasses }}" data-logo-autocontrast="{{ $autoContrast }}">
        <img src="{{ $logoUrl }}" alt="{{ $altText }}" loading="lazy" crossorigin="anonymous">
    </div>
@endif
