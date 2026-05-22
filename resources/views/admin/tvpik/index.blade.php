@extends('admin.layout')

@section('page_title', 'TV / TVPik')
@section('page_subtitle', 'Publica tu carta en las pantallas del local desde Webnu')

@section('content')
@php
    $defaultCompanyId = $company ? $company->id : ($companies->first()->id ?? null);
@endphp

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 bg-primary-subtle">
            <div class="card-body d-flex flex-wrap gap-3 align-items-start">
                <span class="avatar avatar-lg">
                    <span class="avatar-initial rounded bg-primary text-white">
                        <i class="ti ti-device-tv ti-lg"></i>
                    </span>
                </span>
                <div class="flex-grow-1">
                    <h5 class="mb-2">Publicar carta en TV</h5>
                    <p class="text-muted mb-0">
                        Elige pantalla y plantilla TV (menú completo, plato del día, destacados o vídeos).
                        TVPik reproduce la URL optimizada para pantalla grande.
                    </p>
                </div>
                @if($company && (int) $company->menu_type === 1)
                    <form method="POST" action="{{ route('admin.tvpik.publish-all') }}" class="ms-auto">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm" @if(!$canTvpik) disabled @endif>
                            <i class="ti ti-refresh me-1"></i> Republicar todas las TVs
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if(!$canTvpik)
        <div class="col-12">
            @include('admin.partials.plan-feature-lock', [
                'feature' => 'tvpik',
                'message' => 'Publica tu carta en pantallas TV con Plus (1 pantalla incluida) o add-on TVPik en Pro.',
            ])
        </div>
    @else
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Conexión TVPik</h5>
                </div>
                <div class="card-body">
                    @if($tvpikConnected)
                        <p class="text-success mb-3"><i class="ti ti-check me-1"></i> Cuenta conectada</p>
                        <form method="POST" action="{{ route('admin.tvpik.disconnect') }}" onsubmit="return confirm('¿Desconectar TVPik?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-label-secondary">Desconectar</button>
                        </form>
                    @else
                        <p class="text-muted small">Pega el token de tu cuenta TVPik (Integraciones → Webnu en la app TVPik).</p>
                        <form method="POST" action="{{ route('admin.tvpik.connect') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="tvpik_token">Token TVPik</label>
                                <input type="password" name="tvpik_token" id="tvpik_token" class="form-control font-monospace" required autocomplete="off">
                                @error('tvpik_token')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Conectar</button>
                        </form>
                        @if($tvpikWebUrl)
                            <p class="small text-muted mt-3 mb-0">
                                <a href="{{ $tvpikWebUrl }}" target="_blank" rel="noopener">Abrir TVPik</a>
                            </p>
                        @endif
                    @endif
                    @unless($tvpikApiConfigured)
                        <p class="small text-warning mt-3 mb-0">
                            <code>TVPIK_API_URL</code> no configurada: verás pantallas de demostración y las URLs se guardan en Webnu.
                        </p>
                    @endunless
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Token API Webnu</h5>
                    <span class="badge bg-label-secondary">TVPik / desarrolladores</span>
                </div>
                <div class="card-body">
                    <div class="input-group mb-2">
                        <input type="text" class="form-control font-monospace" id="api-token" readonly value="{{ $apiToken }}">
                        <button type="button" class="btn btn-outline-primary" id="copy-token">Copiar</button>
                    </div>
                    <form method="POST" action="{{ route('admin.integrations.regenerate') }}" class="d-inline" onsubmit="return confirm('¿Regenerar token?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-label-secondary">Regenerar</button>
                    </form>
                </div>
            </div>
        </div>

        @error('publish')
            <div class="col-12"><div class="alert alert-danger">{{ $message }}</div></div>
        @enderror

        @include('admin.tvpik.partials.player-mode-card', [
            'defaultCompanyId' => $defaultCompanyId,
            'companies' => $companies,
            'templates' => $templates,
        ])

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Mis pantallas</h5>
                </div>
                <div class="card-body">
                    @if($screensError)
                        <div class="alert alert-warning">{{ $screensError }}</div>
                    @endif
                    @if(empty($screens))
                        <p class="text-muted mb-0">Conecta TVPik para ver tus pantallas. Si la API no está disponible, configura <code>TVPIK_API_URL</code> o usa <code>TVPIK_STUB_SCREENS=true</code> en desarrollo.</p>
                    @else
                        <div class="row g-3">
                            @foreach($screens as $screen)
                                @php
                                    $screenId = (string) ($screen['id'] ?? '');
                                    $link = $links->get($screenId);
                                @endphp
                                <div class="col-md-6 col-xl-4">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <strong>{{ $screen['name'] ?? $screenId }}</strong>
                                            @if(!empty($screen['online']))
                                                <span class="badge bg-success">Online</span>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ route('admin.tvpik.publish') }}">
                                            @csrf
                                            <input type="hidden" name="screen_id" value="{{ $screenId }}">
                                            <input type="hidden" name="screen_name" value="{{ $screen['name'] ?? $screenId }}">
                                            <input type="hidden" name="gallery_id" value="{{ $screen['gallery_id'] ?? '' }}">
                                            <div class="mb-2">
                                                <label class="form-label small">Carta</label>
                                                <select name="company_id" class="form-select form-select-sm" required>
                                                    @foreach($companies as $c)
                                                        <option value="{{ $c->id }}"
                                                            {{ (int) $c->id === (int) $defaultCompanyId ? 'selected' : '' }}
                                                            {{ (int) $c->menu_type !== 1 ? 'disabled' : '' }}>
                                                            {{ $c->name }}{{ (int) $c->menu_type !== 1 ? ' (PDF)' : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small">Plantilla TV</label>
                                                <select name="template_key" class="form-select form-select-sm" required>
                                                    @foreach($templates as $key => $tpl)
                                                        <option value="{{ $key }}" {{ ($link && $link->template_key === $key) ? 'selected' : '' }}>
                                                            {{ $tpl['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if($link && $link->last_synced_at)
                                                <p class="small text-muted mb-2">
                                                    Última publicación: {{ $link->last_synced_at->diffForHumans() }}
                                                    @if($link->last_error)
                                                        <span class="text-danger d-block">{{ Str::limit($link->last_error, 80) }}</span>
                                                    @endif
                                                </p>
                                            @endif
                                            <div class="d-flex flex-wrap gap-2">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="ti ti-upload me-1"></i> Publicar
                                                </button>
                                                <button type="submit"
                                                        formaction="{{ route('admin.tvpik.preview') }}"
                                                        formmethod="GET"
                                                        class="btn btn-outline-secondary btn-sm"
                                                        onclick="this.form.target='_blank'">
                                                    Vista previa
                                                </button>
                                                <button type="submit"
                                                        formaction="{{ route('admin.tvpik.player') }}"
                                                        formmethod="GET"
                                                        class="btn btn-outline-primary btn-sm"
                                                        onclick="this.form.target='_blank'"
                                                        title="Pantalla completa para HDMI o Cast">
                                                    <i class="ti ti-cast me-1"></i> Reproductor
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <details class="card" open>
                <summary class="card-header" style="cursor:pointer;">
                    <h5 class="card-title mb-0 d-inline">Plantillas TV disponibles</h5>
                </summary>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Cada plantilla genera una URL distinta para la pantalla. Usa la miniatura como referencia y abre la vista previa con tu carta actual.
                    </p>
                    <div class="row g-3">
                        @foreach($templates as $key => $tpl)
                            @php
                                $thumb = $tpl['thumbnail'] ?? ('img/tvpik/previews/' . ($tpl['layout'] ?? $key) . '.svg');
                                $previewCompany = $company ?? $companies->firstWhere('id', $defaultCompanyId);
                                $previewSlug = $previewCompany ? $previewCompany->slug : null;
                            @endphp
                            <div class="col-md-6 col-lg-3">
                                <article class="wn-tvpik-template-card">
                                    <div class="wn-tvpik-template-card__thumb">
                                        <img src="{{ asset($thumb) }}" alt="{{ $tpl['label'] }}" width="320" height="180" loading="lazy">
                                        <span class="wn-tvpik-template-card__badge">
                                            <i class="ti {{ $tpl['icon'] ?? 'ti-layout' }}"></i>
                                            {{ $tpl['label'] }}
                                        </span>
                                    </div>
                                    <div class="wn-tvpik-template-card__body">
                                        <h6 class="wn-tvpik-template-card__title">{{ $tpl['label'] }}</h6>
                                        <p class="wn-tvpik-template-card__desc">{{ $tpl['description'] }}</p>
                                        @if(!empty($tpl['duration_hint']))
                                            <p class="wn-tvpik-template-card__hint">{{ $tpl['duration_hint'] }}</p>
                                        @endif
                                        <div class="wn-tvpik-template-card__actions">
                                            @if($defaultCompanyId)
                                                <form method="GET"
                                                      action="{{ route('admin.tvpik.preview') }}"
                                                      target="_blank"
                                                      class="d-inline">
                                                    <input type="hidden" name="company_id" value="{{ $defaultCompanyId }}">
                                                    <input type="hidden" name="template_key" value="{{ $key }}">
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="ti ti-eye me-1"></i> Vista previa
                                                    </button>
                                                </form>
                                                @if($defaultCompanyId)
                                                <form method="GET"
                                                      action="{{ route('admin.tvpik.player') }}"
                                                      target="_blank"
                                                      class="d-inline">
                                                    <input type="hidden" name="company_id" value="{{ $defaultCompanyId }}">
                                                    <input type="hidden" name="template_key" value="{{ $key }}">
                                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                                        <i class="ti ti-cast me-1"></i> Reproductor
                                                    </button>
                                                </form>
                                                @endif
                                                @if($previewSlug)
                                                <a href="{{ route('tv.show.layout', ['companySlug' => $previewSlug, 'layout' => $tpl['layout'] ?? $key]) }}"
                                                   class="btn btn-outline-secondary btn-sm"
                                                   target="_blank"
                                                   rel="noopener">
                                                    <i class="ti ti-external-link me-1"></i> URL TV
                                                </a>
                                                @endif
                                            @else
                                                <span class="text-muted small">Selecciona un negocio para previsualizar.</span>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                </div>
            </details>
        </div>
    @endif
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('adminlte/css/webnu-tvpik.css') }}">
@endpush

@push('scripts')
<script>
(function () {
    var tools = document.getElementById('wn-tvpik-player-tools');
    if (!tools) return;
    var adminBase = tools.getAttribute('data-player-admin');
    var tvRoot = tools.getAttribute('data-tv-root') || '';
    var layouts = {};
    try {
        layouts = JSON.parse(tools.getAttribute('data-layouts') || '{}');
    } catch (e) {}
    var companySel = document.getElementById('wn-player-company');
    var tplSel = document.getElementById('wn-player-template');
    var hint = document.getElementById('wn-player-url-hint');

    function adminPlayerUrl() {
        var cid = companySel && companySel.value;
        var tpl = tplSel && tplSel.value;
        if (!cid || !tpl) return '';
        return adminBase + '?company_id=' + encodeURIComponent(cid) + '&template_key=' + encodeURIComponent(tpl);
    }

    function tvPlayerUrl() {
        var opt = companySel && companySel.options[companySel.selectedIndex];
        var slug = opt && opt.getAttribute('data-slug');
        var tpl = tplSel && tplSel.value;
        var layout = layouts[tpl] || tpl || 'menu';
        if (!slug) return '';
        return tvRoot.replace(/\/$/, '') + '/' + slug + '/' + layout + '?player=1';
    }

    function updateHint() {
        if (hint) {
            var direct = tvPlayerUrl();
            hint.textContent = direct
                ? 'URL para la TV: ' + direct
                : '';
        }
    }

    companySel && companySel.addEventListener('change', updateHint);
    tplSel && tplSel.addEventListener('change', updateHint);
    updateHint();

    document.getElementById('wn-player-open')?.addEventListener('click', function () {
        var url = adminPlayerUrl();
        if (!url) return;
        var w = window.open(url, 'webnu_tv_player', 'noopener,noreferrer');
        if (w) w.focus();
    });

    document.getElementById('wn-player-copy')?.addEventListener('click', function () {
        var url = tvPlayerUrl();
        if (!url || !navigator.clipboard) return;
        navigator.clipboard.writeText(url).then(function () {
            var btn = document.getElementById('wn-player-copy');
            if (btn) {
                var t = btn.innerHTML;
                btn.innerHTML = '<i class="ti ti-check me-1"></i> Copiado';
                setTimeout(function () { btn.innerHTML = t; }, 2000);
            }
        });
    });
})();

document.getElementById('copy-token')?.addEventListener('click', function () {
    var input = document.getElementById('api-token');
    if (!input) return;
    navigator.clipboard?.writeText(input.value);
    this.textContent = 'Copiado';
    var btn = this;
    setTimeout(function () { btn.textContent = 'Copiar'; }, 2000);
});
</script>
@endpush
