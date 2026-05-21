@extends('sales.layout')

@section('title', 'Importar carta')

@push('styles')
<link rel="stylesheet" href="{{ asset('materio/css/webnu-menu-scan.css') }}">
@endpush

@section('content')
<p style="margin: 0 0 0.25rem;"><a href="{{ route('sales.visit.show', $visit->id) }}" style="color: #64748b; font-size: 0.9rem;">← {{ $visit->name }}</a></p>
<h1 style="font-size: 1.25rem; margin: 0 0 1rem;">Importar carta</h1>

@if (! $scanConfigured)
    <div class="wn-sales-error">El escaneo con IA no está configurado. Contacta con administración.</div>
@else
    <div class="wn-sales-card">
        <form method="POST" action="{{ route('sales.menu-scan.store', $visit->id) }}" enctype="multipart/form-data" id="menu-scan-upload-form">
            @csrf
            <input type="file" name="files[]" id="menu-scan-files" accept="image/jpeg,image/png,image/webp,application/pdf" multiple class="d-none">
            <input type="file" id="menu-scan-camera-native" accept="image/*" capture="environment" class="d-none">

            <p style="color: #64748b; font-size: 0.9rem; text-align: center; margin-bottom: 1rem;">
                Fotografía la carta o sube un PDF; la digitalizamos con IA.
            </p>

            <div class="wn-menu-scan-start__actions" style="display: flex; flex-direction: column; gap: 0.75rem;">
                <button type="button" class="wn-sales-btn wn-sales-btn-primary" id="menu-scan-start">
                    Escanear con cámara
                </button>
                <button type="button" class="wn-sales-btn wn-sales-btn-outline" id="menu-scan-pick-files">
                    Subir PDF o fotos
                </button>
            </div>

            <div class="wn-menu-scan-dropzone d-none d-md-block mt-3" id="menu-scan-dropzone" style="cursor: pointer;">
                <div class="wn-menu-scan-dropzone-inner">
                    <p class="mb-0 text-muted small text-center">Arrastra fotos o PDF aquí</p>
                </div>
            </div>

            <ul class="list-group list-group-flush mt-3 d-none" id="menu-scan-file-list"></ul>

            @if ($errors->any())
                <div class="wn-sales-error mt-3">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <button type="submit" class="wn-sales-btn wn-sales-btn-primary mt-3" id="menu-scan-submit" disabled>
                <span class="spinner-border spinner-border-sm d-none me-1" id="menu-scan-spinner" role="status"></span>
                Analizar carta
            </button>
        </form>
    </div>

    <div id="menu-scan-processing" class="wn-scan-processing" hidden aria-hidden="true" aria-live="polite">
        <div class="wn-scan-processing__backdrop" aria-hidden="true"></div>
        <div class="wn-scan-processing__content">
            <p class="mb-0 fw-semibold">Analizando carta con IA…</p>
            <p class="small text-muted mb-0">Puede tardar un minuto. No cierres esta pantalla.</p>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script src="{{ asset('js/webnu-sales-menu-scan.js') }}"></script>
@endpush
