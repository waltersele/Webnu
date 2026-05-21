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

    $homeActive = request()->routeIs('admin.dashboard');
    $menuActive = request()->is('admin/sections*') || request()->is('admin/menu-scan*') || request()->is('admin/products*');
    $qrActive = request()->is('admin/qrgenerator*');
    $moreActive = request()->is('admin/integrations*') || request()->is('admin/signage*') || request()->is('admin/companies*');
@endphp
<nav class="webnu-bottomnav d-lg-none" aria-label="Navegación inferior">
    <a href="{{ route('admin.dashboard') }}" class="webnu-bottomnav__item {{ $homeActive ? 'is-active' : '' }}">
        <i class="ti ti-home"></i>
        <span>Inicio</span>
    </a>
    <a href="{{ $cartaUrl }}" class="webnu-bottomnav__item {{ $menuActive ? 'is-active' : '' }}">
        <i class="ti ti-tools-kitchen-2"></i>
        <span>Carta</span>
    </a>
    <a href="{{ $qrUrl }}" class="webnu-bottomnav__item {{ $qrActive ? 'is-active' : '' }}">
        <i class="ti ti-qrcode"></i>
        <span>QR</span>
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
