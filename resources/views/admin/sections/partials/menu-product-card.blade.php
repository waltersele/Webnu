<div class="col webnu-product-col" id="{{ $product->id }}">
    <article class="card h-100 webnu-dish-card">
        <div class="webnu-dish-card__media">
            @if ($product->highlight)
                <div class="webnu-dish-card__badge">
                    @include('admin.sections.partials.product-highlight-badge', ['highlight' => $product->highlight])
                </div>
            @endif
            @if ($product->image)
                <img src="{{ asset('img/' . $product->image) }}" alt="" class="webnu-dish-card__img">
            @else
                <div class="webnu-dish-card__img webnu-dish-card__img--placeholder">
                    <i class="ri ri-restaurant-2-line"></i>
                </div>
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

