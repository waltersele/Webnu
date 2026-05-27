@php
    $visibleCount = $section->products->where('enabled', true)->count();
    $totalCount = $section->products->count();
@endphp
<article id="{{ $section->id }}" class="card mb-4 webnu-section-card is-open">
    <div class="card-header webnu-section-card__header d-flex flex-wrap align-items-center gap-2 border-0 pb-0">
        <i class="ri ri-draggable icon-20px text-muted webnu-section-drag-handle" title="Arrastrar sección"></i>
        <div class="me-auto min-w-0">
            <h5 class="card-title mb-0">{{ $section->name }}</h5>
            <p class="text-muted small mb-0">
                {{ $totalCount }} {{ $totalCount === 1 ? 'plato' : 'platos' }}
                @if ($section->enabled)
                    &middot; Visibles en web: {{ $visibleCount }}
                @endif
            </p>
        </div>
        <div class="dropdown">
            <button type="button"
                    class="btn btn-icon btn-text-secondary wn-section-kebab"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Acciones de sección">
                <i class="ri ri-more-2-fill"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <button type="button" class="dropdown-item modify-section-btn"
                            data-id="{{ $section->id }}" data-name="{{ $section->name }}"
                            data-enabled="{{ $section->enabled }}"
                            data-bs-toggle="modal" data-bs-target="#modal-modify-section">
                        <i class="ri ri-pencil-line me-2"></i> Editar sección
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item text-danger delete-section-btn"
                            data-id="{{ $section->id }}" data-bs-toggle="modal" data-bs-target="#modal-delete-section">
                        <i class="ri ri-delete-bin-line me-2"></i> Eliminar sección
                    </button>
                </li>
            </ul>
        </div>
        <button type="button" class="btn btn-sm btn-icon btn-text-secondary webnu-section-toggle" aria-label="Mostrar u ocultar">
            <i class="ri ri-arrow-down-s-line"></i>
        </button>
    </div>

    <div class="webnu-section-card__body card-body pt-3">
        {{-- Vista cuadrícula --}}
        <div class="webnu-menu-grid-view">
            @if ($section->products->isEmpty())
                <div class="wn-empty-state">
                    <i class="ri ri-restaurant-2-line"></i>
                    <h6>Aún no hay platos en esta sección</h6>
                    <p class="mb-0 small">Empieza añadiendo tu primer plato para que tus clientes lo vean.</p>
                    <button type="button"
                            class="btn btn-primary product-add-btn"
                            section-id="{{ $section->id }}"
                            data-bs-toggle="modal"
                            data-bs-target="#modal-add-product">
                        <i class="ri ri-add-line me-1"></i> Añadir primer plato
                    </button>
                </div>
            @else
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 sortable-product"
                     section-id="{{ $section->id }}"
                     data-token="{{ csrf_token() }}">
                    @foreach ($section->products as $product)
                        @include('admin.sections.partials.menu-product-card', ['product' => $product, 'section' => $section])
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Vista lista --}}
        <div class="webnu-menu-list-view">
            @if ($section->products->isEmpty())
                <div class="wn-empty-state">
                    <i class="ri ri-restaurant-2-line"></i>
                    <h6>Aún no hay platos en esta sección</h6>
                    <p class="mb-0 small">Empieza añadiendo tu primer plato para que tus clientes lo vean.</p>
                    <button type="button"
                            class="btn btn-primary product-add-btn"
                            section-id="{{ $section->id }}"
                            data-bs-toggle="modal"
                            data-bs-target="#modal-add-product">
                        <i class="ri ri-add-line me-1"></i> Añadir primer plato
                    </button>
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 2.5rem;"></th>
                            <th>Plato</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Alérgenos</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0 sortable-product" section-id="{{ $section->id }}" data-token="{{ csrf_token() }}">
                        @foreach ($section->products as $product)
                            <tr id="{{ $product->id }}"
                                class="wn-list-row"
                                data-edit-url="{{ route('admin.products.edit', $product) }}"
                                role="link"
                                tabindex="0">
                                <td class="align-middle">
                                    <i class="ri ri-draggable icon-18px text-muted webnu-drag-handle"></i>
                                </td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center gap-3">
                                        @if ($product->image)
                                            <img src="{{ asset('img/' . $product->image) }}" alt="" class="wn-list-thumb">
                                        @else
                                            <span class="wn-list-thumb wn-list-thumb--placeholder">
                                                <i class="ri ri-restaurant-2-line"></i>
                                            </span>
                                        @endif
                                        <div class="min-w-0">
                                            <span class="wn-list-product-name d-inline-flex flex-wrap align-items-center gap-2">
                                                <span class="text-truncate">{{ $product->name }}</span>
                                                @if ($product->highlight)
                                                    @include('admin.sections.partials.product-highlight-badge', ['highlight' => $product->highlight, 'size' => 'sm'])
                                                @endif
                                            </span>
                                            @if ($product->description)
                                                <span class="wn-list-product-desc text-truncate">{{ Str::limit($product->description, 80) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle text-nowrap">
                                    <span class="fw-semibold">{{ number_format((float) $product->price_unit, 2, ',', '') }} &euro;</span>
                                </td>
                                <td class="align-middle">
                                    <div class="form-check form-switch mb-0">
                                        <input type="checkbox" class="form-check-input product-enabled-toggle"
                                               data-url="{{ route('admin.products.toggle_enabled', $product) }}"
                                               data-token="{{ csrf_token() }}"
                                               {{ $product->enabled ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    @if ($product->allergens->count())
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($product->allergens as $allergen)
                                                @include('admin.sections.partials.allergen-icon', ['allergen' => $allergen, 'size' => 28])
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted small">&mdash;</span>
                                    @endif
                                </td>
                                <td class="align-middle text-end text-nowrap">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-icon btn-text-secondary" title="Editar">
                                        <i class="ri ri-pencil-line"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-icon btn-text-danger product-delete"
                                            data-id="{{ $product->id }}" section-id="{{ $section->id }}"
                                            data-bs-toggle="modal" data-bs-target="#modal-delete-product">
                                        <i class="ri ri-delete-bin-6-line"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- CTA pie: Añadir plato --}}
        <button type="button"
                class="wn-section-add-product product-add-btn"
                section-id="{{ $section->id }}"
                data-bs-toggle="modal"
                data-bs-target="#modal-add-product">
            <i class="ri ri-add-line"></i> Añadir plato a {{ $section->name }}
        </button>
    </div>
</article>
