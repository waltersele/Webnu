@php
    $prefix = $mode === 'add' ? 'product_add' : 'product_modify';
    $idPrefix = $mode === 'add' ? 'product-add' : 'product-modify';
    $maxSeconds = config('product_media.max_video_seconds', 30);
    $maxMb = round(config('product_media.max_video_kb', 25600) / 1024);
@endphp

<div class="card mb-4 product-media-block" data-media-mode="{{ $mode }}">
    <div class="card-header">
        <h5 class="card-title mb-0">Foto y vídeo</h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <p class="fw-medium mb-2">Foto</p>
                @if ($mode === 'modify')
                    <div class="mb-3" id="product-modify-image-existing" style="display:none;">
                        <img src="" id="product-modify-image-ok" alt="" class="rounded border d-block mb-2" style="max-height: 120px; max-width: 100%; object-fit: cover;">
                        <button type="button"
                                class="btn btn-sm btn-label-danger product-image-delete"
                                data-token="{{ csrf_token() }}"
                                id="delete-image-product-id"
                                product-id="">
                            <i class="ri ri-delete-bin-line me-1"></i> Quitar foto
                        </button>
                    </div>
                @endif
                <div class="product-image-preview mb-3" id="{{ $idPrefix }}-image-preview" style="display:none;">
                    <img src="" alt="Vista previa" class="rounded border d-block" style="max-height: 120px; max-width: 100%; object-fit: cover;">
                </div>
                <label class="webnu-file-drop d-block" for="{{ $idPrefix }}-image">
                    <i class="ri ri-upload-cloud-2-line"></i>
                    <span>Seleccionar imagen</span>
                    <input type="file"
                           accept="image/*"
                           name="{{ $prefix }}_image"
                           id="{{ $idPrefix }}-image"
                           class="product-image-input webnu-file-drop__input">
                </label>
            </div>

            <div class="col-md-6">
                <p class="fw-medium mb-2">Vídeo <small class="text-muted fw-normal">(máx. {{ $maxSeconds }}s, {{ $maxMb }} MB)</small></p>
                @if ($mode === 'modify')
                    <div class="mb-3" id="product-modify-video-existing" style="display:none;">
                        <video id="product-modify-video-ok" class="rounded border d-block mb-2 w-100" controls playsinline style="max-height: 140px;"></video>
                        <button type="button"
                                class="btn btn-sm btn-label-danger product-video-delete"
                                data-token="{{ csrf_token() }}"
                                id="delete-video-product-id"
                                product-id="">
                            <i class="ri ri-delete-bin-line me-1"></i> Quitar vídeo
                        </button>
                    </div>
                @endif

                <div class="btn-group btn-group-sm mb-3 product-video-mode" role="group">
                    <input type="radio" class="btn-check product-video-mode-radio" name="{{ $idPrefix }}-video-mode" id="{{ $idPrefix }}-video-upload" value="upload" checked>
                    <label class="btn btn-outline-primary" for="{{ $idPrefix }}-video-upload">Subir</label>
                    <input type="radio" class="btn-check product-video-mode-radio" name="{{ $idPrefix }}-video-mode" id="{{ $idPrefix }}-video-record" value="record">
                    <label class="btn btn-outline-primary product-video-record-tab-item" for="{{ $idPrefix }}-video-record">Grabar</label>
                </div>

                <div class="product-video-panel product-video-panel--upload" id="{{ $idPrefix }}-video-upload-panel">
                    <label class="webnu-file-drop d-block" for="{{ $idPrefix }}-video">
                        <i class="ri ri-video-line"></i>
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
                    <video class="product-recorder-preview rounded border bg-light w-100 mb-2" playsinline muted style="max-height: 140px;"></video>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-danger product-recorder-start">
                            <i class="ri ri-record-circle-line me-1"></i> Grabar
                        </button>
                        <button type="button" class="btn btn-sm btn-label-secondary product-recorder-stop" disabled>Parar</button>
                        <span class="text-muted small product-recorder-timer">0:00 / 0:{{ str_pad((string) $maxSeconds, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>

                <div class="product-video-upload-preview mt-3" id="{{ $idPrefix }}-video-preview" style="display:none;">
                    <video src="" controls playsinline class="rounded border w-100" style="max-height: 140px;"></video>
                </div>
            </div>
        </div>
    </div>
</div>
