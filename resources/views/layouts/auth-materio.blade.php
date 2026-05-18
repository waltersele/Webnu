<!DOCTYPE html>
<html lang="es" data-assets-path="{{ asset('materio/') }}/">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Acceso') — {{ config('app.name', 'Webnu') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/front/favicon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('materio/vendor/fonts/iconify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('materio/vendor/libs/node-waves/node-waves.css') }}">
    <link rel="stylesheet" href="{{ asset('materio/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('materio/css/demo.css') }}">
    <link rel="stylesheet" href="{{ asset('materio/vendor/css/pages/page-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('materio/css/webnu-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('materio/css/webnu-auth.css') }}">

    <script src="{{ asset('materio/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('materio/js/config.js') }}"></script>
    @stack('styles')
</head>
<body>
@php
    $coverImage = asset('img/auth/login-cover.jpg');
@endphp

<div class="authentication-wrapper authentication-cover">
    <div class="authentication-inner row m-0">
        <div class="d-none d-lg-flex col-lg-7 col-xl-8 p-0 webnu-auth-cover">
            <div class="webnu-auth-cover__bg" style="background-image: url('{{ $coverImage }}');"></div>
            <div class="webnu-auth-cover__overlay"></div>
            <div class="webnu-auth-cover__content">
                <img src="{{ asset('img/front/logo.png') }}" alt="Webnu" class="webnu-auth-cover__logo">
                <h1 class="webnu-auth-cover__title">Tu carta digital, siempre actualizada</h1>
                <p class="webnu-auth-cover__lead">Gestiona menús, QR, pantallas TVPik y pedidos desde un panel pensado para hostelería.</p>
                <ul class="webnu-auth-cover__features">
                    <li><i class="icon-base ri ri-qr-code-line"></i><span>Códigos QR listos para imprimir</span></li>
                    <li><i class="icon-base ri ri-tv-line"></i><span>Carta en pantallas del local con TVPik</span></li>
                    <li><i class="icon-base ri ri-edit-line"></i><span>Cambios ilimitados en tiempo real</span></li>
                </ul>
            </div>
        </div>

        <div class="d-lg-none col-12 p-0 webnu-auth-cover webnu-auth-cover--mobile">
            <div class="webnu-auth-cover__bg" style="background-image: url('{{ $coverImage }}');"></div>
            <div class="webnu-auth-cover__overlay"></div>
            <div class="webnu-auth-cover__content">
                <img src="{{ asset('img/front/logo.png') }}" alt="Webnu" class="webnu-auth-cover__logo">
                <h1 class="webnu-auth-cover__title">Tu carta digital</h1>
            </div>
        </div>

        <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center justify-content-center webnu-auth-panel p-4 p-sm-5">
            <div class="w-100" style="max-width: 26rem;">
                @yield('content')
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('materio/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('materio/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('materio/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('materio/vendor/libs/node-waves/node-waves.js') }}"></script>
@stack('scripts')
</body>
</html>
