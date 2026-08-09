@php
    $measurementService = app(\App\Services\Platform\MeasurementSettingsService::class);
    $measurementConfig = $measurementService->publicConfig(
        session('measurement_event')
    );
    $siteVerification = $measurementService->googleSiteVerification();
    $brand = $measurementConfig['brand'] ?? 'webnu';
    $measurementJsPath = public_path('js/measurement.js');
    $measurementCssPath = public_path('css/measurement-consent.css');
    $measurementJsVersion = is_file($measurementJsPath) ? filemtime($measurementJsPath) : time();
    $measurementCssVersion = is_file($measurementCssPath) ? filemtime($measurementCssPath) : time();
    $measurementLabels = [
        'title' => 'Cookies y medición',
        'description' => 'Usamos cookies necesarias para el funcionamiento del sitio. Con tu permiso, también usamos cookies de analítica y marketing para mejorar Webnu.',
        'exemptNote' => 'La medición de audiencia agregada (sin cookies ni identificadores) funciona siempre de forma anónima. Más detalles en la política de privacidad.',
        'necessary' => 'Necesarias',
        'analytics' => 'Analítica',
        'marketing' => 'Marketing',
        'accept' => 'Aceptar todas',
        'acceptAll' => 'Aceptar todas',
        'save' => 'Guardar selección',
        'reject' => 'Rechazar',
        'privacyLink' => 'Política de privacidad',
        'privacyUrl' => route('legal.privacy'),
        'manage' => 'Gestionar cookies',
    ];
@endphp
@if ($siteVerification)
    <meta name="google-site-verification" content="{{ $siteVerification }}">
@endif
@if (!empty($measurementConfig['enabled']))
    <link rel="stylesheet" href="{{ asset('css/measurement-consent.css') }}?v={{ $measurementCssVersion }}" />
    <script type="application/json" id="{{ $brand }}-measurement-config">{!! json_encode($measurementConfig, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    <script type="application/json" id="{{ $brand }}-measurement-labels">{!! json_encode($measurementLabels, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    <script src="{{ asset('js/measurement.js') }}?v={{ $measurementJsVersion }}" defer></script>
@endif
