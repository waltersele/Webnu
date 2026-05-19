@extends('admin.layout')

@section('page_title', 'Integraciones')
@section('page_subtitle', 'Conecta Webnu con TVPik, pantallas y la API de carta.')

@section('content')
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0"><i class="ri ri-tv-line me-2"></i>TVPik / Pantallas</h5>
                <span class="badge bg-label-success">Activo</span>
            </div>
            <div class="card-body">
                <p class="text-muted">Sincroniza tu carta con TVPik u otra app de digital signage.</p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="api-token" readonly value="{{ $apiToken }}">
                    <button type="button" class="btn btn-outline-primary" id="copy-token">Copiar</button>
                </div>
                <form method="POST" action="{{ route('admin.integrations.regenerate') }}" onsubmit="return confirm('¿Regenerar token? Las apps conectadas dejarán de sincronizar.');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-label-secondary">Regenerar token</button>
                </form>
                @if ($appKeyConfigured)
                    <p class="small text-muted mt-3 mb-0"><i class="ri ri-lock-line"></i> Cabecera: <code>X-Digital-Signage-Key</code></p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0"><i class="ri ri-code-line me-2"></i>API de carta</h5>
                <span class="badge bg-label-success">Activo</span>
            </div>
            <div class="card-body">
                <p class="text-muted mb-2">Base: <code>{{ url('/api/signage') }}</code></p>
                <pre class="bg-lighter rounded p-3 small mb-0"><code>POST /api/signage/login
GET /api/signage/menus
GET /api/signage/menus/{slug}</code></pre>
            </div>
        </div>
    </div>

    @if(count($menus))
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Cartas sincronizables</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Negocio</th>
                            <th>Slug</th>
                            <th>Versión</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($menus as $menu)
                        <tr>
                            <td>{{ $menu['name'] }}</td>
                            <td><code>{{ $menu['slug'] }}</code></td>
                            <td class="text-muted small">{{ $menu['sync_version'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.getElementById('copy-token').addEventListener('click', function () {
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

