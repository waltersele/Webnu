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

    $settingsUrl = $hasCompany && $currentCompany
        ? route('admin.companies.edit', ['company' => $currentCompany, 'step' => 'identity'])
        : route('admin.companies.index');

    $cartaUrl = route('admin.companies.index');

    $companiesIndexUrl = route('admin.companies.index');

    $screensActive = request()->is('admin/tvpik*')
        || request()->is('admin/integrations*')
        || request()->is('admin/signage*');

    $canUseTvpik = isset($planFeatures['tvpik']) ? (bool) $planFeatures['tvpik'] : true;
@endphp

<div class="offcanvas offcanvas-start wn-shell-offcanvas" tabindex="-1" id="wnShellNavOffcanvas" aria-labelledby="wnShellNavOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title d-flex align-items-center gap-2" id="wnShellNavOffcanvasLabel">
            <img src="{{ asset('adminlte/img/isotipo-color.png') }}" alt="" width="24" height="24">
            <span>Webnu</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body p-0">
        <nav class="wn-shell-offcanvas-nav" aria-label="Navegación">
            {{-- Mis cartas con subitems --}}
            @if($hasCompany)
                <a href="{{ $cartaUrl }}"
                   class="wn-shell-offcanvas-link wn-shell-offcanvas-link--parent {{ request()->is('admin/companies*') ? 'is-active' : '' }}"
                   data-bs-dismiss="offcanvas">
                    <i class="ti ti-tools-kitchen-2"></i>
                    <span class="flex-grow-1">Mis cartas</span>
                </a>

                @if($available_companies && $available_companies->count())
                    <div class="wn-shell-offcanvas-subnav" role="list">
                        @foreach($available_companies as $cmp)
                            @php
                                $isCurrent = (int) $cmp->id === (int) $companyId;
                                $dotClass = $cmp->enabled ? 'is-on' : 'is-off';
                                $statusLabel = $cmp->enabled ? 'Publicada' : 'Borrador';
                            @endphp
                            @if($isCurrent)
                                <a href="{{ route('admin.sections.index') }}"
                                   class="wn-shell-offcanvas-sublink is-current"
                                   data-bs-dismiss="offcanvas">
                                    <span class="wn-shell-offcanvas-sublink__dot {{ $dotClass }}" aria-hidden="true"></span>
                                    <span>{{ $cmp->name }}</span>
                                    <small class="text-muted ms-auto me-1">{{ $statusLabel }}</small>
                                    <i class="ti ti-check"></i>
                                </a>
                            @else
                                <form method="POST"
                                      action="{{ route('admin.companies.changecompany') }}"
                                      class="wn-shell-offcanvas-sublink__form">
                                    @csrf
                                    <input type="hidden" name="company_selection" value="{{ $cmp->id }}">
                                    <button type="submit" class="wn-shell-offcanvas-sublink">
                                        <span class="wn-shell-offcanvas-sublink__dot {{ $dotClass }}" aria-hidden="true"></span>
                                        <span>{{ $cmp->name }}</span>
                                        <small class="text-muted ms-auto">{{ $statusLabel }}</small>
                                    </button>
                                </form>
                            @endif
                        @endforeach

                        <a href="{{ $companiesIndexUrl }}"
                           class="wn-shell-offcanvas-sublink wn-shell-offcanvas-sublink--add {{ ! $canCreateCompany ? 'is-locked' : '' }}"
                           data-bs-dismiss="offcanvas">
                            <i class="ti {{ $canCreateCompany ? 'ti-plus' : 'ti-crown' }}"></i>
                            <span>{{ $canCreateCompany ? 'Añadir carta' : 'Añadir carta con Pro' }}</span>
                        </a>
                    </div>
                @endif
            @else
                <span class="wn-shell-offcanvas-link is-disabled">
                    <i class="ti ti-tools-kitchen-2"></i>
                    <span>Mis cartas</span>
                </span>
            @endif

            {{-- QR (abre modal) --}}
            @if($hasCompany && $currentCompany)
                <button type="button"
                        class="wn-shell-offcanvas-link wn-shell-offcanvas-link--btn"
                        data-bs-toggle="modal"
                        data-bs-target="#wn-qr-modal"
                        data-bs-dismiss="offcanvas">
                    <i class="ti ti-qrcode"></i>
                    <span class="flex-grow-1">QR</span>
                </button>
            @else
                <span class="wn-shell-offcanvas-link is-disabled">
                    <i class="ti ti-qrcode"></i>
                    <span>QR</span>
                </span>
            @endif

            {{-- Pantallas --}}
            <a href="{{ route('admin.tvpik.index') }}"
               class="wn-shell-offcanvas-link {{ $screensActive ? 'is-active' : '' }} {{ ! $canUseTvpik ? 'is-locked' : '' }}"
               data-bs-dismiss="offcanvas">
                <i class="ti ti-device-tv"></i>
                <span class="flex-grow-1">Pantallas</span>
                @if(! $canUseTvpik)
                    <span class="wn-shell-offcanvas-badge">Plus</span>
                @endif
            </a>

            {{-- Mi negocio --}}
            <a href="{{ $settingsUrl }}"
               class="wn-shell-offcanvas-link {{ request()->is('admin/companies*') ? 'is-active' : '' }}"
               data-bs-dismiss="offcanvas">
                <i class="ti ti-settings"></i>
                <span class="flex-grow-1">Mi negocio</span>
            </a>
        </nav>
    </div>
</div>
