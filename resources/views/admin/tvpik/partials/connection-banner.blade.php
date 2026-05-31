@php
    $connected = $tvpikConnected ?? false;
    $screenCount = $screenCount ?? 0;
    $maxScreens = $maxScreens ?? null;
    $limitLabel = $maxScreens === null ? 'ilimitadas' : $maxScreens;
@endphp
<div class="wn-tvpik-connection-banner {{ $connected ? 'is-connected' : 'is-disconnected' }}">
    <div class="wn-tvpik-connection-banner__icon" aria-hidden="true">
        <i class="ti ti-plug-connected"></i>
    </div>
    <div class="wn-tvpik-connection-banner__body">
        @if($connected)
            <strong>Pantallas activas</strong>
            <p class="mb-0 small text-muted">
                {{ $screenCount }} pantalla{{ $screenCount === 1 ? '' : 's' }}
                @if($maxScreens !== null)
                    · {{ $screenCount }}/{{ $maxScreens }} incluidas en tu plan
                @else
                    · plan con pantallas ilimitadas
                @endif
            </p>
        @elseif(!empty($bootstrapError))
            <strong>No se pudo conectar</strong>
            <p class="mb-0 small text-muted">{{ $bootstrapError }}</p>
        @else
            <strong>Activando pantallas…</strong>
            <p class="mb-0 small text-muted">Conectando con el servicio de señalización.</p>
        @endif
    </div>
    <div class="wn-tvpik-connection-banner__actions">
        <button type="button" class="btn btn-sm btn-label-secondary" data-bs-toggle="collapse" data-bs-target="#wn-tvpik-advanced" aria-expanded="false">
            Avanzado
        </button>
    </div>
</div>
