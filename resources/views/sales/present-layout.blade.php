<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Presentar') — Webnu Comercial</title>
    <link rel="stylesheet" href="{{ asset('css/webnu-sales.css') }}">
    @stack('styles')
</head>
<body class="wn-sales-body wn-sales-present-body">
    @yield('content')
    @stack('scripts')
</body>
</html>
