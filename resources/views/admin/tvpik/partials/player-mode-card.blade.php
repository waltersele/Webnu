{{-- Modo reproductor: emitir por HDMI o Cast manteniendo control en Webnu --}}
<div class="col-12">
    <div class="card border border-primary border-opacity-25">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3 align-items-start">
                <span class="avatar">
                    <span class="avatar-initial rounded bg-label-primary">
                        <i class="ti ti-cast"></i>
                    </span>
                </span>
                <div class="flex-grow-1">
                    <h5 class="mb-2">Modo reproductor (HDMI o pantalla compartida)</h5>
                    <p class="text-muted small mb-3">
                        Abre la carta en <strong>pantalla completa</strong> en un PC, tablet o móvil y emítela en la TV por cable HDMI
                        o con <strong>Cast / duplicar pantalla</strong>. Tú sigues editando precios y platos en Webnu: la TV se actualiza sola.
                    </p>
                    <ul class="small text-muted mb-3 ps-3">
                        <li><strong>HDMI:</strong> conecta el dispositivo a la TV y abre el enlace del reproductor en el navegador.</li>
                        <li><strong>Cast (Chrome):</strong> menú Cast → «Transmitir pestaña» con el reproductor abierto.</li>
                        <li><strong>TVPik:</strong> si tienes pantallas TVPik, también puedes publicar ahí; el reproductor sirve sin hardware extra.</li>
                    </ul>
                    @if($defaultCompanyId ?? null)
                        @php
                            $playerLayoutMap = collect($templates)->mapWithKeys(function ($tpl, $key) {
                                return [$key => $tpl['layout'] ?? $key];
                            });
                        @endphp
                        <div class="d-flex flex-wrap gap-2 align-items-center" id="wn-tvpik-player-tools"
                             data-player-admin="{{ route('admin.tvpik.player') }}"
                             data-tv-root="{{ url('/tv') }}"
                             data-layouts='@json($playerLayoutMap)'>
                            <select class="form-select form-select-sm w-auto" id="wn-player-company">
                                @foreach($companies as $c)
                                    <option value="{{ $c->id }}"
                                        data-slug="{{ $c->slug }}"
                                        {{ (int) $c->id === (int) $defaultCompanyId ? 'selected' : '' }}
                                        {{ (int) $c->menu_type !== 1 ? 'disabled' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                            <select class="form-select form-select-sm w-auto" id="wn-player-template">
                                @foreach($templates as $key => $tpl)
                                    <option value="{{ $key }}">{{ $tpl['label'] }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-primary btn-sm" id="wn-player-open">
                                <i class="ti ti-player-play me-1"></i> Abrir reproductor
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="wn-player-copy">
                                <i class="ti ti-link me-1"></i> Copiar enlace TV
                            </button>
                        </div>
                        <p class="small text-muted mt-2 mb-0" id="wn-player-url-hint"></p>
                    @else
                        <p class="small text-muted mb-0">Crea un negocio con carta digital para generar el enlace del reproductor.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
