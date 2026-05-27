@php
    $canPhotos = isset($planFeatures['photos']) ? (bool) $planFeatures['photos'] : true;
    $canVideos = isset($planFeatures['videos']) ? (bool) $planFeatures['videos'] : false;
@endphp
<div class="col webnu-product-col" id="{{ $product->id }}">
    <article class="card h-100 webnu-dish-card">
        <div class="webnu-dish-card__media"
             data-product-media
             data-product-id="{{ $product->id }}"
             data-upload-image-url="{{ route('admin.products.upload_image', $product) }}"
             data-upload-video-url="{{ route('admin.products.upload_video', $product) }}"
             data-token="{{ csrf_token() }}">
            @if ($product->highlight)
                <div class="webnu-dish-card__badge">
                    @include('admin.sections.partials.product-highlight-badge', ['highlight' => $product->highlight])
                </div>
            @endif
            @if ($product->image)
                <img src="{{ asset('img/' . $product->image) }}" alt="" class="webnu-dish-card__img" data-product-image>
            @else
                <div class="webnu-dish-card__img webnu-dish-card__img--placeholder webnu-dish-card__placeholder" data-product-placeholder>
                    <i class="ri ri-restaurant-2-line webnu-dish-card__placeholder-icon"></i>
                    <div class="webnu-dish-card__placeholder-actions">
                        <button type="button" class="webnu-dish-card__add-media" data-add-media="image" title="Añadir foto">
                            <i class="ri ri-image-add-line"></i>
                            <span>Añadir foto</span>
                        </button>
                        <button type="button" class="webnu-dish-card__add-media" data-add-media="video" title="Añadir vídeo"{{ $canVideos ? '' : ' data-locked="1"' }}>
                            <i class="ri ri-video-add-line"></i>
                            <span>Añadir vídeo</span>
                            @if(!$canVideos)<span class="webnu-dish-card__media-lock"><i class="ri ri-vip-crown-line"></i></span>@endif
                        </button>
                    </div>
                    <div class="webnu-dish-card__media-loading" data-media-loading hidden>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Subiendo…
                    </div>
                </div>
                <input type="file" accept="image/*" capture="environment" class="visually-hidden" data-product-image-input>
                <input type="file" accept="video/*" capture="environment" class="visually-hidden" data-product-video-input>
            @endif
        </div>
        <div class="card-body d-flex flex-column pb-2">
            <h6 class="webnu-dish-card__title mb-1">{{ $product->name }}</h6>
            <p class="webnu-dish-card__price mb-1">{{ number_format((float) $product->price_unit, 2, ',', '') }} &euro;</p>
            @if ($product->description)
                <p class="webnu-dish-card__desc text-muted mb-2">{{ Str::limit($product->description, 72) }}</p>
            @endif
            @if ($product->allergens->count())
                <div class="d-flex flex-wrap gap-1 mb-2">
                    @foreach ($product->allergens as $allergen)
                        @include('admin.sections.partials.allergen-icon', ['allergen' => $allergen, 'size' => 26])
                    @endforeach
                </div>
            @endif
            <div class="mt-auto d-flex align-items-center justify-content-between gap-2 pt-2 border-top">
                <label class="webnu-visibility-pill mb-0">
                    <input type="checkbox"
                           class="webnu-visibility-pill__input product-enabled-toggle"
                           id="product-enabled-{{ $product->id }}"
                           data-id="{{ $product->id }}"
                           data-token="{{ csrf_token() }}"
                           data-url="{{ route('admin.products.toggle_enabled', $product) }}"
                           {{ $product->enabled ? 'checked' : '' }}>
                    <span class="webnu-visibility-pill__ui">
                        <span class="webnu-visibility-pill__on">Visible</span>
                        <span class="webnu-visibility-pill__off">Oculto</span>
                    </span>
                </label>
                <div class="d-flex gap-1">
                    <a href="{{ route('admin.products.edit', $product) }}"
                       class="btn btn-sm btn-icon btn-text-secondary"
                       title="Editar">
                        <i class="ri ri-pencil-line"></i>
                    </a>
                    <button type="button"
                            class="btn btn-sm btn-icon btn-text-danger product-delete"
                            data-id="{{ $product->id }}"
                            section-id="{{ $section->id }}"
                            data-bs-toggle="modal"
                            data-bs-target="#modal-delete-product"
                            title="Eliminar">
                        <i class="ri ri-delete-bin-6-line"></i>
                    </button>
                </div>
            </div>
        </div>
        <i class="ri ri-draggable webnu-dish-card__drag webnu-drag-handle" title="Arrastrar"></i>
    </article>
</div>

