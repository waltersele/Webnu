<!DOCTYPE html>
@php
    $usePlatformShell = auth()->check() && auth()->user()->isSuperAdmin() && request()->is('admin/platform*');
    $useClientShell = auth()->check() && !$usePlatformShell;
    $isDashboard = request()->routeIs('admin.dashboard');
@endphp
<html lang="es" class="{{ $useClientShell ? 'wn-shell-body' : 'layout-menu-fixed layout-compact' }}" data-assets-path="{{ asset('materio/') }}/">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>@yield('page_title', 'Panel') — Webnu</title>
    <link rel="icon" type="image/png" href="{{ \App\PlatformSetting::brandUrl('favicon') }}">
    @if($useClientShell)
        <link rel="manifest" href="{{ asset('manifest-admin.webmanifest') }}">
        <meta name="theme-color" content="#378add">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="Webnu">
        <link rel="apple-touch-icon" href="{{ asset('img/pwa/icon-192.png') }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @if($useClientShell)
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
        <link rel="stylesheet" href="{{ asset('materio/vendor/css/core.css') }}">
        <link rel="stylesheet" href="{{ asset('materio/css/webnu-theme.css') }}">
        <link rel="stylesheet" href="{{ asset('materio/css/webnu-admin-shell.css') }}">
        <link rel="stylesheet" href="{{ asset('materio/css/webnu-admin.css') }}">
        <link rel="stylesheet" href="{{ asset('materio/css/webnu-dashboard.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('materio/vendor/fonts/iconify-icons.css') }}">
        <link rel="stylesheet" href="{{ asset('materio/vendor/libs/node-waves/node-waves.css') }}">
        <link rel="stylesheet" href="{{ asset('materio/vendor/css/core.css') }}">
        <link rel="stylesheet" href="{{ asset('materio/css/demo.css') }}">
        <link rel="stylesheet" href="{{ asset('materio/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}">
        <link rel="stylesheet" href="{{ asset('materio/css/webnu-theme.css') }}">
        <link rel="stylesheet" href="{{ asset('materio/css/webnu-admin.css') }}">
    @endif

    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

    <script src="{{ asset('materio/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('materio/js/config.js') }}"></script>
    <script>var baseurl = '{{ url('/') }}/admin/';</script>
    @stack('styles')
</head>
<body>
@include('admin.partials.allergen-sprite')

@if(session()->has('impersonator_id'))
    <div class="wn-impersonate-banner" style="position:sticky;top:0;z-index:1080;background:linear-gradient(90deg,#fbbf24,#f59e0b);color:#1f2937;padding:.5rem 1rem;text-align:center;font-weight:600;box-shadow:0 2px 8px rgba(0,0,0,.12);display:flex;justify-content:center;align-items:center;gap:.75rem;flex-wrap:wrap;">
        <span><i class="ri-user-shared-line"></i> Estás operando como <strong>{{ auth()->user()->email ?? '—' }}</strong>.</span>
        <form method="POST" action="{{ route('admin.platform.users.stop-impersonating') }}" style="margin:0;">
            @csrf
            <button type="submit" class="btn btn-sm btn-dark" style="padding:.25rem .75rem;">Volver a mi cuenta</button>
        </form>
    </div>
@endif

@if($useClientShell)
<div class="wn-shell-app">
    @include('admin.partials.shell.topbar')
    @include('admin.partials.shell.sidebar')
    @include('admin.partials.shell.mobile-nav')

    <main class="wn-shell-main {{ $isDashboard ? 'wn-shell-main--dashboard' : '' }}">
        @include('admin.partials.materio.flash')
        @php
            $hideTrialBanner = $isDashboard || !empty($planPresentation['trial_active']);
        @endphp
        @unless($hideTrialBanner)
            @include('admin.partials.plan-trial-banner')
        @endunless

        @hasSection('page_title')
            @if(!$isDashboard)
            <div class="wn-shell-page-header">
                <h1>@yield('page_title')</h1>
                @hasSection('page_subtitle')
                    <p>@yield('page_subtitle')</p>
                @endif
                @hasSection('page_actions')
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        @yield('page_actions')
                    </div>
                @endif
            </div>
            @endif
        @endif

        @yield('content')
    </main>

    @include('admin.partials.app.bottom-nav')
</div>
@else
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('admin.partials.materio.menu')

        <div class="layout-page">
            @include('admin.partials.materio.navbar')

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    @include('admin.partials.materio.flash')
                    @include('admin.partials.plan-trial-banner')

                    @hasSection('page_title')
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                        <div>
                            <h4 class="mb-1 fw-semibold">@yield('page_title')</h4>
                            @hasSection('page_subtitle')
                                <p class="text-muted mb-0">@yield('page_subtitle')</p>
                            @endif
                        </div>
                        @hasSection('page_actions')
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                @yield('page_actions')
                            </div>
                        @endif
                    </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <div class="layout-overlay layout-menu-toggle"></div>
</div>
@endif

<script src="{{ asset('materio/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('materio/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('materio/vendor/js/bootstrap.js') }}"></script>
@if(!$useClientShell)
<script src="{{ asset('materio/vendor/libs/node-waves/node-waves.js') }}"></script>
<script src="{{ asset('materio/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('materio/vendor/js/menu.js') }}"></script>
<script src="{{ asset('materio/js/main.js') }}"></script>
@endif
<script src="{{ asset('materio/js/webnu-admin.js') }}"></script>
@if($useClientShell && auth()->check())
    @php
        $wnUpgradeTriggers = $upgradeTriggers ?? [];
    @endphp
    <script>
        window.WebnuUpgradeTriggers = @json([
            'copy' => $wnUpgradeTriggers['copy'] ?? [],
            'billing_url' => $wnUpgradeTriggers['billing_url'] ?? route('admin.settings'),
        ]);
    </script>
    @include('admin.partials.plan-upgrade-popover')
    <script src="{{ asset('materio/js/webnu-upgrade-triggers.js') }}"></script>
@endif
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
@php
    $shareMenuCompany = null;
    if ($useClientShell && !empty($selected_company) && !empty($available_companies)) {
        $shareMenuCompany = $available_companies->firstWhere('id', (int) $selected_company);
    }
@endphp
@if($shareMenuCompany)
    @include('admin.partials.share-menu-modal', ['company' => $shareMenuCompany])
@endif
@stack('scripts')
@if($useClientShell)
<script src="{{ asset('js/admin-share-menu.js') }}"></script>
<script src="{{ asset('js/webnu-pwa-install.js') }}"></script>
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('{{ asset('sw-admin.js') }}', { scope: '/admin/' }).catch(function () {});
    });
}
</script>
@endif
</body>
</html>
