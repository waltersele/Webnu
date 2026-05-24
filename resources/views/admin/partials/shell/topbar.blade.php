@php
    $user = auth()->user();
    $initial = $user ? strtoupper(substr($user->name, 0, 1)) : '?';
    $hasCompany = !empty($selected_company) && !empty($available_companies);
    $currentCompany = $hasCompany
        ? $available_companies->firstWhere('id', (int) $selected_company)
        : null;
    $planPresentation = $planPresentation ?? app(\App\Services\UserPlanService::class)->planPresentation($user);
    $billingUrl = ($planFeatures['billing_url'] ?? null) ?: route('admin.settings');
    $showTrial = !empty($planPresentation['trial_active']) && ($planPresentation['trial_days_remaining'] ?? null) !== null;
@endphp

<header class="wn-shell-topbar">
    <a href="{{ route('admin.tvpik.index') }}" class="wn-shell-topbar__logo">
        <span class="wn-shell-topbar__logo-dot" aria-hidden="true"></span>
        <span>Webnu</span>
    </a>

    <div class="wn-shell-topbar__center">
        @if($hasCompany && $currentCompany)
            @if($available_companies->count() > 1)
                <form method="POST" action="{{ route('admin.companies.changecompany', '0') }}" class="wn-shell-business-form" id="wn-topbar-company-form">
                    @csrf
                    <label class="visually-hidden" for="wn-topbar-company-select">Negocio activo</label>
                    <select name="company_selection" id="wn-topbar-company-select" class="wn-shell-business-select" onchange="this.form.submit()">
                        @foreach($available_companies as $company)
                            <option value="{{ $company->id }}" {{ (int) $company->id === (int) $selected_company ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @else
                <div class="wn-shell-business-pill">
                    <span class="wn-shell-business-pill__dot" aria-hidden="true"></span>
                    {{ $currentCompany->name }}
                </div>
            @endif
            <span class="wn-shell-topbar__url d-none d-md-inline">webnu.es/carta/{{ $currentCompany->slug }}</span>
            <button type="button"
                    class="wn-shell-topbar__share-btn d-none d-md-inline-flex"
                    data-bs-toggle="modal"
                    data-bs-target="#modal-share-menu"
                    title="Compartir carta">
                <i class="ti ti-share" aria-hidden="true"></i>
                <span class="visually-hidden">Compartir carta</span>
            </button>
        @else
            <span class="wn-shell-topbar__hint">Sin negocio activo</span>
        @endif
    </div>

    <div class="wn-shell-topbar__right">
        @if($showTrial)
            <a href="{{ $billingUrl }}" class="wn-shell-trial-badge d-none d-sm-inline-flex text-decoration-none" title="Gestionar suscripción">
                <i class="ti ti-clock"></i>
                {{ $planPresentation['trial_days_remaining'] }} {{ $planPresentation['trial_days_remaining'] === 1 ? 'día' : 'días' }} gratis
            </a>
        @endif
        @if($user && !$user->hasActiveSubscription())
            <a href="{{ $billingUrl }}" class="wn-shell-topbar__plan-btn d-none d-md-inline-flex">
                {{ $showTrial ? 'Gestionar plan' : 'Activar plan' }}
            </a>
        @endif
        <div class="dropdown d-none" id="wn-pwa-topbar-wrap" data-pwa-topbar-wrap>
            <button type="button"
                    class="wn-shell-topbar__pwa-btn"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    title="Instalar Webnu como aplicación">
                <i class="ti ti-download" aria-hidden="true"></i>
                <span class="d-none d-md-inline ms-1">Instalar app</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end p-0 border-0 shadow">
                @include('admin.partials.pwa-install', ['variant' => 'dropdown'])
            </div>
        </div>
        <button type="button" class="wn-shell-topbar__menu-btn d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#wnShellNavOffcanvas" aria-label="Abrir menú">
            <i class="ti ti-menu-2"></i>
        </button>
        <div class="dropdown">
            <button type="button" class="wn-shell-avatar" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Cuenta">
                {{ $initial }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li class="px-3 py-2">
                    <div class="fw-medium">{{ $user->name ?? '' }}</div>
                    <div class="small text-muted">{{ $user->email ?? '' }}</div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="{{ $billingUrl }}">
                        <i class="ti ti-settings me-2"></i> Configuración
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="ti ti-logout me-2"></i> Cerrar sesión
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
