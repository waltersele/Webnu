@php
    $pf = $planFeatures ?? [];
    $plans = app(\App\Services\UserPlanService::class);
    $user = auth()->user();
    $canTvpik = $pf['tvpik'] ?? $plans->canUseTvpik($user);
    $hasAllTemplates = $pf['all_templates'] ?? $plans->hasAllTemplates($user);
    $templateKey = $company->template ?: 'basic';
    $templateLabel = config('company_templates.templates.' . $templateKey . '.label', 'Básica');
    $templateLocked = ! $hasAllTemplates && $plans->isTemplateLockedForUser($user, $templateKey, $templateKey);
    $tvpikLinksCount = $canTvpik
        ? \App\TvpikScreenLink::where('company_id', $company->id)->count()
        : 0;
    $hasHighlights = $company->hasDailySpotlight();
    $settingsOpen = request()->boolean('menu_settings');
@endphp
<div class="webnu-menu-editor">
    @if((int) $company->menu_type === 1)
        @include('admin.sections.partials.daily-highlights-form', ['company' => $company])
    @endif

    <div class="accordion mb-4" id="wn-menu-settings-accordion">
        <div class="accordion-item border shadow-none">
            <h2 class="accordion-header">
                <button class="accordion-button {{ $settingsOpen ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#wn-menu-settings-body" aria-expanded="{{ $settingsOpen ? 'true' : 'false' }}">
                    <i class="ri ri-settings-3-line me-2"></i>
                    <span class="fw-semibold">Ajustes de carta</span>
                </button>
            </h2>
            <div id="wn-menu-settings-body" class="accordion-collapse collapse {{ $settingsOpen ? 'show' : '' }}" data-bs-parent="#wn-menu-settings-accordion">
                <div class="accordion-body">
                    <div class="webnu-menu-type mb-4" role="radiogroup" aria-label="Tipo de carta">
                        <p class="small text-muted mb-2">Tipo de carta pública</p>
                        <div class="d-flex flex-wrap gap-2">
                            <label class="webnu-menu-type__option {{ $company->menu_type == 1 ? 'is-active' : '' }}" for="menu-type-custom">
                                <input type="radio" class="d-none" id="menu-type-custom" name="menu_type" value="menu_type_custom" {{ $company->menu_type == 1 ? 'checked' : '' }}>
                                <span class="webnu-menu-type__icon" aria-hidden="true"><i class="ri-layout-grid-line"></i></span>
                                <span class="webnu-menu-type__copy">
                                    <span class="webnu-menu-type__title">Carta digital</span>
                                </span>
                            </label>
                            <label class="webnu-menu-type__option {{ $company->menu_type == 2 ? 'is-active' : '' }}" for="menu-type-pdf">
                                <input type="radio" class="d-none" id="menu-type-pdf" name="menu_type" value="menu_type_pdf" {{ $company->menu_type == 2 ? 'checked' : '' }}>
                                <span class="webnu-menu-type__icon" aria-hidden="true"><i class="ri-file-pdf-line"></i></span>
                                <span class="webnu-menu-type__copy">
                                    <span class="webnu-menu-type__title">Carta PDF</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                        <div>
                            <strong class="d-block small">Plantilla</strong>
                            <span class="text-muted small">{{ $templateLabel }}@if($templateLocked) · @include('admin.partials.plan-pro-badge', ['label' => 'Pro', 'size' => 'xs'])@endif</span>
                        </div>
                        <a href="{{ route('admin.companies.edit', ['company' => $company, 'step' => 'design']) }}" class="btn btn-sm btn-outline-primary">Personalizar</a>
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                        <div>
                            <strong class="d-block small"><i class="ti ti-device-tv me-1"></i> Pantallas TV (TVPik)</strong>
                            <span class="text-muted small">
                                @if($canTvpik && $tvpikLinksCount > 0)
                                    {{ $tvpikLinksCount }} pantalla(s) vinculada(s)
                                @else
                                    Publica esta carta en TV
                                @endif
                            </span>
                        </div>
                        <div class="d-flex gap-2">
                            @component('admin.partials.plan-gated-action', [
                                'feature' => 'tvpik',
                                'enabled' => $canTvpik,
                                'planLabel' => 'Plus',
                                'element' => 'a',
                                'href' => route('admin.tvpik.index'),
                                'class' => 'btn btn-sm btn-outline-primary',
                            ])
                                Gestionar TVs
                            @endcomponent
                            @if($canTvpik && $tvpikLinksCount > 0)
                                <form method="POST" action="{{ route('admin.tvpik.publish-all') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">Republicar</button>
                                </form>
                            @endif
                        </div>
                    </div>

                    @php
                        $pdfMenuUrl = $company->menu_type_2_pdf
                            ? url('img/' . ltrim($company->menu_type_2_pdf, '/'))
                            : null;
                    @endphp
                    <div id="pdf-menu" data-pdf-url="{{ $pdfMenuUrl ?? '' }}" style="{{ $company->menu_type == 1 ? 'display:none' : '' }}">
                        <strong class="d-block small mb-2">Subir PDF</strong>
                        <div class="webnu-pdf-preview mb-3" id="pdf-menu-preview">
                            @if($pdfMenuUrl)
                                <iframe id="pdf-preview-frame" src="{{ $pdfMenuUrl }}#toolbar=1&navpanes=0" title="Vista previa PDF" class="w-100" style="min-height:280px;border:0"></iframe>
                            @else
                                <div class="webnu-pdf-preview__empty text-center py-4 border rounded">
                                    <p class="text-muted small mb-0">Sube un PDF para mostrarlo como carta.</p>
                                </div>
                            @endif
                        </div>
                        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.sections.updatepdfmenu') }}">
                            @csrf
                            @method('PUT')
                            <div class="row g-2 align-items-end">
                                <div class="col-md-8">
                                    <input type="file" accept="application/pdf" name="pdf_menu_file" id="pdf-menu-file" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <input type="hidden" name="company_id" value="{{ $company->id }}">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">Guardar PDF</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="custom-menu" style="{{ $company->menu_type == 2 ? 'display:none' : '' }}">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <p class="text-muted small mb-0">Secciones y platos</p>
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
