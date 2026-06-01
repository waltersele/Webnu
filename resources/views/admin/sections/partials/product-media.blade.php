@php
    $prefix = $mode === 'add' ? 'product_add' : 'product_modify';
    $idPrefix = $mode === 'add' ? 'product-add' : 'product-modify';
    $maxSeconds = config('product_media.max_video_seconds', 20);
    $maxMb = round(config('product_media.max_video_kb', 15360) / 1024);
    $tvHeight = config('product_media.tv_max_height', 720);
    $canVideos = $planFeatures['videos'] ?? true;
    $showVideoUpgrade = ! $canVideos && ($upgradeTriggers['show_video_trigger'] ?? true);
@endphp

<div class="webnu-product-media-group mb-4">
<div class="card product-media-block webnu-product-media" data-media-mode="{{ $mode }}">
    <div class="card-body webnu-product-media__body">
        <header class="webnu-product-media__intro">
            <h5 class="webnu-product-media__title">Foto y vídeo</h5>
            <p class="webnu-product-media__lead">Sube desde el dispositivo o usa la cámara para hacer una foto o grabar ahí mismo.</p>
        </header>

        <div class="row g-3 g-lg-4">
            <div class="col-12 col-lg-{{ $canVideos ? '6' : '12' }}">
                <div class="webnu-media-panel webnu-media-panel--photo" data-media-kind="photo">
                    <div class="webnu-media-panel__head">
                        <div class="webnu-media-panel__head-main">
                            <span class="webnu-media-panel__icon webnu-media-panel__icon--photo" aria-hidden="true">
                                <i class="ri-image-line"></i>
                            </span>
                            <div>
                                <span class="webnu-media-panel__eyebrow">Foto del plato</span>
                                <span class="webnu-media-panel__meta">JPG, PNG o WebP</span>
                            </div>
                        </div>
                        <span class="webnu-media-status" data-photo-status hidden>
                            <i class="ri-checkbox-circle-fill" aria-hidden="true"></i>
                            Subida
                        </span>
                    </div>

                    <input type="file"
                           accept="image/*"
                           name="{{ $prefix }}_image"
                           id="{{ $idPrefix }}-image"
                           class="product-image-input webnu-media-file-input"
                           tabindex="-1">

                    <div class="webnu-media-stage webnu-media-stage--empty" data-photo-empty>
                        <label class="webnu-file-drop webnu-file-drop--panel" for="{{ $idPrefix }}-image">
                            <span class="webnu-file-drop__icon" aria-hidden="true"><i class="ri-camera-line"></i></span>
                            <span class="webnu-file-drop__title">Subir o hacer foto</span>
                            <span class="webnu-file-drop__hint">Cámara, galería o archivos en tu dispositivo.</span>
                        </label>
                    </div>

                    <div class="webnu-media-stage webnu-media-stage--filled" data-photo-filled hidden>
                        <div class="webnu-media-preview-frame">
                            <img src=""
                                 alt="Vista previa de la foto del plato"
                                 class="webnu-media-preview-frame__img"
                                 @if ($mode === 'modify') id="product-modify-image-ok" @else id="{{ $idPrefix }}-image-preview-img" @endif
                                 data-photo-preview-img>
                        </div>
                        <div class="webnu-media-actions">
                            <label class="webnu-media-btn webnu-media-btn--change" for="{{ $idPrefix }}-image">
                                <i class="ri-refresh-line" aria-hidden="true"></i>
                                Cambiar foto
                            </label>
                            @if ($mode === 'modify')
                                <button type="button"
                                        class="webnu-media-btn webnu-media-btn--delete product-image-delete"
                                        data-token="{{ csrf_token() }}"
                                        id="delete-image-product-id"
                                        product-id="">
                                    <i class="ri-delete-bin-line" aria-hidden="true"></i>
                                    Eliminar
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Compatibilidad con product-media.js legacy --}}
                    <div class="webnu-media-preview d-none" id="{{ $idPrefix }}-image-preview" aria-hidden="true">
                        <img src="" alt="">
                    </div>
                </div>
            </div>

            @if ($canVideos)
            <div class="col-12 col-lg-6">
                <div class="webnu-media-panel webnu-media-panel--video" data-media-kind="video">
                    <div class="webnu-media-panel__head">
                        <div class="webnu-media-panel__head-main">
                            <span class="webnu-media-panel__icon webnu-media-panel__icon--video" aria-hidden="true">
                                <i class="ri-video-line"></i>
                            </span>
                            <div>
                                <span class="webnu-media-panel__eyebrow">Vídeo del plato</span>
                                <span class="webnu-media-panel__meta">Máx. {{ $maxSeconds }}s · {{ $maxMb }} MB · hasta {{ $tvHeight }}p en TV</span>
                            </div>
                        </div>
                        <span class="webnu-media-status" data-video-status hidden>
                            <i class="ri-checkbox-circle-fill" aria-hidden="true"></i>
                            Subido
                        </span>
                    </div>

                    <input type="file"
                           accept="video/*"
                           name="{{ $prefix }}_video"
                           id="{{ $idPrefix }}-video"
                           class="product-video-file-input webnu-media-file-input"
                           tabindex="-1">

                    <div class="webnu-media-stage webnu-media-stage--empty" data-video-empty>
                        <label class="webnu-file-drop webnu-file-drop--panel" for="{{ $idPrefix }}-video">
                            <span class="webnu-file-drop__icon" aria-hidden="true"><i class="ri-film-line"></i></span>
                            <span class="webnu-file-drop__title">Subir o grabar vídeo</span>
                            <span class="webnu-file-drop__hint">Webnu lo comprime a H.264 ligero (sin audio) para móvil y Smart TV.</span>
                        </label>
                    </div>

                    <div class="webnu-media-stage webnu-media-stage--filled" data-video-filled hidden>
                        <div class="webnu-media-preview-frame webnu-media-preview-frame--video">
                            <video class="webnu-media-preview-frame__video w-100"
                                   controls
                                   playsinline
                                   @if ($mode === 'modify') id="product-modify-video-ok" @endif
                                   data-video-preview></video>
                        </div>
                        <div class="webnu-media-actions">
                            <label class="webnu-media-btn webnu-media-btn--change" for="{{ $idPrefix }}-video">
                                <i class="ri-refresh-line" aria-hidden="true"></i>
                                Cambiar vídeo
                            </label>
                            @if ($mode === 'modify')
                                <button type="button"
                                        class="webnu-media-btn webnu-media-btn--delete product-video-delete"
                                        data-token="{{ csrf_token() }}"
                                        id="delete-video-product-id"
                                        product-id="">
                                    <i class="ri-delete-bin-line" aria-hidden="true"></i>
                                    Eliminar
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="webnu-media-preview d-none" id="{{ $idPrefix }}-video-preview" aria-hidden="true">
                        <video src="" controls playsinline></video>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if ($showVideoUpgrade)
    <div class="webnu-media-plus-banner-wrap">
        @include('admin.sections.partials.product-media-plus-banner')
    </div>
@endif
</div>
