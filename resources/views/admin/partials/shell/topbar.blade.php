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
    $logoHomeUrl = $hasCompany ? route('admin.sections.index') : route('admin.companies.index');
    $planKey = $planPresentation['key'] ?? 'free';
    $planLabel = $planPresentation['label'] ?? 'Gratis';
    $planBadgeClass = match($planKey) {
        'plus'      => 'bg-label-primary',
        'unlimited' => 'bg-label-success',
        default     => 'bg-label-secondary',
    };
    $hasActivePaidPlan = $user && $user->hasActiveSubscription();
@endphp

<header class="wn-shell-topbar">
    <a href="{{ $logoHomeUrl }}" class="wn-shell-topbar__logo" aria-label="Ir a Mi carta">
        <img src="{{ asset('adminlte/img/isotipo-color.png') }}"
             alt=""
             class="wn-shell-logo wn-shell-logo--mark d-md-none"
             width="28" height="28">
        <img src="{{ asset('adminlte/img/logo-color.png') }}"
             alt="Webnu"
             class="wn-shell-logo wn-shell-logo--wordmark d-none d-md-inline">
    </a>

    <div class="wn-shell-topbar__center">
        @if($hasCompany && $currentCompany)
            @if($available_companies->count() > 1)
                <form method="POST" action="{{ route('admin.companies.changecompany') }}" class="wn-shell-business-form" id="wn-topbar-company-form">
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
            <a href="{{ $currentCompany->publicUrl() }}"
               target="_blank"
               rel="noopener"
               class="wn-shell-topbar__url d-none d-md-inline-flex"
               title="Abrir carta pública">
                <i class="ti ti-external-link" aria-hidden="true"></i>
                <span>webnu.es/{{ $currentCompany->publicPath() }}</span>
            </a>
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
            <ul class="dropdown-menu dropdown-menu-end" style="min-width:220px">
                <li class="px-3 pt-2 pb-1">
                    <div class="fw-medium">{{ $user->name ?? '' }}</div>
                    <div class="small text-muted mb-2">{{ $user->email ?? '' }}</div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge {{ $planBadgeClass }}">{{ $planLabel }}</span>
                        @if($showTrial)
                            <small class="text-muted">
                                {{ $planPresentation['trial_days_remaining'] }} {{ ($planPresentation['trial_days_remaining'] ?? 0) === 1 ? 'día' : 'días' }} restantes
                            </small>
                        @endif
                    </div>
                    @if(! $hasActivePaidPlan)
                        <a href="{{ $billingUrl }}" class="d-block mt-2 small fw-medium text-primary text-decoration-none">
                            Mejorar plan <i class="ti ti-arrow-right" style="font-size:12px"></i>
                        </a>
                    @endif
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
