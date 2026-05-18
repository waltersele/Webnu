<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="description" content="Obtén tu carta digital. Cambios ilimitados. Modificacion de productos en tiempo real. Personalizala a tu gusto. ">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.min.css') }}" media="all" />
    <!-- Slick nav CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/slicknav.min.css') }}" media="all" />
    <!-- Iconfont CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/icofont.css') }}" media="all" />
    <!-- Slick CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/slick.css') }}">

    <link rel="stylesheet" type="text/css" href="{{ asset('css/font-awesome.min.css') }}">
    <!-- Owl carousel CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/owl.carousel.css') }}">
    <!-- Popup CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/magnific-popup.css') }}">
    <!-- Switcher CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/switcher-style.css') }}">
    <!-- Animate CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/animate.min.css') }}">
    <!-- Main style CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}" media="all" />
    <!-- Responsive CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/responsive.css') }}" media="all" />
    <!-- Favicon Icon -->
    <link rel="icon" type="image/png" href="{{ asset('img/front/favicon.png') }}" />
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Styles
    <link href="{{ asset('css/app.css') }}" rel="stylesheet"> -->

    @stack('styles')

</head>
<body data-spy="scroll" data-target=".header" data-offset="50">
    @include('layouts.partials.header')
    @yield('content')
    @include('layouts.partials.footer')

    <!-- jquery main JS -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <!-- Bootstrap JS -->
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <!-- Slick nav JS -->
    <script src="{{ asset('js/jquery.slicknav.min.js') }}"></script>
    <!-- Slick JS -->
    <script src="{{ asset('js/slick.min.js') }}"></script>
    <!-- owl carousel JS -->
    <script src="{{ asset('js/owl.carousel.min.js') }}"></script>
    <!-- Popup JS -->
    <script src="{{ asset('js/jquery.magnific-popup.min.js') }}"></script>
    <!-- Counter JS -->
    <script src="{{ asset('js/jquery.counterup.min.js') }}"></script>
    <!-- Counterup waypoints JS -->
    <script src="{{ asset('js/waypoints.min.js') }}"></script>
    <!-- YTPlayer JS -->
    <script src="{{ asset('js/jquery.mb.YTPlayer.min.js') }}"></script>
    <!-- WOW JS -->
    <script src="{{ asset('js/wow-1.3.0.min.js') }}"></script>
    <!-- Switcher JS -->
    <script src="{{ asset('js/switcher.js') }}"></script>
    <!-- main JS -->
    <script src="{{ asset('js/main.js') }}"></script>

    @stack('scripts')
</body>
</html>
