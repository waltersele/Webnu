<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Webnu — Carta digital para restaurantes. Cambios ilimitados, QR al instante, 30 días gratis.">
    <title>Webnu — Vista previa landing</title>
    <link rel="icon" type="image/png" href="{{ asset('img/front/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>
    <div id="webnu-landing-root"></div>
    <script>window.WEBNU_LANDING = @json($landingPayload);</script>
    <script src="{{ asset('js/landing.js') }}"></script>
    @include('partials.subscription-stripe-scripts', ['formId' => 'subscription-form'])
</body>
</html>
