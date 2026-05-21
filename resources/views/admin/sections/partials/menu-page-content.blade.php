<div class="webnu-menu-editor">
    @if((int) $company->menu_type === 1)
        @include('admin.sections.partials.daily-highlights-form', ['company' => $company])
        @php
            $canTvpikMenu = auth()->check() && app(\App\Services\UserPlanService::class)->canUseTvpik(auth()->user());
            $tvpikLinksCount = $canTvpikMenu
                ? \App\TvpikScreenLink::where('company_id', $company->id)->count()
                : 0;
        @endphp
        @if($canTvpikMenu)
            <div class="alert alert-light border d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <strong class="d-block"><i class="ti ti-device-tv me-1"></i> Pantallas TV (TVPik)</strong>
                    <span class="small text-muted">
                        @if($tvpikLinksCount > 0)
                            {{ $tvpikLinksCount }} pantalla(s) vinculada(s). Al guardar platos se republican automáticamente.
                        @else
                            Publica esta carta en TV desde el hub TVPik.
                        @endif
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.tvpik.index') }}" class="btn btn-sm btn-outline-primary">Gestionar TVs</a>
                    @if($tvpikLinksCount > 0)
                        <form method="POST" action="{{ route('admin.tvpik.publish-all') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary">Republicar TVs</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    @endif

    <div class="alert alert-light border d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-start gap-2">
            <i class="ri-palette-line ri-lg text-primary mt-1" aria-hidden="true"></i>
            <div>
                <strong class="d-block mb-0">Aspecto de tu carta</strong>
                <span class="small text-muted">
                    Plantilla actual:
                    <strong>{{ config('company_templates.templates.' . ($company->template ?: 'basic') . '.label', 'Básica') }}</strong>.
                    Puedes cambiar colores y diseño en el negocio.
                </span>
            </div>
        </div>
        <a href="{{ route('admin.companies.edit', ['company' => $company, 'step' => 'design']) }}" class="btn btn-sm btn-outline-primary flex-shrink-0">
            Personalizar colores y plantilla
        </a>
    </div>

    <div class="card mb-4 border shadow-none webnu-menu-type-card">
        <div class="card-body py-3">
            <p class="small text-muted mb-3 mb-md-3">Elige cómo se verá tu carta pública para los clientes.</p>
            <div class="webnu-menu-type" role="radiogroup" aria-label="Tipo de carta">
                <label class="webnu-menu-type__option {{ $company->menu_type == 1 ? 'is-active' : '' }}" for="menu-type-custom">
                    <input type="radio" class="d-none" id="menu-type-custom" name="menu_type" value="menu_type_custom" {{ $company->menu_type == 1 ? 'checked' : '' }}>
                    <span class="webnu-menu-type__icon" aria-hidden="true">
                        <i class="ri-layout-grid-line"></i>
                    </span>
                    <span class="webnu-menu-type__copy">
                        <span class="webnu-menu-type__title">Carta digital</span>
                        <span class="webnu-menu-type__desc">Secciones, platos y diseño personalizable</span>
                    </span>
                </label>
                <label class="webnu-menu-type__option {{ $company->menu_type == 2 ? 'is-active' : '' }}" for="menu-type-pdf">
                    <input type="radio" class="d-none" id="menu-type-pdf" name="menu_type" value="menu_type_pdf" {{ $company->menu_type == 2 ? 'checked' : '' }}>
                    <span class="webnu-menu-type__icon" aria-hidden="true">
                        <i class="ri-file-pdf-line"></i>
                    </span>
                    <span class="webnu-menu-type__copy">
                        <span class="webnu-menu-type__title">Carta PDF</span>
                        <span class="webnu-menu-type__desc">Sube un documento y muéstralo tal cual</span>
                    </span>
                </label>
            </div>
        </div>
    </div>

    @php
        $pdfMenuUrl = $company->menu_type_2_pdf
            ? url('img/' . ltrim($company->menu_type_2_pdf, '/'))
            : null;
    @endphp

    <div id="pdf-menu" class="card mb-4 border shadow-none" data-pdf-url="{{ $pdfMenuUrl ?? '' }}" style="{{ $company->menu_type == 1 ? 'display:none' : '' }}">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h5 class="card-title mb-1">Carta en PDF</h5>
                <p class="text-muted small mb-0">Sube un documento para mostrarlo como carta pública.</p>
            </div>
            @if($pdfMenuUrl)
                <a href="{{ route('see_menu', $company->slug) }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                    <i class="ri ri-external-link-line me-1"></i> Ver como cliente
                </a>
            @endif
        </div>
        <div class="card-body">
            <div class="webnu-pdf-preview mb-3" id="pdf-menu-preview">
                @if($pdfMenuUrl)
                    <iframe id="pdf-preview-frame" src="{{ $pdfMenuUrl }}#toolbar=1&navpanes=0" title="Vista previa PDF"></iframe>
                @else
                    <div class="webnu-pdf-preview__empty text-center py-5" id="pdf-preview-empty">
                        <i class="ri ri-file-pdf-2-line icon-48px text-muted mb-2 d-block"></i>
                        <p class="text-muted mb-0">Aún no hay PDF. Sube uno para ver la vista previa.</p>
                    </div>
                @endif
            </div>
            <p class="small text-muted mb-0" id="pdf-preview-filename" @if(!$pdfMenuUrl) style="display:none" @endif>
                @if($pdfMenuUrl)
                    <i class="ri ri-checkbox-circle-line text-success"></i>
                    PDF cargado � <a href="{{ $pdfMenuUrl }}" target="_blank" rel="noopener">Abrir archivo</a>
                @endif
            </p>
        </div>
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.sections.updatepdfmenu') }}">
            @csrf
            @method('PUT')
            <div class="card-body border-top bg-lighter">
                @if($company->menu_type_2_pdf)
                    <div class="alert alert-warning py-2 small mb-3 mb-0">Al guardar, el nuevo archivo sustituirá al PDF actual.</div>
                @endif
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label" for="pdf-menu-file">Archivo PDF</label>
                        <input type="file" accept="application/pdf" name="pdf_menu_file" id="pdf-menu-file" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <input type="hidden" name="company_id" value="{{ $company->id }}">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri ri-upload-2-line me-1"></i> Guardar PDF
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div id="custom-menu" style="{{ $company->menu_type == 2 ? 'display:none' : '' }}">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <p class="text-muted small mb-0">Organiza secciónes y platos de tu carta digital.</p>
            <div class="btn-group webnu-menu-view-toggle" role="group" aria-label="Vista de platos">
                <button type="button" class="btn btn-sm btn-primary" data-menu-view="grid" title="Vista cuadrícula">
                    <i class="ri ri-layout-grid-line"></i>
                </button>
                <button type="button" class="btn btn-sm btn-label-secondary" data-menu-view="list" title="Vista lista">
                    <i class="ri ri-list-check"></i>
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
                    <p class="text-muted mb-4">Crea la primera sección (Entrantes, Principales…) y añade platos.</p>
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
