@extends('admin.layout')

@section('page_title', $company->name)
@section('page_subtitle', 'Configura nombre, contacto, diseño y publicación de tu carta')

@section('page_actions')
    <a href="{{ $company->publicUrl() }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
        <i class="ri-eye-line me-1"></i> Ver carta
    </a>
    <a href="{{ route('admin.companies.languages', $company) }}" class="btn btn-outline-primary btn-sm">
        <i class="ri-translate-2 me-1"></i> Idiomas
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
            <div class="card border-0 shadow-sm wn-studio-card">
                <div class="card-body pb-0 pt-3">
                    <nav class="wn-studio-stepper" role="tablist" aria-label="Pasos del estudio">
                        @foreach($steps as $stepKey => $stepMeta)
                            @php $stepIndex = array_search($stepKey, array_keys($steps), true); @endphp
                            <button type="button"
                                class="wn-studio-stepper__item {{ $activeStep === $stepKey ? 'is-active' : '' }}"
                                data-wn-step="{{ $stepKey }}"
                                aria-current="{{ $activeStep === $stepKey ? 'step' : 'false' }}">
                                <span class="wn-studio-stepper__num">{{ $stepIndex + 1 }}</span>
                                <span class="wn-studio-stepper__label">{{ $stepMeta['label'] }}</span>
                            </button>
                        @endforeach
                    </nav>
                </div>
                <div class="card-body pt-3">
                    @include('admin.companies.partials.studio-step-identity')
                    @include('admin.companies.partials.studio-step-contact')
                    @include('admin.companies.partials.studio-step-design')
                    @include('admin.companies.partials.studio-step-publish')
                </div>
                <div class="card-footer wn-studio-footer border-0">
                    <div class="wn-studio-footer__row">
                        <div class="wn-studio-footer__group wn-studio-footer__group--start">
                            <button type="button" class="btn btn-outline-secondary" id="wn-step-prev" disabled>
                                <i class="ri-arrow-left-line me-1"></i> Anterior
                            </button>
                            <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-s-line me-1"></i> Volver a negocios
                            </a>
                        </div>
                        <div class="wn-studio-footer__group wn-studio-footer__group--end">
                            <button type="button" class="btn btn-outline-primary" id="wn-step-next">
                                Siguiente <i class="ri-arrow-right-line ms-1"></i>
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> Guardar
                            </button>
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modal-delete-company">
                                <i class="ri-delete-bin-line me-1"></i> Eliminar negocio
                            </button>
                        </div>
                    </div>
                </div>
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
    @php
        $studioFontFamilies = [];
        foreach ($fonts as $fontMeta) {
            $family = str_replace(' ', '+', $fontMeta['family'] ?? 'Inter');
            $weights = $fontMeta['weights'] ?? '400;600;700';
            $studioFontFamilies[] = 'family=' . $family . ':wght@' . $weights;
        }
    @endphp
    @if(count($studioFontFamilies))
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?{{ implode('&', $studioFontFamilies) }}&display=swap" rel="stylesheet">
    @endif
@endpush

@push('scripts')
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

