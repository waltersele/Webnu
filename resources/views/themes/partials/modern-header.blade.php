<header class="wn-modern-header" role="banner">
    <div class="wn-modern-header__brand">
        @if ($company->logo)
            <img src="{{ URL::to('/') . '/img/' . $company->logo }}" alt="{{ $company->name }}" class="wn-modern-header__logo">
        @else
            <span class="wn-modern-header__name">{{ $company->name }}</span>
        @endif
    </div>
    <button type="button" class="wn-modern-header__info" id="wn-info-toggle" aria-label="{{ isset($menuLocaleService) ? $menuLocaleService->uiLabel('business_info', $menuLocale ?? 'es') : 'Información del negocio' }}">
        <i class="fas fa-info-circle" aria-hidden="true"></i>
    </button>
</header>
