@php
    $measurementConfig = app(\App\Services\Platform\MeasurementSettingsService::class)->publicConfig(
        session('measurement_event')
    );
    $siteVerification = app(\App\Services\Platform\MeasurementSettingsService::class)->googleSiteVerification();
@endphp
@if ($siteVerification)
    <meta name="google-site-verification" content="{{ $siteVerification }}">
@endif
<script type="application/json" id="webnu-measurement-config">{!! json_encode($measurementConfig, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
<script src="{{ asset('js/webnu-measurement.js') }}" defer></script>
