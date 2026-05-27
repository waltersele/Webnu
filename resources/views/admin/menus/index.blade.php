@extends('admin.layout')

@section('page_title', 'Menús')
@section('page_subtitle', 'Crea menús del día, degustación o infantiles. Cada menú tiene su precio y sus platos por turno.')

@section('content')
<div class="wn-menus-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">Menús de {{ $company->name }}</h2>
            <p class="text-muted small mb-0">
                {{ $menus->count() === 0
                    ? 'Aún no has creado ningún menú.'
                    : ($menus->count() === 1 ? '1 menú creado.' : $menus->count() . ' menús creados.') }}
            </p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create-menu">
            <i class="ri ri-add-line me-1"></i> Crear menú
        </button>
    </div>

    @if(session('flash'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('flash') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if($menus->where('enabled', true)->count() >= 2)
        <form method="POST" action="{{ route('admin.menus.combine') }}" class="wn-menus-combine card border-0 shadow-sm mb-4">
            @csrf
            <div class="card-body d-flex flex-wrap align-items-center gap-3">
                <div class="flex-grow-1 me-3">
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="combine_menus" name="combine_menus" value="1"
                               {{ $company->combine_menus ? 'checked' : '' }}
                               onchange="this.form.submit()">
                        <label class="form-check-label fw-semibold" for="combine_menus">
                            Mostrar todos los menús en la misma carta
                        </label>
                    </div>
                    <p class="text-muted small mb-0 mt-1">
                        Si lo activas, el QR de tu carta abrirá una sola página con pestañas por menú (ej. Comidas / Cenas / Brunch).
                        Si lo dejas desactivado, cada menú se ve en su propia URL y se puede compartir individualmente.
                    </p>
                </div>
                <noscript>
                    <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                </noscript>
            </div>
        </form>
    @endif

    @if($menus->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="wn-menus-empty-icon mx-auto mb-3">
                    <i class="ri ri-restaurant-line"></i>
                </div>
                <h5 class="mb-2">Crea tu primer menú</h5>
                <p class="text-muted mb-4">
                    Define un menú del día con precio fijo, o una carta de menús degustación, infantiles, etc.
                </p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create-menu">
                    <i class="ri ri-add-line me-1"></i> Crear menú
                </button>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach($menus as $menu)
                <div class="col-md-6 col-xl-4">
                    <article class="card h-100 wn-menu-card {{ $menu->enabled ? '' : 'wn-menu-card--disabled' }}">
                        <div class="card-body d-flex flex-column gap-2">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <h5 class="mb-0 fw-bold">
                                    <i class="ti ti-bowl-spoon text-primary"></i>
                                    {{ $menu->name }}
                                </h5>
                                @if($menu->enabled)
                                    <span class="badge bg-label-success">Activo</span>
                                @else
                                    <span class="badge bg-label-secondary">Oculto</span>
                                @endif
                            </div>
                            @if($menu->subtitle)
                                <p class="text-muted small mb-1">{{ $menu->subtitle }}</p>
                            @endif
                            <div class="d-flex flex-wrap gap-3 align-items-center small text-muted">
                                <span>
                                    <i class="ri ri-money-euro-circle-line"></i>
                                    {{ $menu->formattedPrice() ?? 'Sin precio' }}
                                </span>
                                <span>
                                    <i class="ri ri-list-check-2"></i>
                                    {{ $menu->items_count }} {{ $menu->items_count === 1 ? 'plato' : 'platos' }}
                                </span>
                            </div>
                            @if($menu->includes)
                                <p class="small text-muted mb-0">
                                    <i class="ri ri-information-line"></i> {{ $menu->includes }}
                                </p>
                            @endif
                            <div class="mt-auto pt-3 d-flex gap-2">
                                <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-sm btn-primary flex-grow-1">
                                    <i class="ri ri-edit-2-line me-1"></i> Editar
                                </a>
                                @if($menu->enabled)
                                    <a href="{{ route('admin.qr.menu.generator', $menu) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Descargar QR de este menú">
                                        <i class="ri ri-qr-code-line"></i>
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}"
                                      onsubmit="return confirm('¿Eliminar este menú? Esta acción no se puede deshacer.');"
                                      class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar menú">
                                        <i class="ri ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    @endif
</div>

@endsection

@push('modals')
<div class="modal fade" id="modal-create-menu" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('admin.menus.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Nuevo menú</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <label for="new-menu-name" class="form-label">Nombre del menú</label>
                <input type="text" class="form-control" id="new-menu-name" name="name" required
                       maxlength="120" placeholder="Ej. Menú del día, Menú degustación, Menú infantil">
                <p class="form-text">Podrás añadir precio, platos y el texto "incluye…" en el siguiente paso.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear y editar</button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
(function () {
    function openCreateMenuIfHashNew() {
        if (location.hash !== '#new') return;
        var modalEl = document.getElementById('modal-create-menu');
        if (!modalEl || typeof bootstrap === 'undefined') return;
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
        // Limpiamos el hash para que no se reabra al refrescar
        history.replaceState(null, '', location.pathname + location.search);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', openCreateMenuIfHashNew);
    } else {
        openCreateMenuIfHashNew();
    }
})();
</script>
@endpush

@push('styles')
<style>
.wn-menus-empty-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: rgba(0, 74, 198, 0.08);
    color: #004ac6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
}
.wn-menu-card { transition: box-shadow .15s ease, transform .15s ease; }
.wn-menu-card:hover { box-shadow: 0 6px 20px rgba(15, 23, 42, 0.08); transform: translateY(-2px); }
.wn-menu-card--disabled { opacity: .7; }
</style>
@endpush
