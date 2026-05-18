@extends('admin.layout')

@section('page_title', $company->name)
@section('page_subtitle', 'Estudio del negocio — configura tu carta digital')

@section('page_actions')
    <a href="{{ route('see_menu', $company->slug) }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
        <i class="ri-eye-line me-1"></i> Ver carta
    </a>
    <a href="{{ route('admin.sections.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ri-restaurant-line me-1"></i> Mi carta
    </a>
@endsection

@section('content')
@php
    $activeStep = request('step', 'identity');
    $steps = [
        'identity' => ['label' => 'Identidad', 'icon' => 'ri-store-2-line'],
        'contact' => ['label' => 'Contacto', 'icon' => 'ri-map-pin-line'],
        'design' => ['label' => 'Diseño', 'icon' => 'ri-palette-line'],
        'publish' => ['label' => 'Publicación', 'icon' => 'ri-rocket-line'],
    ];
@endphp

<form method="POST" action="{{ route('admin.companies.update', $company) }}" id="company-studio-form">
    @csrf
    @method('PUT')
    <input type="hidden" name="studio_step" id="studio-step-input" value="{{ $activeStep }}">

    <div class="row g-4 wn-studio-layout">
        <div class="col-lg-7 col-xl-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body pb-0">
                    <nav class="nav nav-pills flex-column flex-md-row wn-studio-nav gap-2 mb-0" role="tablist">
                        @foreach($steps as $stepKey => $stepMeta)
                            <button type="button"
                                class="nav-link {{ $activeStep === $stepKey ? 'active' : '' }}"
                                data-wn-step="{{ $stepKey }}">
                                <i class="{{ $stepMeta['icon'] }} me-1"></i> {{ $stepMeta['label'] }}
                            </button>
                        @endforeach
                    </nav>
                </div>
                <div class="card-body pt-4">
                    @include('admin.companies.partials.studio-step-identity')
                    @include('admin.companies.partials.studio-step-contact')
                    @include('admin.companies.partials.studio-step-design')
                    @include('admin.companies.partials.studio-step-publish')
                </div>
                <div class="card-footer bg-light d-flex flex-wrap justify-content-between align-items-center gap-2 border-top">
                    <button type="button" class="btn btn-label-secondary" id="wn-step-prev" disabled>
                        <i class="ri-arrow-left-line me-1"></i> Anterior
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" id="wn-step-next">
                            Siguiente <i class="ri-arrow-right-line ms-1"></i>
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i> Guardar
                        </button>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.companies.index') }}" class="btn btn-label-secondary btn-sm">Volver a negocios</a>
                <a href="#" class="text-danger btn btn-sm btn-text" data-bs-toggle="modal" data-bs-target="#modal-delete-company">
                    <i class="ri-delete-bin-line me-1"></i> Eliminar negocio
                </a>
            </div>
        </div>

        <div class="col-lg-5 col-xl-4">
            @include('admin.companies.partials.studio-preview')
        </div>
    </div>
</form>

<div class="modal fade" id="modal-delete-company">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Eliminar negocio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="{{ route('admin.companies.delete') }}">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>¿Eliminar <strong>{{ $company->name }}</strong>? Esta acción no se puede deshacer.</p>
                    <input type="hidden" name="companyid" value="{{ $company->id }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('materio/css/webnu-company-studio.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.0/dropzone.min.css">
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.0/min/dropzone.min.js"></script>
    <script>
        window.WebnuCompanyStudio = {
            companyId: {{ $company->id }},
            csrf: '{{ csrf_token() }}',
            previewUrl: @json($previewUrl),
            activeStep: @json($activeStep),
            themePresets: @json($themePresets),
            templateLabels: @json($templateLabels),
            steps: @json(array_keys($steps)),
            logoUrl: @json($company->logo ? '/img/' . $company->logo : null),
            headerUrl: @json($company->background_header ? '/img/' . $company->background_header : null),
        };
    </script>
    <script src="{{ asset('materio/js/webnu-company-studio.js') }}"></script>
@endpush
