@php
    $prefix = $mode === 'add' ? 'product_add' : 'product_modify';
    $idPrefix = $mode === 'add' ? 'product-add' : 'product-modify';
    $maxSeconds = config('product_media.max_video_seconds', 30);
    $maxMb = round(config('product_media.max_video_kb', 25600) / 1024);
    $canVideos = $planFeatures['videos'] ?? true;
@endphp

<div class="card mb-4 product-media-block" data-media-mode="{{ $mode }}">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title mb-0">Foto y vídeo</h5>
        <span class="text-muted small">Sube archivos o usa la cámara del dispositivo</span>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="webnu-media-panel">
                    <div class="webnu-media-panel__head">
                        <span class="webnu-media-panel__icon webnu-media-panel__icon--photo"><i class="ri-image-line"></i></span>
                        <div>
                            <span class="webnu-media-panel__title">Foto del plato</span>
                            <span class="webnu-media-panel__meta">JPG, PNG o WebP</span>
                        </div>
                    </div>

                    @if ($mode === 'modify')
                        <div class="webnu-media-existing" id="product-modify-image-existing" style="display:none;">
                            <img src="" id="product-modify-image-ok" alt="" class="webnu-media-existing__preview">
                            <button type="button"
                                    class="btn btn-sm btn-label-danger product-image-delete"
                                    data-token="{{ csrf_token() }}"
                                    id="delete-image-product-id"
                                    product-id="">
                                <i class="ri-delete-bin-line me-1"></i> Quitar foto
                            </button>
                        </div>
                    @endif

                    <div class="webnu-media-preview" id="{{ $idPrefix }}-image-preview" style="display:none;">
                        <img src="" alt="Vista previa" class="webnu-media-existing__preview">
                    </div>

                    <div class="webnu-media-controls">
                        <div class="webnu-media-mode btn-group btn-group-sm w-100" role="group">
                            <input type="radio" class="btn-check product-image-mode-radio" name="{{ $idPrefix }}-image-mode" id="{{ $idPrefix }}-image-upload" value="upload" checked>
                            <label class="btn btn-outline-secondary" for="{{ $idPrefix }}-image-upload"><i class="ri-upload-2-line me-1"></i> Subir</label>
                            <input type="radio" class="btn-check product-image-mode-radio" name="{{ $idPrefix }}-image-mode" id="{{ $idPrefix }}-image-camera" value="camera">
                            <label class="btn btn-outline-secondary product-image-camera-tab-item" for="{{ $idPrefix }}-image-camera"><i class="ri-camera-line me-1"></i> Hacer foto</label>
                        </div>

                        <div class="product-image-panel product-image-panel--upload" id="{{ $idPrefix }}-image-upload-panel">
                            <label class="webnu-file-drop webnu-file-drop--compact d-block" for="{{ $idPrefix }}-image">
                                <i class="ri-upload-cloud-2-line"></i>
                                <span>Seleccionar imagen</span>
                                <input type="file"
                                       accept="image/*"
                                       capture="environment"
                                       name="{{ $prefix }}_image"
                                       id="{{ $idPrefix }}-image"
                                       class="product-image-input webnu-file-drop__input">
                            </label>
                        </div>

                        <div class="product-image-panel product-image-panel--camera hidden" id="{{ $idPrefix }}-image-camera-panel">
                            <p class="text-muted small product-photo-unsupported" style="display:none;">
                                Tu navegador no permite usar la cámara. Usa «Subir» en su lugar.
                            </p>
                            <video class="product-photo-preview rounded border bg-dark w-100 mb-2" playsinline autoplay muted style="max-height: 160px;"></video>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-primary product-photo-capture">
                                    <i class="ri-camera-fill me-1"></i> Capturar foto
                                </button>
                                <button type="button" class="btn btn-sm btn-label-secondary product-photo-retake" style="display:none;">Otra foto</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                @if ($canVideos)
                <div class="webnu-media-panel">
                    <div class="webnu-media-panel__head">
                        <span class="webnu-media-panel__icon webnu-media-panel__icon--video"><i class="ri-video-line"></i></span>
                        <div>
                            <span class="webnu-media-panel__title">Vídeo del plato</span>
                            <span class="webnu-media-panel__meta">Máx. {{ $maxSeconds }}s · {{ $maxMb }} MB</span>
                        </div>
                    </div>

                    @if ($mode === 'modify')
                        <div class="webnu-media-existing" id="product-modify-video-existing" style="display:none;">
                            <video id="product-modify-video-ok" class="webnu-media-existing__preview w-100" controls playsinline></video>
                            <button type="button"
                                    class="btn btn-sm btn-label-danger product-video-delete"
                                    data-token="{{ csrf_token() }}"
                                    id="delete-video-product-id"
                                    product-id="">
                                <i class="ri-delete-bin-line me-1"></i> Quitar vídeo
                            </button>
                        </div>
                    @endif

                    <div class="webnu-media-controls">
                        <div class="webnu-media-mode btn-group btn-group-sm w-100 mb-2" role="group">
                            <input type="radio" class="btn-check product-video-mode-radio" name="{{ $idPrefix }}-video-mode" id="{{ $idPrefix }}-video-upload" value="upload" checked>
                            <label class="btn btn-outline-secondary" for="{{ $idPrefix }}-video-upload"><i class="ri-upload-2-line me-1"></i> Subir</label>
                            <input type="radio" class="btn-check product-video-mode-radio" name="{{ $idPrefix }}-video-mode" id="{{ $idPrefix }}-video-record" value="record">
                            <label class="btn btn-outline-secondary product-video-record-tab-item" for="{{ $idPrefix }}-video-record"><i class="ri-record-circle-line me-1"></i> Grabar</label>
                        </div>

                        <div class="product-video-panel product-video-panel--upload" id="{{ $idPrefix }}-video-upload-panel">
                            <label class="webnu-file-drop webnu-file-drop--compact d-block" for="{{ $idPrefix }}-video">
                                <i class="ri-film-line"></i>
                                <span>Seleccionar vídeo</span>
                                <input type="file"
                                       accept="video/*"
                                       name="{{ $prefix }}_video"
                                       id="{{ $idPrefix }}-video"
                                       class="product-video-file-input webnu-file-drop__input">
                            </label>
                        </div>

                        <div class="product-video-panel product-video-panel--record hidden" id="{{ $idPrefix }}-video-record-panel">
                            <p class="text-muted small product-recorder-unsupported" style="display:none;">
                                Tu navegador no permite grabar. Usa «Subir» en su lugar.
                            </p>
                            <video class="product-recorder-preview rounded border bg-dark w-100 mb-2" playsinline muted style="max-height: 160px;"></video>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-danger product-recorder-start">
                                    <i class="ri-record-circle-line me-1"></i> Grabar
                                </button>
                                <button type="button" class="btn btn-sm btn-label-secondary product-recorder-stop" disabled>Parar</button>
                                <span class="text-muted small product-recorder-timer">0:00 / 0:{{ str_pad((string) $maxSeconds, 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                        </div>

                        <div class="webnu-media-preview mt-2" id="{{ $idPrefix }}-video-preview" style="display:none;">
                            <video src="" controls playsinline class="webnu-media-existing__preview w-100"></video>
                        </div>
                    </div>
                </div>
                @else
                @component('admin.partials.plan-feature-lock', [
                    'feature' => 'videos',
                    'message' => 'Añade reels y vídeos cortos en cada plato con el plan Plus.',
                    'class' => 'p-1',
                ])
                <div class="webnu-media-panel">
                    <div class="webnu-media-panel__head">
                        <span class="webnu-media-panel__icon webnu-media-panel__icon--video"><i class="ri-video-line"></i></span>
                        <div>
                            <span class="webnu-media-panel__title">Vídeo del plato @include('admin.partials.plan-pro-badge', ['label' => 'Plus', 'size' => 'xs'])</span>
                            <span class="webnu-media-panel__meta">Máx. {{ $maxSeconds }}s · {{ $maxMb }} MB</span>
                        </div>
                    </div>
                    <div class="webnu-media-controls">
                        <div class="webnu-media-mode btn-group btn-group-sm w-100 mb-2" role="group">
                            <button type="button" class="btn btn-outline-secondary" disabled><i class="ri-upload-2-line me-1"></i> Subir</button>
                            <button type="button" class="btn btn-outline-secondary" disabled><i class="ri-record-circle-line me-1"></i> Grabar</button>
                        </div>
                        <label class="webnu-file-drop webnu-file-drop--compact d-block opacity-75">
                            <i class="ri-film-line"></i>
                            <span>Seleccionar vídeo</span>
                        </label>
                    </div>
                </div>
                @endcomponent
                @endif
            </div>
        </div>
    </div>
</div>
