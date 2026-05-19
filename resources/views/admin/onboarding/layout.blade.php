<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configura tu carta — Webnu</title>
    <link rel="icon" type="image/png" href="{{ asset('img/front/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css">
    <link rel="stylesheet" href="{{ asset('css/webnu-onboarding.css') }}">
</head>
<body class="wn-onb-body">
@yield('content')
<script src="{{ asset('js/webnu-onboarding.js') }}"></script>
@stack('scripts')
</body>
</html>
