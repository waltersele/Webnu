@php
    $prefix = $mode === 'add' ? 'product_add' : 'product_modify';
    $idPrefix = $mode === 'add' ? 'product-add' : 'product-modify';
    $maxSeconds = config('product_media.max_video_seconds', 20);
    $maxMb = round(config('product_media.max_video_kb', 15360) / 1024);
    $tvHeight = config('product_media.tv_max_height', 720);
    $canVideos = $planFeatures['videos'] ?? true;
    $showVideoUpgrade = ! $canVideos && ($upgradeTriggers['show_video_trigger'] ?? true);
@endphp

<div class="card mb-4 product-media-block" data-media-mode="{{ $mode }}">
    <div class="card-header">
        <h5 class="card-title mb-0">Foto y vídeo</h5>
        <p class="text-muted small mb-0">Sube desde el dispositivo o usa la cámara para hacer una foto/grabar ahí mismo.</p>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-lg-{{ $canVideos ? '6' : '12' }}">
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

                    <label class="webnu-file-drop d-block" for="{{ $idPrefix }}-image">
                        <i class="ri-upload-cloud-2-line"></i>
                        <span class="webnu-file-drop__title">Subir o hacer foto</span>
                        <span class="webnu-file-drop__hint">Tu móvil te dejará elegir entre cámara, galería o archivos.</span>
                        <input type="file"
                               accept="image/*"
                               name="{{ $prefix }}_image"
                               id="{{ $idPrefix }}-image"
                               class="product-image-input webnu-file-drop__input">
                    </label>
                </div>
            </div>

            @if ($canVideos)
            <div class="col-lg-6">
                <div class="webnu-media-panel">
                    <div class="webnu-media-panel__head">
                        <span class="webnu-media-panel__icon webnu-media-panel__icon--video"><i class="ri-video-line"></i></span>
                        <div>
                            <span class="webnu-media-panel__title">Vídeo del plato</span>
                            <span class="webnu-media-panel__meta">Máx. {{ $maxSeconds }}s · {{ $maxMb }} MB · hasta {{ $tvHeight }}p en TV</span>
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

                    <div class="webnu-media-preview" id="{{ $idPrefix }}-video-preview" style="display:none;">
                        <video src="" controls playsinline class="webnu-media-existing__preview w-100"></video>
                    </div>

                    <label class="webnu-file-drop d-block" for="{{ $idPrefix }}-video">
                        <i class="ri-film-line"></i>
                        <span class="webnu-file-drop__title">Subir o grabar vídeo</span>
                        <span class="webnu-file-drop__hint">Cámara, galería o archivos. Webnu lo comprime a H.264 ligero (sin audio) para móvil y Smart TV.</span>
                        <input type="file"
                               accept="video/*"
                               name="{{ $prefix }}_video"
                               id="{{ $idPrefix }}-video"
                               class="product-video-file-input webnu-file-drop__input">
                    </label>
                </div>
            </div>
            @elseif ($showVideoUpgrade)
            <div class="col-lg-6 d-none d-lg-block">
                <div class="wn-upgrade-trigger-teaser rounded-xl border border-dashed p-4 h-100 d-flex flex-column justify-content-center text-center">
                    <i class="ri-play-circle-line text-primary mb-2" style="font-size: 2.5rem;"></i>
                    <p class="text-muted small mb-3">Los vídeos en platos aumentan el apetito y las ventas. Disponibles en Plus.</p>
                    <button type="button" class="btn btn-sm btn-primary" data-upgrade-trigger="video">
                        Saber más · Plus
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
