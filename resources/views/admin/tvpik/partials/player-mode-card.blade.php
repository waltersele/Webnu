{{-- Reproductor rápido: HDMI, navegador o compartir pantalla (carta + menú; plantilla en vista previa) --}}
@php
    $playerLayoutMap = collect($templates ?? config('tvpik_templates.templates', []))->mapWithKeys(function ($tpl, $key) {
        return [$key => $tpl['layout'] ?? $key];
    });
    $templateLabels = collect($templates ?? config('tvpik_templates.templates', []))->mapWithKeys(function ($tpl, $key) {
        return [$key => $tpl['label'] ?? $key];
    });
    $menusForDefault = ($menusByCompany ?? collect())->get($defaultCompanyId ?? 0, collect());
@endphp
<div class="col-12">
    <div class="card wn-tvpik-player-card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="wn-tvpik-player-card__icon"><i class="ti ti-player-play"></i></span>
                <h5 class="mb-0">Reproductor rápido</h5>
            </div>
            <p class="text-muted small mb-3">
                Previsualiza arriba la plantilla elegida y emítela en cualquier TV por <strong>HDMI</strong>,
                el <strong>navegador</strong> de la tele o <strong>compartir pantalla</strong> (Cast / presentación).
            </p>
            @if($defaultCompanyId ?? null)
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="wn-tvpik-player-row" id="wn-tvpik-player-tools"
                             data-player-admin="{{ route('admin.tvpik.player') }}"
                             data-preview-admin="{{ route('admin.tvpik.preview') }}"
                             data-tv-root="{{ url('/tv') }}"
                             data-layouts='@json($playerLayoutMap)'
                             data-template-labels='@json($templateLabels)'
                             data-default-template="menu"
                             data-selected-template="menu">
                            <div class="wn-tvpik-player-field">
                                <label class="form-label small text-muted mb-1" for="wn-player-company">Seleccionar carta</label>
                                <select class="form-select form-select-sm" id="wn-player-company" aria-label="Seleccionar carta">
                                    @foreach($companies as $c)
                                        <option value="{{ $c->id }}"
                                            data-slug="{{ $c->slug }}"
                                            {{ (int) $c->id === (int) $defaultCompanyId ? 'selected' : '' }}
                                            {{ (int) $c->menu_type !== 1 ? 'disabled' : '' }}>
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="wn-tvpik-player-field">
                                <label class="form-label small text-muted mb-1" for="wn-player-menu">Seleccionar menú</label>
                                <select class="form-select form-select-sm" id="wn-player-menu" aria-label="Seleccionar menú">
                                    <option value="">Carta completa</option>
                                    @foreach($menusForDefault as $menu)
                                        <option value="{{ $menu->id }}" data-company-id="{{ $menu->company_id }}">
                                            {{ $menu->name }}
                                        </option>
                                    @endforeach
                                    @foreach($menusByCompany ?? [] as $companyId => $menus)
                                        @if((int) $companyId === (int) $defaultCompanyId)
                                            @continue
                                        @endif
                                        @foreach($menus as $menu)
                                            <option value="{{ $menu->id }}" data-company-id="{{ $menu->company_id }}" hidden>
                                                {{ $menu->name }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <div class="wn-tvpik-player-actions d-flex flex-wrap gap-2 align-items-end w-100">
                                <button type="button" class="btn btn-primary btn-sm" id="wn-player-open" title="Pantalla completa en otra pestaña">
                                    <i class="ti ti-maximize me-1"></i> Abrir en TV
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="wn-player-copy" title="Copiar enlace">
                                    <i class="ti ti-link me-1"></i> Copiar enlace
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="wn-player-screenshare" title="Transmitir a Chromecast o pantalla inalámbrica">
                                    <i class="ti ti-screen-share me-1"></i> Compartir pantalla
                                </button>
                            </div>
                            <p class="small text-muted mt-2 mb-0 w-100" id="wn-player-url-hint"></p>
                            <p class="small text-muted mb-0 w-100 d-none" id="wn-player-screenshare-hint"></p>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        @include('admin.tvpik.partials.preview-panel')
                    </div>
                </div>
            @else
                <p class="small text-muted mb-0">Crea un negocio con carta digital para generar el enlace.</p>
            @endif
        </div>
    </div>
</div>
