@php
    $currentCompany = null;
    if (!empty($selected_company) && !empty($available_companies)) {
        $currentCompany = $available_companies->firstWhere('id', (int) $selected_company);
    }
@endphp
<aside class="webnu-sidebar" aria-label="Navegación principal">
    <a href="{{ route('admin.dashboard') }}" class="webnu-sidebar__brand">
        <img src="{{ \App\PlatformSetting::brandUrl('isotipo') }}" alt="Webnu" width="32" height="32">
        <span>Webnu<span class="text-primary">.es</span></span>
    </a>

    @if (!empty($selected_company) && !empty($available_companies))
    <div class="webnu-sidebar__company">
        <label for="company_selection_desktop">Establecimiento</label>
        <form method="POST" action="{{ route('admin.companies.changecompany') }}" id="company-selection-form-desktop">
            @csrf
            <select name="company_selection" id="company_selection_desktop" class="form-select form-select-sm">
                @foreach ($available_companies as $company)
                    <option value="{{ $company->id }}" {{ $company->id == $selected_company ? 'selected' : '' }}>{{ $company->name }}</option>
                @endforeach
            </select>
        </form>
    </div>
    @endif

    <nav class="webnu-sidebar__nav">
        <a href="{{ route('admin.dashboard') }}" class="webnu-sidebar__link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
            <i class="fas fa-home"></i> Panel de control
        </a>
        <a href="{{ route('admin.companies.index') }}" class="webnu-sidebar__link {{ request()->is('admin/companies*') ? 'is-active' : '' }}">
            <i class="fas fa-store"></i> Negocios
        </a>
        @if ($hasCompany = !empty($selected_company))
        <a href="{{ route('admin.sections.index') }}" class="webnu-sidebar__link {{ request()->is('admin/sections*') ? 'is-active' : '' }}">
            <i class="fas fa-utensils"></i> Carta
        </a>
        @if ($currentCompany)
        <a href="{{ $currentCompany->publicUrl() }}" class="webnu-sidebar__link" target="_blank" rel="noopener">
            <i class="fas fa-external-link-alt"></i> Ver carta pública
        </a>
        @endif
        @endif
        <a href="{{ route('admin.integrations.index') }}" class="webnu-sidebar__link {{ request()->is('admin/integrations*') || request()->is('admin/signage*') ? 'is-active' : '' }}">
            <i class="fas fa-plug"></i> Integraciones
        </a>
    </nav>

    <div class="webnu-sidebar__footer">
        <div class="webnu-sidebar__user">{{ auth()->user()->name }}</div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="webnu-sidebar__logout">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </button>
        </form>
    </div>
</aside>

