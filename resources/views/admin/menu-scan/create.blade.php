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
                <strong>{{ $plan['label'] ?? 'Tu plan' }}:</strong>
                @if (($scansRemaining ?? 0) > 0)
                    Te quedan <strong>{{ $scansRemaining }}</strong> de {{ $scanLimit }} escaneos IA correctos
                    @if(($scanPeriod ?? null) === 'monthly')
                        este mes
                    @endif
                    (has usado {{ $scansUsed }}).
                @else
                    Has usado tus {{ $scanLimit }} escaneos IA correctos
                    @if(($scanPeriod ?? null) === 'monthly')
                        de este mes
                    @endif
                    .
                    <a href="{{ $billingUrl }}" class="alert-link">Mejorar plan</a> para más escaneos.
                @endif
                <span class="d-block small mt-1 text-muted">Solo cuentan los escaneos en los que la IA procesa bien la carta; los fallidos no consumen cupo.</span>
            </div>
        @endif

        @php $scanLocked = ! ($canScan ?? true) && $scanLimit !== null; @endphp
        <div class="{{ $scanLocked ? 'wn-plan-feature-lock' : '' }}">
            <div class="{{ $scanLocked ? 'wn-plan-feature-lock__content' : '' }}">
        <form method="POST" action="{{ route('admin.menu-scan.store') }}" enctype="multipart/form-data" id="menu-scan-upload-form">
            @csrf
            <input type="file" name="files[]" id="menu-scan-files" accept="image/jpeg,image/png,image/webp,application/pdf" multiple class="d-none">
            <input type="file" id="menu-scan-camera-native" accept="image/*" capture="environment" class="d-none">

            <div class="wn-menu-scan-start py-4 mb-2">
                <p class="text-muted text-center mb-4">Fotografía tu carta o sube un PDF; la digitalizamos con IA. Revisamos la nitidez antes de enviar para ahorrarte escaneos fallidos.</p>
                <div class="wn-menu-scan-start__actions">
                    <button type="button"
                            class="btn btn-primary btn-lg wn-menu-scan-start__btn"
                            id="menu-scan-start"
                            @if(! $scanConfigured || ! ($canScan ?? true)) disabled @endif>
                        <i class="ti ti-scan me-2"></i> Escanear carta
                    </button>
                    <button type="button"
                            class="btn btn-primary btn-lg wn-menu-scan-start__btn"
                            id="menu-scan-pick-files"
                            @if(! $scanConfigured || ! ($canScan ?? true)) disabled @endif>
                        <i class="ti ti-upload me-2"></i> Subir PDF o fotos
                    </button>
                </div>
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
                    'message' => 'Has usado tus escaneos IA incluidos. Mejora tu plan para seguir digitalizando cartas.',
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
                <i class="ti ti-sparkles"></i>
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
            <li data-step="upload" class="is-active"><i class="ti ti-upload"></i> Subida</li>
            <li data-step="read"><i class="ti ti-eye"></i> Lectura</li>
            <li data-step="sections"><i class="ti ti-layout-grid"></i> Secciones</li>
            <li data-step="prices"><i class="ti ti-tag"></i> Precios</li>
            <li data-step="done"><i class="ti ti-check"></i> Listo</li>
        </ul>
        <p class="wn-scan-processing__hint">Puede tardar hasta un minuto. No cierres esta pestaña.</p>
    </div>
</div>

