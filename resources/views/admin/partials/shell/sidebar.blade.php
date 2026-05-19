@php
    $currentCompany = null;
    if (!empty($selected_company) && !empty($available_companies)) {
        $currentCompany = $available_companies->firstWhere('id', (int) $selected_company);
    }
@endphp
<aside id="webnu-sidebar" class="webnu-sidebar">
    <a href="{{ route('admin.dashboard') }}" class="webnu-sidebar__brand">
        <img src="{{ asset('adminlte/img/webnu.png') }}" alt="Webnu">
        <div class="webnu-sidebar__brand-text">
            <span class="webnu-sidebar__brand-name">Webnu</span>
            <span class="webnu-sidebar__brand-sub">Panel</span>
        </div>
    </a>

    <nav class="webnu-sidebar__nav">
        <div class="webnu-sidebar__group">
            <span class="webnu-sidebar__group-label">Tu cuenta</span>
            <a href="{{ route('admin.dashboard') }}" class="webnu-nav-item {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Inicio</span>
            </a>
            <a href="{{ route('admin.companies.index') }}" class="webnu-nav-item {{ request()->is('admin/companies*') ? 'is-active' : '' }}">
                <i class="fas fa-store"></i>
                <span>Negocios</span>
            </a>
        </div>

        @if (!empty($selected_company) && !empty($available_companies))
        <div class="webnu-sidebar__group">
            <span class="webnu-sidebar__group-label">Negocio actual</span>
            <div class="webnu-sidebar__company">
                <label for="company_selection">Establecimiento</label>
                <form method="POST" action="{{ route('admin.companies.changecompany', '0') }}" id="company-selection-form">
                    @csrf
                    <select name="company_selection" id="company_selection">
                        @foreach ($available_companies as $company)
                            <option value="{{ $company->id }}" {{ $company->id == $selected_company ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <a href="{{ route('admin.sections.index') }}" class="webnu-nav-item {{ request()->is('admin/sections*') ? 'is-active' : '' }}">
                <i class="fas fa-utensils"></i>
                <span>Carta</span>
            </a>
            @if ($currentCompany)
            <a href="{{ route('see_menu', $currentCompany->slug) }}" class="webnu-nav-item" target="_blank" rel="noopener">
                <i class="fas fa-external-link-alt"></i>
                <span>Ver carta pública</span>
            </a>
            @endif
        </div>
        @endif

        <div class="webnu-sidebar__group">
            <span class="webnu-sidebar__group-label">Conectar</span>
            <a href="{{ route('admin.integrations.index') }}" class="webnu-nav-item {{ request()->is('admin/integrations*') || request()->is('admin/signage*') ? 'is-active' : '' }}">
                <i class="fas fa-plug"></i>
                <span>Integraciones</span>
            </a>
        </div>
    </nav>

    <div class="webnu-sidebar__footer">
        <div class="mb-2 text-truncate">{{ auth()->user()->name }}</div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="webnu-nav-item w-100 border-0 bg-transparent text-left">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar sesión</span>
            </button>
        </form>
    </div>
</aside>

