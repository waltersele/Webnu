@php
    $hasCompany = !empty($selected_company);
    $menuUrl = $hasCompany ? route('admin.sections.index') : route('admin.companies.index');
    $menuActive = request()->is('admin/sections*');
    $homeActive = request()->routeIs('admin.dashboard');
    $moreActive = request()->is('admin/integrations*') || request()->is('admin/signage*') || request()->is('admin/companies*');
@endphp
<nav class="webnu-bottomnav" aria-label="Navegación principal">
    <a href="{{ route('admin.dashboard') }}" class="webnu-bottomnav__item {{ $homeActive ? 'is-active' : '' }}">
        <i class="fas fa-home"></i>
        <span>Inicio</span>
    </a>
    <a href="{{ $menuUrl }}" class="webnu-bottomnav__item {{ $menuActive ? 'is-active' : '' }}">
        <i class="fas fa-utensils"></i>
        <span>Carta</span>
    </a>
    <button type="button" class="webnu-bottomnav__item" id="webnu-orders-soon" title="Próximamente">
        <i class="fas fa-receipt"></i>
        <span>Pedidos</span>
    </button>
    <button type="button"
            class="webnu-bottomnav__item {{ $moreActive ? 'is-active' : '' }}"
            data-bs-toggle="offcanvas"
            data-bs-target="#webnuMorePanel"
            aria-label="Más opciones">
        <i class="fas fa-ellipsis-h"></i>
        <span>Más</span>
    </button>
</nav>
