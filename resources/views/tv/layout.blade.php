<!DOCTYPE html>
<html lang="{{ $locale ?? 'es' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $company->name }} — TV{{ !empty($templateMeta['label']) ? ' · ' . $templateMeta['label'] : '' }}</title>
    <link rel="stylesheet" href="{{ asset('css/webnu-tv.css') }}">
    <style>
        :root {
            --wn-tv-accent: {{ $accent ?? '#004ac6' }};
        }
    </style>
</head>
<body class="wn-tv wn-tv--{{ $layout }}{{ !empty($isPreview) ? ' wn-tv--preview' : '' }}{{ !empty($isPlayerMode) ? ' wn-tv--player' : '' }}{{ empty($showHeader) ? ' wn-tv--no-header' : '' }}">
    @include('tv.partials.background')
    <div class="wn-tv__frame">
        @include('tv.partials.header')
        <main class="wn-tv-main">
            @yield('tv_content')
        </main>
    </div>
    @if(!empty($isPlayerMode))
        <div class="wn-tv-player-hud" id="wn-tv-player-hud" aria-live="polite">
            <span class="wn-tv-player-hud__dot"></span>
            <span class="wn-tv-player-hud__text" data-wn-tv-hud-status>En vivo · Webnu</span>
        </div>
    @endif
    @stack('tv_scripts')
    @if(!empty($isPlayerMode))
        <script src="{{ asset('js/webnu-tv-player.js') }}"></script>
        <script>
            WebnuTvPlayer.init({
                syncUrl: @json($syncUrl ?? ''),
                syncVersion: @json($syncVersion ?? ''),
                layout: @json($layout ?? 'menu'),
                pollSeconds: {{ (int) ($playerPollSeconds ?? 30) }}
            });
        </script>
    @endif
</body>
</html>
