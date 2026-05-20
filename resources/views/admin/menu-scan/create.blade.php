@extends('admin.layout')

@section('page_title', 'Importar carta')
@section('page_subtitle', 'Fotografía o sube tu carta para digitalizarla con IA')

@push('styles')
<link rel="stylesheet" href="{{ asset('materio/css/webnu-menu-scan.css') }}">
@endpush

@section('page_actions')
    <a href="{{ route('admin.sections.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ri-arrow-left-line me-1"></i> Volver a Mi carta
    </a>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body wn-menu-scan-guide">
        <h6 class="fw-semibold mb-3"><i class="ri-camera-line me-1"></i> Cómo conseguir una foto perfecta</h6>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="wn-scan-tip">
                    <span class="wn-scan-tip__icon ri-sun-line"></span>
                    <strong>Luz uniforme</strong>
                    <p class="small text-muted mb-0">Evita sombras y reflejos sobre el papel.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="wn-scan-tip">
                    <span class="wn-scan-tip__icon ri-focus-3-line"></span>
                    <strong>Encuadra entero</strong>
                    <p class="small text-muted mb-0">Toda la página visible; usa el marco de la cámara.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="wn-scan-tip">
                    <span class="wn-scan-tip__icon ri-smartphone-line"></span>
                    <strong>Móvil en vertical</strong>
                    <p class="small text-muted mb-0">Sujeta el teléfono paralelo a la mesa, sin inclinar.</p>
                </div>
            </div>
        </div>
        <ul class="small text-muted mb-0 mt-3 ps-3">
            <li>Puedes hacer varias fotos (una por página o sección).</li>
            <li>También puedes subir un PDF desde el móvil o el ordenador.</li>
        </ul>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if (! $scanConfigured)
            <div class="alert alert-warning">
                @if ($isSuperAdmin)
                    El escaneo con IA no está configurado.
                    <a href="{{ route('admin.platform.settings') }}" class="alert-link">Configura la API de Gemini en Plataforma → Configuración</a>.
                @else
                    El escaneo con IA no está disponible en este momento. Contacta con el administrador de Webnu.
                @endif
            </div>
        @endif

        @if ($scanLimit !== null)
            <div class="alert {{ ($canScan ?? true) ? 'alert-info' : 'alert-warning' }} mb-3">
                <strong>Plan Gratis:</strong>
                @if (($scansRemaining ?? 0) > 0)
                    Te quedan <strong>{{ $scansRemaining }}</strong> de {{ $scanLimit }} escaneos IA (has usado {{ $scansUsed }}).
                @else
                    Has usado tus {{ $scanLimit }} escaneos IA incluidos.
                    <a href="{{ $billingUrl }}" class="alert-link">Pásate a Plus</a> para escaneos ilimitados.
                @endif
            </div>
        @endif

        @php $scanLocked = ! ($canScan ?? true) && $scanLimit !== null; @endphp
        <div class="{{ $scanLocked ? 'wn-plan-feature-lock' : '' }}">
            <div class="{{ $scanLocked ? 'wn-plan-feature-lock__content' : '' }}">
        <form method="POST" action="{{ route('admin.menu-scan.store') }}" enctype="multipart/form-data" id="menu-scan-upload-form">
            @csrf
            <input type="file" name="files[]" id="menu-scan-files" accept="image/jpeg,image/png,image/webp,application/pdf" multiple class="d-none">
            <input type="file" id="menu-scan-camera-native" accept="image/*" capture="environment" class="d-none">

            <div class="d-grid gap-2 d-sm-flex mb-3">
                <button type="button" class="btn btn-primary btn-lg flex-sm-fill" id="menu-scan-open-camera" @if(! $scanConfigured || ! ($canScan ?? true)) disabled @endif>
                    <i class="ri-camera-line me-1"></i> Hacer foto
                </button>
                <button type="button" class="btn btn-outline-primary btn-lg flex-sm-fill" id="menu-scan-pick-files" @if(! $scanConfigured || ! ($canScan ?? true)) disabled @endif>
                    <i class="ri-image-add-line me-1"></i> Galería / archivos
                </button>
            </div>

            <div class="wn-menu-scan-dropzone d-none d-md-block" id="menu-scan-dropzone">
                <div class="wn-menu-scan-dropzone-inner">
                    <i class="ri-upload-cloud-2-line display-6 text-primary mb-2"></i>
                    <p class="mb-0 text-muted small">En ordenador: arrastra fotos o PDF aquí</p>
                </div>
            </div>

            <ul class="list-group list-group-flush mt-3 d-none" id="menu-scan-file-list"></ul>

            @if ($errors->any())
                <div class="alert alert-danger mt-3 mb-0">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.sections.index') }}" class="btn btn-label-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary" id="menu-scan-submit" disabled @if(! $scanConfigured || ! ($canScan ?? true)) disabled @endif>
                    <span class="spinner-border spinner-border-sm d-none me-1" id="menu-scan-spinner" role="status"></span>
                    Analizar carta
                </button>
            </div>
        </form>
            </div>
            @if ($scanLocked)
                @include('admin.partials.plan-upgrade-veil', [
                    'feature' => 'menu_scan',
                    'message' => 'Has usado tus escaneos incluidos. Pásate a Plus para escaneos ilimitados.',
                ])
            @endif
        </div>
    </div>
