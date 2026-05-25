@php
    $hasCompany = !empty($selected_company);
    $currentCompany = null;
    if ($hasCompany && !empty($available_companies)) {
        $currentCompany = $available_companies->firstWhere('id', (int) $selected_company);
    }

    $cartaUrl = $hasCompany ? route('admin.sections.index') : route('admin.companies.index');
    $qrUrl = $hasCompany && $currentCompany
        ? route('admin.qrgenerator', $currentCompany)
        : route('admin.companies.index');

    $screensActive = request()->is('admin/tvpik*')
        || request()->is('admin/integrations*')
        || request()->is('admin/signage*');
    $menuActive = request()->is('admin/sections*') || request()->is('admin/menu-scan*') || request()->is('admin/products*');
    $qrActive = request()->is('admin/qrgenerator*');
    $moreActive = request()->is('admin/companies*');
    $canUseTvpik = isset($planFeatures['tvpik']) ? (bool) $planFeatures['tvpik'] : true;
@endphp
<nav class="webnu-bottomnav d-lg-none" aria-label="Navegación inferior">
    <a href="{{ $cartaUrl }}" class="webnu-bottomnav__item {{ $menuActive ? 'is-active' : '' }}">
        <i class="ti ti-tools-kitchen-2"></i>
        <span>Mi carta</span>
    </a>
    @if($hasCompany && $currentCompany)
        <button type="button"
                class="webnu-bottomnav__item"
                data-bs-toggle="modal"
                data-bs-target="#wn-qr-modal"
                aria-label="Ver código QR">
            <i class="ti ti-qrcode"></i>
            <span>QR</span>
        </button>
    @else
        <a href="{{ route('admin.companies.index') }}" class="webnu-bottomnav__item">
            <i class="ti ti-qrcode"></i>
            <span>QR</span>
        </a>
    @endif
    <a href="{{ route('admin.tvpik.index') }}" class="webnu-bottomnav__item {{ $screensActive ? 'is-active' : '' }} {{ ! $canUseTvpik ? 'is-locked' : '' }}">
        <span class="webnu-bottomnav__icon-wrap">
            <i class="ti ti-device-tv"></i>
            @if(! $canUseTvpik)
                <span class="webnu-bottomnav__lock" aria-hidden="true"><i class="ti ti-lock"></i></span>
            @endif
        </span>
        <span>Pantallas</span>
    </a>
    <button type="button"
            class="webnu-bottomnav__item {{ $moreActive ? 'is-active' : '' }}"
            data-bs-toggle="offcanvas"
            data-bs-target="#wnShellNavOffcanvas"
            aria-label="Más opciones">
        <i class="ti ti-dots"></i>
        <span>Más</span>
    </button>
</nav>
