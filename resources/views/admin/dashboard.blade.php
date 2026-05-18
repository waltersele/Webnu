@extends('admin.layout')

@section('page_title', 'Panel de control')
@section('page_subtitle', 'Gestiona negocios, carta digital e integraciones.')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="avatar avatar-lg mx-auto mb-3">
                    <span class="avatar-initial rounded bg-label-primary">
                        <i class="ri ri-store-2-line icon-28px"></i>
                    </span>
                </div>
                <h5 class="card-title mb-2">Negocios</h5>
                <p class="text-muted small mb-3">Alta y configuración de establecimientos.</p>
                <a href="{{ route('admin.companies.index') }}" class="btn btn-webnu w-100">Ir a negocios</a>
            </div>
        </div>
    </div>

    @if (!empty($selected_company))
    <div class="col-sm-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="avatar avatar-lg mx-auto mb-3">
                    <span class="avatar-initial rounded bg-label-primary">
                        <i class="ri ri-restaurant-line icon-28px"></i>
                    </span>
                </div>
                <h5 class="card-title mb-2">Mi carta</h5>
                <p class="text-muted small mb-3">Secciones, platos, fotos y vídeos.</p>
                <a href="{{ route('admin.sections.index') }}" class="btn btn-webnu w-100">Ir a mi carta</a>
            </div>
        </div>
    </div>
    @endif

    <div class="col-sm-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="avatar avatar-lg mx-auto mb-3">
                    <span class="avatar-initial rounded bg-label-primary">
                        <i class="ri ri-plug-line icon-28px"></i>
                    </span>
                </div>
                <h5 class="card-title mb-2">Integraciones</h5>
                <p class="text-muted small mb-3">TVPik, API y pantallas.</p>
                <a href="{{ route('admin.integrations.index') }}" class="btn btn-webnu w-100">Ver integraciones</a>
            </div>
        </div>
    </div>
</div>

<div class="card bg-primary text-white overflow-hidden">
    <div class="card-body position-relative">
        <span class="badge bg-white text-primary mb-2">Nuevo: TVPik 2.0</span>
        <h4 class="text-white mb-3">Potencia la experiencia de tus comensales</h4>
        <a href="{{ route('admin.integrations.index') }}" class="btn btn-light btn-sm">Saber más</a>
    </div>
</div>
@endsection