{{-- Guía: foto perfecta (se abre al pulsar Escanear) --}}
<div class="modal fade" id="menu-scan-guide-modal" tabindex="-1" aria-labelledby="menu-scan-guide-title" aria-hidden="true" data-bs-backdrop="static" data-guide-user-id="{{ auth()->id() }}">
    <div class="modal-dialog modal-dialog-centered wn-scan-guide-modal__dialog">
        <div class="modal-content wn-scan-guide-modal border-0">
            <button type="button" class="btn-close wn-scan-guide-modal__close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            <div class="modal-body wn-scan-guide-modal__body">
                <p class="wn-scan-guide-step-label" id="menu-scan-guide-step-label">
                    Paso <span id="menu-scan-guide-step-num">1</span> de 3
                </p>
                <div class="wn-scan-guide-carousel" id="menu-scan-guide-carousel" data-guide-carousel>
                    <div class="wn-scan-guide-carousel__track">
                        <div class="wn-scan-guide-slide is-active" data-guide-slide="0">
                            <div class="wn-scan-guide-visual wn-scan-guide-visual--light" aria-hidden="true">
                                <div class="wn-scan-guide-slide__icon"><i class="ti ti-sun"></i></div>
                                <span class="wn-scan-guide-visual__glow"></span>
                            </div>
                            <h2 class="wn-scan-guide-slide__title" id="menu-scan-guide-title">Luz uniforme</h2>
                            <p class="wn-scan-guide-slide__text">Evita sombras y reflejos sobre el papel.</p>
                        </div>
                        <div class="wn-scan-guide-slide" data-guide-slide="1">
                            <div class="wn-scan-guide-visual wn-scan-guide-visual--frame" aria-hidden="true">
                                <div class="wn-scan-guide-slide__icon"><i class="ti ti-focus-2"></i></div>
                                <span class="wn-scan-guide-visual__frame"></span>
                                <span class="wn-scan-guide-visual__scanline"></span>
                            </div>
                            <h2 class="wn-scan-guide-slide__title">Encuadra entero</h2>
                            <p class="wn-scan-guide-slide__text">Toda la página visible; usa el marco de la cámara.</p>
                        </div>
                        <div class="wn-scan-guide-slide" data-guide-slide="2">
                            <div class="wn-scan-guide-visual wn-scan-guide-visual--phone" aria-hidden="true">
                                <div class="wn-scan-guide-slide__icon"><i class="ti ti-device-mobile"></i></div>
                                <span class="wn-scan-guide-visual__phone"></span>
                            </div>
                            <h2 class="wn-scan-guide-slide__title">Móvil en vertical</h2>
                            <p class="wn-scan-guide-slide__text">Sujeta el teléfono paralelo a la mesa, sin inclinar.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer wn-scan-guide-modal__footer border-0">
                <button type="button" class="btn btn-primary btn-lg w-100 wn-scan-guide-cta" id="menu-scan-guide-next">
                    Siguiente
                    <i class="ti ti-arrow-right ms-2"></i>
                </button>
                <button type="button" class="btn btn-primary btn-lg w-100 wn-scan-guide-cta d-none" id="menu-scan-guide-continue">
                    <i class="ti ti-scan me-2"></i>
                    Escanear
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Revisión de foto antes de enviar a IA --}}
<div class="modal fade" id="menu-scan-preview-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0" id="menu-scan-preview-title">Revisar foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="wn-scan-preview-image-wrap mb-3">
                    <img src="" alt="Vista previa" id="menu-scan-preview-img" class="wn-scan-preview-img d-none">
                    <div id="menu-scan-preview-pdf" class="wn-scan-preview-pdf d-none text-center py-5">
                        <i class="ri-file-pdf-line display-4 text-danger"></i>
                        <p class="mb-0 mt-2 fw-medium" id="menu-scan-preview-pdf-name"></p>
                    </div>
                </div>
                <div id="menu-scan-preview-quality" class="mb-3">
                    <span class="badge d-none" id="menu-scan-preview-badge"></span>
                    <p class="small mb-0 mt-2" id="menu-scan-preview-message"></p>
                </div>
            </div>
            <div class="modal-footer flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary" id="menu-scan-preview-retake">Otra foto</button>
                <button type="button" class="btn btn-outline-warning d-none" id="menu-scan-preview-force">Usar igualmente</button>
                <button type="button" class="btn btn-primary" id="menu-scan-preview-accept">Usar esta foto</button>
            </div>
        </div>
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