</div>

<div id="menu-scan-processing" class="wn-scan-processing" hidden aria-hidden="true" aria-live="polite">
    <div class="wn-scan-processing__backdrop" aria-hidden="true"></div>
    <div class="wn-scan-processing__grid" aria-hidden="true"></div>
    <div class="wn-scan-processing__content">
        <div class="wn-scan-processing__scanner" aria-hidden="true">
            <div class="wn-scan-processing__ring wn-scan-processing__ring--1"></div>
            <div class="wn-scan-processing__ring wn-scan-processing__ring--2"></div>
            <div class="wn-scan-processing__core">
                <i class="ri-sparkling-2-fill"></i>
            </div>
            <div class="wn-scan-processing__beam"></div>
        </div>
        <p class="wn-scan-processing__eyebrow">Webnu · Escaneo inteligente</p>
        <h2 class="wn-scan-processing__title">Analizando tu carta</h2>
        <p class="wn-scan-processing__status" id="menu-scan-processing-status">Preparando imágenes…</p>
        <div class="wn-scan-processing__progress" role="progressbar" aria-valuemin="0" aria-valuemax="100">
            <div class="wn-scan-processing__progress-bar" id="menu-scan-processing-bar"></div>
        </div>
        <ul class="wn-scan-processing__steps" id="menu-scan-processing-steps">
            <li data-step="upload" class="is-active"><i class="ri-upload-cloud-line"></i> Subida</li>
            <li data-step="read"><i class="ri-eye-line"></i> Lectura</li>
            <li data-step="sections"><i class="ri-layout-grid-line"></i> Secciones</li>
            <li data-step="prices"><i class="ri-price-tag-3-line"></i> Precios</li>
            <li data-step="done"><i class="ri-check-line"></i> Listo</li>
        </ul>
        <p class="wn-scan-processing__hint">Puede tardar hasta un minuto. No cierres esta pestaña.</p>
    </div>
</div>

{{-- Modal cámara con guía visual --}}
<div class="modal fade" id="menu-scan-camera-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen-sm-down modal-dialog-centered">
        <div class="modal-content wn-camera-modal">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0">Fotografiar carta</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0 position-relative bg-dark">
                <video id="menu-scan-video" class="wn-camera-video" playsinline autoplay muted></video>
                <canvas id="menu-scan-canvas" class="d-none"></canvas>
                <div class="wn-camera-overlay" aria-hidden="true">
                    <p class="wn-camera-overlay__hint">Coloca la carta dentro del marco</p>
                    <div class="wn-camera-frame"></div>
                    <p class="wn-camera-overlay__tip">Mantén el móvil quieto y con buena luz</p>
                </div>
            </div>
            <div class="modal-footer justify-content-center gap-2 py-3">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-lg rounded-pill px-4" id="menu-scan-capture-btn">
                    <i class="ri-camera-fill me-1"></i> Capturar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('materio/js/webnu-menu-scan.js') }}"></script>
@endpush

