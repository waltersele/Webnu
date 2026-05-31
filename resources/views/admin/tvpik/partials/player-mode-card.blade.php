{{-- Reproductor rápido: HDMI o Cast --}}
<div class="col-12">
    <div class="card wn-tvpik-player-card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="wn-tvpik-player-card__icon"><i class="ti ti-cast"></i></span>
                <h5 class="mb-0">Reproductor rápido</h5>
            </div>
            <p class="text-muted small mb-3">
                Abre la carta en pantalla completa y emítela por HDMI o Cast. Ideal si no tienes hardware TVPik.
            </p>
            @if($defaultCompanyId ?? null)
                @php
                    $playerLayoutMap = collect($templates)->mapWithKeys(function ($tpl, $key) {
                        return [$key => $tpl['layout'] ?? $key];
                    });
                @endphp
                <div class="wn-tvpik-player-row" id="wn-tvpik-player-tools"
                     data-player-admin="{{ route('admin.tvpik.player') }}"
                     data-tv-root="{{ url('/tv') }}"
                     data-layouts='@json($playerLayoutMap)'>
                    <select class="form-select form-select-sm" id="wn-player-company" aria-label="Carta">
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}"
                                data-slug="{{ $c->slug }}"
                                {{ (int) $c->id === (int) $defaultCompanyId ? 'selected' : '' }}
                                {{ (int) $c->menu_type !== 1 ? 'disabled' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                    <select class="form-select form-select-sm" id="wn-player-template" aria-label="Plantilla">
                        @foreach($templates as $key => $tpl)
                            <option value="{{ $key }}">{{ $tpl['label'] }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-primary btn-sm" id="wn-player-open">
                        <i class="ti ti-player-play me-1"></i> Abrir
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="wn-player-copy">
                        <i class="ti ti-link me-1"></i> Copiar enlace
                    </button>
                </div>
                <p class="small text-muted mt-2 mb-0" id="wn-player-url-hint"></p>
            @else
                <p class="small text-muted mb-0">Crea un negocio con carta digital para generar el enlace.</p>
            @endif
        </div>
    </div>
</div>
