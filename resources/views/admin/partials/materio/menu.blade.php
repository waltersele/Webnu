@php
    $currentCompany = null;
    if (!empty($selected_company) && !empty($available_companies)) {
        $currentCompany = $available_companies->firstWhere('id', (int) $selected_company);
    }
@endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('admin.dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo me-2">
                <img src="{{ \App\PlatformSetting::brandUrl('isotipo') }}" alt="Webnu">
            </span>
            <span class="app-brand-text demo menu-text fw-semibold">Webnu</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="menu-toggle-icon d-xl-inline-block align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1 flex-grow-1">
        <li class="menu-header small">
            <span class="menu-header-text">Tu cuenta</span>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}" class="menu-link">
                <i class="menu-icon icon-base ri ri-home-smile-line"></i>
                <div>Inicio</div>
            </a>
        </li>
        <li class="menu-item {{ request()->is('admin/companies*') ? 'active' : '' }}">
            <a href="{{ route('admin.companies.index') }}" class="menu-link">
                <i class="menu-icon icon-base ri ri-store-2-line"></i>
                <div>Negocios</div>
            </a>
        </li>

        @if (!empty($selected_company))
        <li class="menu-header small mt-3">
            <span class="menu-header-text">Mi carta</span>
        </li>
        <li class="menu-item {{ request()->is('admin/sections*') ? 'active' : '' }}">
            <a href="{{ route('admin.sections.index') }}" class="menu-link">
                <i class="menu-icon icon-base ri ri-restaurant-line"></i>
                <div>Carta</div>
            </a>
        </li>
        @if ($currentCompany)
        <li class="menu-item">
            <a href="{{ route('see_menu', $currentCompany->slug) }}" class="menu-link" target="_blank" rel="noopener">
                <i class="menu-icon icon-base ri ri-external-link-line"></i>
                <div>Ver carta pública</div>
            </a>
        </li>
        @endif
        @endif

        <li class="menu-header small mt-3">
            <span class="menu-header-text">Conectar</span>
        </li>
        <li class="menu-item {{ request()->is('admin/tvpik*') || request()->is('admin/integrations*') || request()->is('admin/signage*') ? 'active' : '' }}">
            <a href="{{ route('admin.tvpik.index') }}" class="menu-link">
                <i class="menu-icon icon-base ri ri-tv-line"></i>
                <div>TV / TVPik</div>
            </a>
        </li>

        @if (auth()->check() && ! auth()->user()->hasActiveSubscription())
        <li class="menu-item {{ request()->routeIs('admin.billing*') ? 'active' : '' }}">
            <a href="{{ route('admin.billing') }}" class="menu-link">
                <i class="menu-icon icon-base ri ri-bank-card-line"></i>
                <div>Suscripción</div>
            </a>
        </li>
        @endif

        @if (auth()->check() && auth()->user()->isSuperAdmin())
        <li class="menu-header small mt-3">
            <span class="menu-header-text">Plataforma</span>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.platform.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.platform.dashboard') }}" class="menu-link">
                <i class="menu-icon icon-base ri ri-dashboard-line"></i>
                <div>Dashboard</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.platform.billing*') ? 'active' : '' }}">
            <a href="{{ route('admin.platform.billing.index') }}" class="menu-link">
                <i class="menu-icon icon-base ri ri-bank-card-line"></i>
                <div>Facturación</div>
            </a>
        </li>
        <li class="menu-item {{ request()->is('admin/platform/users*') ? 'active' : '' }}">
            <a href="{{ route('admin.platform.users.index') }}" class="menu-link">
                <i class="menu-icon icon-base ri ri-team-line"></i>
                <div>Clientes</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.platform.sales*') ? 'active' : '' }}">
            <a href="{{ route('admin.platform.sales.index') }}" class="menu-link">
                <i class="menu-icon icon-base ri ri-briefcase-line"></i>
                <div>Comercial</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.platform.settings*') ? 'active' : '' }}">
            <a href="{{ route('admin.platform.settings') }}" class="menu-link">
                <i class="menu-icon icon-base ri ri-settings-3-line"></i>
                <div>Configuración</div>
            </a>
        </li>
        @endif
    </ul>

    <div class="webnu-menu-footer mt-auto">
        @if (!empty($selected_company) && !empty($available_companies))
        <div class="webnu-menu-company mb-3">
            <label for="company_selection">Negocio</label>
            <form method="POST" action="{{ route('admin.companies.changecompany', '0') }}" id="company-selection-form">
                @csrf
                <select name="company_selection" id="company_selection" class="form-select form-select-sm">
                    @foreach ($available_companies as $company)
                        <option value="{{ $company->id }}" {{ $company->id == $selected_company ? 'selected' : '' }}>{{ $company->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        @endif
        <div class="text-truncate small mb-2 opacity-75">{{ auth()->user()->name }}</div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-light w-100">
                <i class="ri ri-logout-box-r-line me-1"></i> Cerrar sesión
            </button>
        </form>
    </div>
</aside>


