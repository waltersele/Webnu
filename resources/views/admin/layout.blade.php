<!DOCTYPE html>
<html lang="es" class="layout-menu-fixed layout-compact" data-assets-path="{{ asset('materio/') }}/">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>@yield('page_title', 'Panel') — Webnu</title>
    <link rel="icon" type="image/png" href="{{ asset('img/front/favicon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('materio/vendor/fonts/iconify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('materio/vendor/libs/node-waves/node-waves.css') }}">
    <link rel="stylesheet" href="{{ asset('materio/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('materio/css/demo.css') }}">
    <link rel="stylesheet" href="{{ asset('materio/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('materio/css/webnu-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('materio/css/webnu-admin.css') }}">

    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

    <script src="{{ asset('materio/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('materio/js/config.js') }}"></script>
    <script>var baseurl = '{{ url('/') }}/admin/';</script>
    @stack('styles')
</head>
<body>
@include('admin.partials.allergen-sprite')
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('admin.partials.materio.menu')

        <div class="layout-page">
            @include('admin.partials.materio.navbar')

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    @include('admin.partials.materio.flash')

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

<script src="{{ asset('materio/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('materio/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('materio/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('materio/vendor/libs/node-waves/node-waves.js') }}"></script>
<script src="{{ asset('materio/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('materio/vendor/js/menu.js') }}"></script>
<script src="{{ asset('materio/js/main.js') }}"></script>
<script src="{{ asset('materio/js/webnu-admin.js') }}"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
@stack('scripts')
</body>
</html>
