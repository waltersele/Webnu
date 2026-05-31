@php
    $hasCompany = !empty($selected_company);
    $companyId = $hasCompany ? (int) $selected_company : null;
    $currentCompany = null;
    if ($hasCompany && !empty($available_companies)) {
        $currentCompany = $available_companies->firstWhere('id', $companyId);
    }

    $user = auth()->user();
    $planService = app(\App\Services\UserPlanService::class);
    $canCreateCompany = $user ? $planService->canCreateCompany($user) : false;
    $maxCompanies = $user ? $planService->maxCompanies($user) : null;

    $businessUrl = $hasCompany && $currentCompany
        ? route('admin.companies.edit', ['company' => $currentCompany, 'step' => 'identity'])
        : route('admin.companies.index');

    $cartaUrl = route('admin.companies.index');

    $companiesIndexUrl = route('admin.companies.index');

    $canUseTvpik = isset($planFeatures['tvpik']) ? (bool) $planFeatures['tvpik'] : true;
    $tvpikActive = request()->is('admin/tvpik*')
        || request()->is('admin/integrations*')
        || request()->is('admin/signage*');

    $cartaActive = request()->is('admin/companies*')
        || request()->is('admin/sections*')
        || request()->is('admin/products*')
        || request()->is('admin/menu-scan*');

    $companiesActive = request()->is('admin/companies*');

    $menusActive = request()->is('admin/menus*');

    $homeActive = request()->routeIs('admin.dashboard');
@endphp

