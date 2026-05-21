@extends('admin.layout')

@section('page_title', 'TV y TVPik')
@section('page_subtitle', 'Lo que configuras en Webnu se muestra en las pantallas del local a través de TVPik.')

@section('content')
@php
    $canTvpik = $planFeatures['tvpik'] ?? false;
@endphp

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 bg-primary-subtle">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-start gap-3">
                    <div class="avatar avatar-lg">
                        <span class="avatar-initial rounded bg-primary text-white">
                            <i class="ti ti-device-tv ti-lg"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-2">Webnu + TVPik: carta en el móvil, cartelería en la TV</h5>
                        <p class="text-muted mb-0">
                            En <strong>Webnu</strong> gestionas platos, precios y la carta QR.
                            En <strong>TVPik</strong> eliges qué se ve en cada pantalla: slides de platos, menú del día, destacados, vídeos o carta completa — con <strong>plantillas TV</strong> distintas a la del QR.
                            Los datos salen de Webnu y se sincronizan solos; tú decides el formato en TV.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Cómo conectarlo (3 pasos)</h5>
            </div>
            <div class="card-body">
                <ol class="mb-0 ps-3">
                    <li class="mb-3">
                        <strong>Prepara tu carta en Webnu</strong> — secciones, platos, precios y plantilla móvil (Carta → Vista previa).
                    </li>
                    <li class="mb-3">
                        <strong>Copia el token de abajo</strong> y pégalo en la app <strong>TVPik</strong> (menú Integraciones → Webnu).
                    </li>
                    <li>
                        <strong>En TVPik</strong> selecciona tu negocio, la carta que quieres emitir y la plantilla de TV. Asigna esa carta a la pantalla del bar, comedor o terraza.
                    </li>
                </ol>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        @if ($canTvpik)
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="card-title mb-0"><i class="ti ti-plug me-2"></i>Token para TVPik</h5>
                <span class="badge bg-label-success">Plan Ilimitado</span>
            </div>
            <div class="card-body">
                <p class="text-muted">Pega este código en TVPik para que tus TVs lean las cartas de tu cuenta Webnu.</p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control font-monospace" id="api-token" readonly value="{{ $apiToken }}">
                    <button type="button" class="btn btn-primary" id="copy-token">Copiar</button>
                </div>
                <form method="POST" action="{{ route('admin.integrations.regenerate') }}" onsubmit="return confirm('¿Regenerar token? Tendrás que volver a pegarlo en TVPik.');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-label-secondary">Regenerar token</button>
                </form>
                @if ($appKeyConfigured)
                    <p class="small text-muted mt-3 mb-0"><i class="ti ti-lock"></i> Conexión segura con cabecera <code>X-Digital-Signage-Key</code></p>
                @endif
            </div>
        </div>
        @else
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="card-title mb-0"><i class="ti ti-plug me-2"></i>Token para TVPik</h5>
                @include('admin.partials.plan-pro-badge', ['label' => 'Ilimitado'])
            </div>
            @component('admin.partials.plan-feature-lock', [
                'feature' => 'tvpik',
                'message' => 'Muestra tu carta Webnu en las TVs del local con el plan Ilimitado.',
            ])
            <div class="card-body">
                <p class="text-muted">Con el plan Ilimitado obtienes el token para conectar TVPik y elegir carta + plantilla en cada pantalla.</p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" readonly value="••••••••••••••••••••••••••••••••">
                    <button type="button" class="btn btn-outline-primary" disabled>Copiar</button>
                </div>
                <a href="{{ route('admin.settings') }}#plan" class="btn btn-primary btn-sm">Ver planes</a>
            </div>
            @endcomponent
        </div>
        @endif
    </div>

    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ti ti-list-check me-2"></i>Qué verás en TVPik</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex gap-2 mb-3">
                        <i class="ti ti-check text-success mt-1"></i>
                        <span>Todas tus cartas / negocios de Webnu</span>
                    </li>
                    <li class="d-flex gap-2 mb-3">
                        <i class="ti ti-check text-success mt-1"></i>
                        <span>Plantillas de TV distintas a la del móvil (cartelería, menú del día, destacados…)</span>
                    </li>
                    <li class="d-flex gap-2 mb-3">
                        <i class="ti ti-check text-success mt-1"></i>
                        <span>Actualización al guardar platos o precios en Webnu</span>
                    </li>
                    <li class="d-flex gap-2">
                        <i class="ti ti-check text-success mt-1"></i>
                        <span>Varias pantallas: barra, sala, terraza con cartas diferentes si quieres</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    @if(count($menus))
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tus cartas disponibles en TVPik</h5>
            </div>
            <div class="card-body pb-0">
                <p class="text-muted small">En TVPik podrás elegir cualquiera de estos negocios. La URL pública es la misma que usan tus clientes con el QR.</p>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Negocio</th>
                            <th>Enlace carta</th>
                            <th>Última versión</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($menus as $menu)
                        <tr>
                            <td>{{ $menu['name'] }}</td>
                            <td>
                                <a href="{{ $menu['public_url'] ?? url('/carta/' . $menu['slug']) }}" target="_blank" rel="noopener" class="small">
                                    webnu.es/carta/{{ $menu['slug'] }}
                                </a>
                            </td>
                            <td class="text-muted small">{{ $menu['sync_version'] ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <div class="col-12">
        <details class="card">
            <summary class="card-header" style="cursor:pointer;">
                <h5 class="card-title mb-0 d-inline">Documentación técnica (API)</h5>
            </summary>
            <div class="card-body">
                <p class="text-muted mb-2">Solo si desarrollas integraciones propias. TVPik usa este API internamente.</p>
                <p class="mb-2">Base: <code>{{ url('/api/signage') }}</code></p>
                <pre class="bg-lighter rounded p-3 small mb-0"><code>POST /api/signage/login
GET /api/signage/menus
GET /api/signage/menus/{slug}</code></pre>
            </div>
        </details>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('copy-token')?.addEventListener('click', function () {
    var input = document.getElementById('api-token');
    input.select();
    if (navigator.clipboard) {
        navigator.clipboard.writeText(input.value);
    } else {
        document.execCommand('copy');
    }
    this.textContent = 'Copiado';
    var btn = this;
    setTimeout(function () { btn.textContent = 'Copiar'; }, 2000);
});
</script>
@endpush
