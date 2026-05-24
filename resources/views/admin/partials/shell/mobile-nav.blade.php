@php
    $hasCompany = !empty($selected_company);
    $currentCompany = null;
    if ($hasCompany && !empty($available_companies)) {
        $currentCompany = $available_companies->firstWhere('id', (int) $selected_company);
    }

    $settingsUrl = $hasCompany && $currentCompany
        ? route('admin.companies.edit', ['company' => $currentCompany, 'step' => 'identity'])
        : route('admin.companies.index');

    $qrUrl = $hasCompany && $currentCompany
        ? route('admin.qrgenerator', $currentCompany)
        : route('admin.companies.index');

    $cartaUrl = $hasCompany
        ? route('admin.sections.index')
        : route('admin.companies.index');

    $screensActive = request()->is('admin/tvpik*')
        || request()->is('admin/integrations*')
        || request()->is('admin/signage*')
        || request()->routeIs('admin.dashboard');

    $offcanvasNav = [
        ['label' => 'Pantallas', 'icon' => 'ti-device-tv', 'url' => route('admin.tvpik.index'), 'active' => $screensActive, 'enabled' => true],
        ['label' => 'Carta', 'icon' => 'ti-tools-kitchen-2', 'url' => $cartaUrl, 'active' => request()->is('admin/sections*') || request()->is('admin/products*') || request()->is('admin/menu-scan*'), 'enabled' => $hasCompany],
        ['label' => 'QR', 'icon' => 'ti-qrcode', 'url' => $qrUrl, 'active' => request()->is('admin/qrgenerator*'), 'enabled' => $hasCompany],
        ['label' => 'Mi negocio', 'icon' => 'ti-settings', 'url' => $settingsUrl, 'active' => request()->is('admin/companies*'), 'enabled' => true],
    ];
@endphp

<div class="offcanvas offcanvas-start wn-shell-offcanvas" tabindex="-1" id="wnShellNavOffcanvas" aria-labelledby="wnShellNavOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="wnShellNavOffcanvasLabel">Menú</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body p-0">
        <nav class="wn-shell-offcanvas-nav" aria-label="Navegación">
            @foreach($offcanvasNav as $item)
                @if($item['enabled'])
                    <a href="{{ $item['url'] }}"
                       class="wn-shell-offcanvas-link {{ $item['active'] ? 'is-active' : '' }}"
                       data-bs-dismiss="offcanvas">
                        <i class="ti {{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @else
                    <span class="wn-shell-offcanvas-link is-disabled">
                        <i class="ti {{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </span>
                @endif
            @endforeach
        </nav>
    </div>
</div>
