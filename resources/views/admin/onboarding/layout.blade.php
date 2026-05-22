<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configura tu carta — Webnu</title>
    <link rel="icon" type="image/png" href="{{ \App\PlatformSetting::brandUrl('favicon') }}">
    <link rel="manifest" href="{{ asset('manifest-admin.webmanifest') }}">
    <meta name="theme-color" content="#378add">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Webnu">
    <link rel="apple-touch-icon" href="{{ asset('img/pwa/icon-192.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css">
    <link rel="stylesheet" href="{{ asset('css/webnu-onboarding.css') }}">
</head>
<body class="wn-onb-body">
@yield('content')
<script src="{{ asset('js/webnu-onboarding.js') }}"></script>
<script src="{{ asset('js/webnu-pwa-install.js') }}"></script>
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('{{ asset('sw-admin.js') }}', { scope: '/admin/' }).catch(function () {});
    });
}
</script>
@stack('scripts')
</body>
</html>