<nav class="wn-shell-sidebar" aria-label="Navegación principal">
    {{-- Inicio (dashboard) --}}
    <a href="{{ route('admin.dashboard') }}"
       class="wn-shell-nav {{ $homeActive ? 'is-active' : '' }}"
       aria-label="Inicio"
       @if($homeActive) aria-current="page" @endif>
        <span class="wn-shell-nav__icon">
            <i class="ti ti-home"></i>
        </span>
        <span class="wn-shell-nav__label">Inicio</span>
    </a>

    {{-- Mis cartas (parent con subitems) --}}
    @if($hasCompany)
        <div class="wn-shell-nav-group {{ $cartaActive ? 'is-active-group' : '' }}">
            <a href="{{ $cartaUrl }}"
               class="wn-shell-nav wn-shell-nav--parent {{ $cartaActive && empty($companiesActive) ? 'is-active' : '' }}"
               aria-label="Mis cartas"
               @if($cartaActive) aria-current="page" @endif>
                <span class="wn-shell-nav__icon">
                    <i class="ti ti-tools-kitchen-2"></i>
                </span>
                <span class="wn-shell-nav__label">Mis cartas</span>
            </a>

            @if($available_companies && $available_companies->count())
                <div class="wn-shell-subnav" role="list" aria-label="Cartas disponibles">
                    @foreach($available_companies as $cmp)
                        @php
                            $isCurrent = (int) $cmp->id === (int) $companyId;
                        @endphp
                        @php
                            $dotClass = $cmp->enabled ? 'is-on' : 'is-off';
                            $statusLabel = $cmp->enabled ? 'publicada' : 'borrador';
                        @endphp
                        @if($isCurrent)
                            <a href="{{ route('admin.sections.index') }}"
                               class="wn-shell-subnav__item is-current"
                               title="{{ $cmp->name }} ({{ $statusLabel }})"
                               aria-current="true">
                                <span class="wn-shell-subnav__dot {{ $dotClass }}" aria-hidden="true"></span>
                                <span class="wn-shell-subnav__name">{{ $cmp->name }}</span>
                            </a>
                        @else
                            <form method="POST"
                                  action="{{ route('admin.companies.changecompany') }}"
                                  class="wn-shell-subnav__form">
                                @csrf
                                <input type="hidden" name="company_selection" value="{{ $cmp->id }}">
                                <input type="hidden" name="redirect_after" value="/admin/sections">
                                <button type="submit"
                                        class="wn-shell-subnav__item"
                                        title="Cambiar a {{ $cmp->name }} ({{ $statusLabel }})">
                                    <span class="wn-shell-subnav__dot {{ $dotClass }}" aria-hidden="true"></span>
                                    <span class="wn-shell-subnav__name">{{ $cmp->name }}</span>
                                </button>
                            </form>
                        @endif
                    @endforeach

                    <a href="{{ $companiesIndexUrl }}"
                       class="wn-shell-subnav__add {{ ! $canCreateCompany ? 'is-locked' : '' }}"
                       title="{{ $canCreateCompany ? 'Añadir una carta' : 'Mejora tu plan para añadir más cartas' }}">
                        <i class="ti {{ $canCreateCompany ? 'ti-plus' : 'ti-crown' }}"></i>
                        <span>{{ $canCreateCompany ? 'Añadir carta' : 'Añadir carta con Pro' }}</span>
                    </a>
                </div>
            @endif
        </div>
    @else
        <span class="wn-shell-nav is-disabled" title="Crea un negocio primero" aria-disabled="true">
            <span class="wn-shell-nav__icon">
                <i class="ti ti-tools-kitchen-2"></i>
            </span>
            <span class="wn-shell-nav__label">Mis cartas</span>
        </span>
    @endif

    {{-- Menús (parent con subitems) --}}
    @if($hasCompany)
        @php
            $menusEditing = request()->is('admin/menus/*/edit');
            $menusIndexActive = $menusActive && ! $menusEditing;
        @endphp
        <div class="wn-shell-nav-group {{ $menusActive ? 'is-active-group' : '' }}">
            <a href="{{ route('admin.menus.index') }}"
               class="wn-shell-nav wn-shell-nav--parent {{ $menusIndexActive ? 'is-active' : '' }}"
               aria-label="Menús"
               @if($menusActive) aria-current="page" @endif>
                <span class="wn-shell-nav__icon">
                    <i class="ti ti-bowl-spoon"></i>
                </span>
                <span class="wn-shell-nav__label">Menús</span>
            </a>

            @if(!empty($available_menus) && $available_menus->count())
                <div class="wn-shell-subnav" role="list" aria-label="Menús disponibles">
                    @foreach($available_menus as $m)
                        @php
                            $isCurrentMenu = request()->is('admin/menus/' . $m->id . '/edit');
                            $menuDotClass = $m->enabled ? 'is-on' : 'is-off';
                            $menuStatusLabel = $m->enabled ? 'activo' : 'borrador';
                        @endphp
                        <a href="{{ route('admin.menus.edit', $m->id) }}"
                           class="wn-shell-subnav__item {{ $isCurrentMenu ? 'is-current' : '' }}"
                           title="{{ $m->name }} ({{ $menuStatusLabel }})"
                           @if($isCurrentMenu) aria-current="true" @endif>
                            <span class="wn-shell-subnav__dot {{ $menuDotClass }}" aria-hidden="true"></span>
                            <span class="wn-shell-subnav__name">{{ $m->name }}</span>
                        </a>
                    @endforeach
                    <a href="{{ route('admin.menus.index') }}#new"
                       class="wn-shell-subnav__add"
                       title="Crear un menú">
                        <i class="ti ti-plus"></i>
                        <span>Crear menú</span>
                    </a>
                </div>
            @endif
        </div>
    @else
        <span class="wn-shell-nav is-disabled" title="Crea un negocio primero" aria-disabled="true">
            <span class="wn-shell-nav__icon">
                <i class="ti ti-bowl-spoon"></i>
            </span>
            <span class="wn-shell-nav__label">Menús</span>
        </span>
    @endif

    {{-- QR (abre modal) --}}
    @if($hasCompany && $currentCompany)
        <button type="button"
                class="wn-shell-nav wn-shell-nav--btn"
                data-bs-toggle="modal"
                data-bs-target="#wn-qr-modal"
                aria-label="Ver código QR">
            <span class="wn-shell-nav__icon">
                <i class="ti ti-qrcode"></i>
            </span>
            <span class="wn-shell-nav__label">QR</span>
        </button>
    @else
        <span class="wn-shell-nav is-disabled" title="Crea un negocio primero" aria-disabled="true">
            <span class="wn-shell-nav__icon">
                <i class="ti ti-qrcode"></i>
            </span>
            <span class="wn-shell-nav__label">QR</span>
        </span>
    @endif

    {{-- Pantallas --}}
    @if($canUseTvpik)
        <a href="{{ route('admin.tvpik.index') }}"
           class="wn-shell-nav {{ $tvpikActive ? 'is-active' : '' }}"
           aria-label="Pantallas"
           @if($tvpikActive) aria-current="page" @endif>
            <span class="wn-shell-nav__icon">
                <i class="ti ti-device-tv"></i>
            </span>
            <span class="wn-shell-nav__label">Pantallas</span>
        </a>
    @else
        <a href="{{ route('admin.tvpik.index') }}"
           class="wn-shell-nav is-locked"
           aria-label="Pantallas (disponible con Plus)"
           title="Disponible con el plan Plus">
            <span class="wn-shell-nav__icon">
                <i class="ti ti-device-tv"></i>
                <span class="wn-shell-nav__lock" aria-hidden="true"><i class="ti ti-lock"></i></span>
            </span>
            <span class="wn-shell-nav__label">Pantallas</span>
            <span class="wn-shell-nav__badge">Plus</span>
        </a>
    @endif

    {{-- Mi negocio --}}
    <a href="{{ $businessUrl }}"
       class="wn-shell-nav {{ $companiesActive ? 'is-active' : '' }}"
       aria-label="Mi negocio"
       @if($companiesActive) aria-current="page" @endif>
        <span class="wn-shell-nav__icon">
            <i class="ti ti-building-store"></i>
        </span>
        <span class="wn-shell-nav__label">Mi negocio</span>
    </a>

    <div class="wn-shell-sidebar__spacer" aria-hidden="true"></div>

    {{-- Footer: Ajustes y Ayuda --}}
    @php
        $settingsActive = request()->is('admin/settings*') || request()->is('admin/billing*');
    @endphp
    <div class="wn-shell-sidebar__footer">
        <a href="{{ route('admin.settings') }}"
           class="wn-shell-nav {{ $settingsActive ? 'is-active' : '' }}"
           aria-label="Ajustes"
           @if($settingsActive) aria-current="page" @endif>
            <span class="wn-shell-nav__icon">
                <i class="ti ti-settings"></i>
            </span>
            <span class="wn-shell-nav__label">Ajustes</span>
        </a>
        <a href="{{ url('/') }}#faq"
           class="wn-shell-nav"
           target="_blank"
           rel="noopener"
           aria-label="Ayuda y preguntas frecuentes">
            <span class="wn-shell-nav__icon">
                <i class="ti ti-help"></i>
            </span>
            <span class="wn-shell-nav__label">Ayuda</span>
        </a>
    </div>
</nav>
