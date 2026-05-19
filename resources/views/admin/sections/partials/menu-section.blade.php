@php
    $visibleCount = $section->products->where('enabled', true)->count();
    $totalCount = $section->products->count();
@endphp
<article id="{{ $section->id }}" class="card mb-4 webnu-section-card is-open">
    <div class="card-header webnu-section-card__header d-flex flex-wrap align-items-center gap-2 border-0 pb-0">
        <i class="ri ri-draggable icon-20px text-muted webnu-drag-handle" title="Arrastrar sección"></i>
        <div class="me-auto min-w-0">
            <h5 class="card-title mb-0">{{ $section->name }}</h5>
            <p class="text-muted small mb-0">
                {{ $totalCount }} {{ $totalCount === 1 ? 'plato' : 'platos' }}
                @if ($section->enabled)
                    &bull; Visibles en web: {{ $visibleCount }}
                @endif
            </p>
        </div>
        <button type="button"
                class="btn btn-sm btn-link text-primary product-add-btn px-2"
                section-id="{{ $section->id }}"
                data-bs-toggle="modal"
                data-bs-target="#modal-add-product">
            <i class="ri ri-add-line"></i> Añadir plato
        </button>
        <button type="button" class="btn btn-sm btn-icon btn-text-secondary webnu-section-toggle" aria-label="Mostrar u ocultar">
            <i class="ri ri-arrow-down-s-line"></i>
        </button>
    </div>

    <div class="webnu-section-card__body card-body pt-3">
        {{-- Vista cuadrícula --}}
        <div class="webnu-menu-grid-view">
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 sortable-product"
                 section-id="{{ $section->id }}"
                 data-token="{{ csrf_token() }}">
                @forelse ($section->products as $product)
                    @include('admin.sections.partials.menu-product-card', ['product' => $product, 'section' => $section])
                @empty
                    <div class="col-12">
                        <p class="text-center text-muted py-4 mb-0">No hay platos en esta sección.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Vista lista --}}
        <div class="webnu-menu-list-view d-none">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover mb-0">
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
                        @forelse ($section->products as $product)
                            <tr id="{{ $product->id }}">
                                <td class="align-middle">
                                    <i class="ri ri-draggable icon-18px text-muted webnu-drag-handle"></i>
                                </td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center gap-3">
                                        @if ($product->image)
                                            <img src="{{ asset('img/' . $product->image) }}" alt="" class="rounded flex-shrink-0" width="42" height="42" style="object-fit: cover;">
                                        @else
                                            <span class="avatar avatar-sm flex-shrink-0">
                                                <span class="avatar-initial rounded bg-label-secondary"><i class="ri ri-restaurant-2-line"></i></span>
                                            </span>
                                        @endif
                                        <div class="min-w-0">
                                            <span class="fw-medium d-inline-flex flex-wrap align-items-center gap-2 text-truncate">
                                                <span class="text-truncate">{{ $product->name }}</span>
                                                @if ($product->highlight)
                                                    @include('admin.sections.partials.product-highlight-badge', ['highlight' => $product->highlight, 'size' => 'sm'])
                                                @endif
                                            </span>
                                            @if ($product->description)
                                                <small class="text-muted text-truncate d-block">{{ Str::limit($product->description, 60) }}</small>
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
                                        <span class="text-muted small">—</span>
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
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No hay platos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-end gap-2 mt-3 pt-3 border-top">
            <div class="dropdown">
                <button type="button" class="btn btn-sm btn-label-secondary dropdown-toggle" data-bs-toggle="dropdown">Sección</button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <button type="button" class="dropdown-item modify-section-btn"
                                data-id="{{ $section->id }}" data-name="{{ $section->name }}"
                                data-enabled="{{ $section->enabled }}"
                                data-bs-toggle="modal" data-bs-target="#modal-modify-section">
                            <i class="ri ri-settings-3-line me-2"></i> Editar sección
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
        </div>
    </div>
</article>

