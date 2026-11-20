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
        'description' => 'Usamos cookies necesarias y de analítica para el funcionamiento y la mejora de Webnu. Las cookies de marketing son opcionales y puedes rechazarlas.',
        'exemptNote' => 'La medición de audiencia agregada (sin cookies ni identificadores) funciona siempre de forma anónima. Más detalles en la política de privacidad.',
        'necessary' => 'Necesarias',
        'analytics' => 'Analítica (obligatoria)',
        'marketing' => 'Marketing (opcional)',
        'accept' => 'Aceptar',
        'acceptAll' => 'Aceptar todas',
        'save' => 'Guardar selección',
        'reject' => 'Rechazar opcionales',
        'privacyLink' => 'Política de privacidad',
        'privacyUrl' => route('legal.privacy'),
        'manage' => 'Gestionar cookies',
    ];
@endphp
@if ($siteVerification)
    <meta name="google-site-verification" content="{{ $siteVerification }}">
@endif
@if (!empty($measurementConfig['enabled']))
    @php
        $gtagId = $measurementConfig['consented']['gtagId'] ?? null;
        $gtmId = $measurementConfig['consented']['gtmId'] ?? null;
        $loadGoogleBeforeConsent = ! empty($measurementConfig['loadGoogleBeforeConsent']);
    @endphp
    {{-- Snippet estático: el escáner de GA4/Tag Assistant no ejecuta JS diferido y no ve el ID solo en JSON. --}}
    @if ($gtagId && ! $gtmId)
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('consent', 'default', {
                ad_storage: 'denied',
                ad_user_data: 'denied',
                ad_personalization: 'denied',
                analytics_storage: 'denied',
                functionality_storage: 'granted',
                security_storage: 'granted',
                wait_for_update: 500
            });
            gtag('set', 'url_passthrough', true);
            gtag('set', 'ads_data_redaction', true);
            gtag('js', new Date());
            gtag('config', @json($gtagId), { anonymize_ip: true });
        </script>
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gtagId }}"></script>
    @elseif ($gtmId && $loadGoogleBeforeConsent)
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('consent', 'default', {
                ad_storage: 'denied',
                ad_user_data: 'denied',
                ad_personalization: 'denied',
                analytics_storage: 'denied',
                functionality_storage: 'granted',
                security_storage: 'granted',
                wait_for_update: 500
            });
            gtag('set', 'url_passthrough', true);
            gtag('set', 'ads_data_redaction', true);
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer',@json($gtmId));
        </script>
    @endif
    <link rel="stylesheet" href="{{ asset('css/measurement-consent.css') }}?v={{ $measurementCssVersion }}" />
    <script type="application/json" id="{{ $brand }}-measurement-config">{!! json_encode($measurementConfig, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    <script type="application/json" id="{{ $brand }}-measurement-labels">{!! json_encode($measurementLabels, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    <script src="{{ asset('js/measurement.js') }}?v={{ $measurementJsVersion }}" defer></script>
@endif
@php
    $analyticsJsPath = public_path('js/webnu-analytics.js');
    $analyticsJsVersion = is_file($analyticsJsPath) ? filemtime($analyticsJsPath) : time();
@endphp
{{-- Analítica first-party (panel Plataforma → Analítica). Sin cookies de terceros. --}}
<script src="{{ asset('js/webnu-analytics.js') }}?v={{ $analyticsJsVersion }}" defer></script>
