@extends('admin.layout')

@section('page_title', 'Panel de control')
@section('page_subtitle', 'Gestiona negocios, carta digital e integraciones.')

@section('content')
@if($dashboardCompany ?? null)
    <div class="card border-0 shadow-sm mb-4 wn-dash-menu-card">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                <div>
                    <p class="text-muted small mb-1">Negocio activo</p>
                    <h5 class="mb-0 fw-semibold">{{ $dashboardCompany->name }}</h5>
                    <a href="{{ route('see_menu', $dashboardCompany->slug) }}" target="_blank" rel="noopener" class="small text-primary">
                        webnu.es/carta/{{ $dashboardCompany->slug }}
                    </a>
                </div>
                <div class="wn-dash-stat text-center text-md-end">
                    <div class="wn-dash-stat__value">{{ number_format($dashboardCompany->menu_views ?? 0, 0, ',', '.') }}</div>
                    <div class="wn-dash-stat__label">Visitas a la carta (QR)</div>
                </div>
            </div>
            <div class="row g-2">
                <div class="col-sm-6 col-md-4">
                    <a href="{{ route('see_menu', $dashboardCompany->slug) }}" target="_blank" rel="noopener" class="btn btn-outline-primary w-100 wn-dash-action">
                        <i class="ri-eye-line me-1"></i> Ver carta
                    </a>
                </div>
                <div class="col-sm-6 col-md-4">
                    <a href="{{ route('admin.qrgenerator', $dashboardCompany) }}" target="_blank" rel="noopener" class="btn btn-primary w-100 wn-dash-action">
                        <i class="ri-qr-code-line me-1"></i> Obtener QR
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('admin.companies.edit', ['company' => $dashboardCompany, 'step' => 'design']) }}" class="btn btn-label-secondary w-100 wn-dash-action">
                        <i class="ri-palette-line me-1"></i> Personalizar diseño
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif

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
                <a href="{{ route('admin.sections.index') }}" class="btn btn-webnu w-100 mb-2">Ir a mi carta</a>
                <a href="{{ route('admin.menu-scan.create') }}" class="btn btn-outline-success w-100 btn-sm">Importar desde foto o PDF</a>
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

@push('styles')
    <link rel="stylesheet" href="{{ asset('materio/css/webnu-dashboard.css') }}">
@endpush

