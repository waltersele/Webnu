<header class="wn-modern-header wn-modern-header--{{ $company->headerToneIsDark() ? 'dark' : 'light' }}" role="banner">
    <div class="wn-modern-header__brand">
        @if ($company->logo)
            @include('themes.partials.logo-chip', ['company' => $company, 'size' => 'sm'])
        @else
            <span class="wn-modern-header__name">{{ $company->name }}</span>
        @endif
    </div>
    <button type="button" class="wn-modern-header__info" id="wn-info-toggle" aria-label="{{ isset($menuLocaleService) ? $menuLocaleService->uiLabel('business_info', $menuLocale ?? 'es') : 'Información del negocio' }}">
        @include('themes.partials.icons.svg-info')
    </button>
</header>
