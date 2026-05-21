@php
    $hasCompany = !empty($selected_company);
    $companyId = $hasCompany ? (int) $selected_company : null;
    $currentCompany = null;
    if ($hasCompany && !empty($available_companies)) {
        $currentCompany = $available_companies->firstWhere('id', $companyId);
    }

    $businessUrl = $hasCompany && $currentCompany
        ? route('admin.companies.edit', ['company' => $currentCompany, 'step' => 'identity'])
        : route('admin.companies.index');

    $qrUrl = $hasCompany && $currentCompany
        ? route('admin.qrgenerator', $currentCompany)
        : route('admin.companies.index');

    $cartaUrl = $hasCompany
        ? route('admin.sections.index')
        : route('admin.companies.index');

    $navItems = [
        ['key' => 'home', 'label' => 'Inicio', 'icon' => 'ti-home', 'url' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard'), 'enabled' => true],
        ['key' => 'menu', 'label' => 'Carta', 'icon' => 'ti-tools-kitchen-2', 'url' => $cartaUrl, 'active' => request()->is('admin/sections*') || request()->is('admin/products*') || request()->is('admin/menu-scan*'), 'enabled' => $hasCompany],
        ['key' => 'qr', 'label' => 'QR', 'icon' => 'ti-qrcode', 'url' => $qrUrl, 'active' => request()->is('admin/qrgenerator*'), 'enabled' => $hasCompany],
        ['key' => 'tv', 'label' => 'TV', 'icon' => 'ti-device-tv', 'url' => route('admin.tvpik.index'), 'active' => request()->is('admin/tvpik*') || request()->is('admin/integrations*') || request()->is('admin/signage*'), 'enabled' => true],
        ['key' => 'business', 'label' => 'Mi negocio', 'icon' => 'ti-settings', 'url' => $businessUrl, 'active' => request()->is('admin/companies*') && !request()->routeIs('admin.dashboard'), 'enabled' => true, 'bottom' => true],
    ];
@endphp

<nav class="wn-shell-sidebar" aria-label="Navegación principal">
    @foreach($navItems as $item)
        @if(!empty($item['bottom']))
            <div class="wn-shell-sidebar__spacer" aria-hidden="true"></div>
        @endif
        @if($item['enabled'])
            <a href="{{ $item['url'] }}"
               class="wn-shell-nav {{ $item['active'] ? 'is-active' : '' }}"
               aria-label="{{ $item['label'] }}"
               @if($item['active']) aria-current="page" @endif>
                <i class="ti {{ $item['icon'] }}"></i>
                <span class="wn-shell-nav__label">{{ $item['label'] }}</span>
            </a>
        @else
            <span class="wn-shell-nav is-disabled" title="Crea un negocio primero" aria-disabled="true">
                <i class="ti {{ $item['icon'] }}"></i>
                <span class="wn-shell-nav__label">{{ $item['label'] }}</span>
            </span>
        @endif
    @endforeach
</nav>
