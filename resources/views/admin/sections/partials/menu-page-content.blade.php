@php
    $isPdfMenu = (int) $company->menu_type === 2;
@endphp
<div class="webnu-menu-editor">
    @if ($isPdfMenu)
        <div class="alert alert-info d-flex flex-wrap align-items-center gap-3 mb-4">
            <i class="ri ri-file-pdf-line fs-4 shrink-0"></i>
            <div class="flex-grow-1">
                <strong class="d-block">Tu carta está en modo PDF</strong>
                <p class="mb-0 small">Estás mostrando un PDF como carta pública. Cambia a <em>Carta digital</em> en
                    <a href="#" class="alert-link" data-mi-carta-tab="personalizacion">Personalización</a>
                    para editar platos y secciones aquí.</p>
            </div>
        </div>
    @endif

    <div id="custom-menu" class="{{ $isPdfMenu ? 'opacity-50' : '' }}">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h2 class="h5 mb-0">Secciones y platos</h2>
                <p class="text-muted small mb-0">Arrastra para reordenar. Pulsa una sección para colapsarla.</p>
            </div>
            <div class="wn-view-switch" role="group" aria-label="Vista de platos">
                <button type="button" class="wn-view-switch__btn is-active" data-menu-view="grid" aria-pressed="true" title="Vista cuadrícula">
                    <i class="ri ri-layout-grid-line"></i>
                    <span class="wn-view-switch__label">Cuadrícula</span>
                </button>
                <button type="button" class="wn-view-switch__btn" data-menu-view="list" aria-pressed="false" title="Vista lista">
                    <i class="ri ri-list-check"></i>
                    <span class="wn-view-switch__label">Lista</span>
                </button>
            </div>
        </div>

        <div id="sortable-section" class="webnu-sections-stack webnu-menu-view--grid" data-token="{{ csrf_token() }}">
            @foreach ($sections as $section)
                @include('admin.sections.partials.menu-section', ['section' => $section])
            @endforeach
        </div>

        @if ($sections->isEmpty())
            <div class="card border shadow-none mb-4">
                <div class="card-body text-center py-5">
                    <i class="ri ri-restaurant-line icon-48px text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">Tu carta está vacía</h5>
                    <p class="text-muted mb-4">Crea la primera sección y añade platos.</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-section">
                        <i class="ri ri-add-line me-1"></i> Crear primera sección
                    </button>
                </div>
            </div>
        @endif

        <button type="button" class="btn btn-outline-primary w-100 webnu-add-section-btn py-3" data-bs-toggle="modal" data-bs-target="#modal-add-section">
            <i class="ri ri-add-line me-1"></i> Añadir sección
        </button>
    </div>
</div>
