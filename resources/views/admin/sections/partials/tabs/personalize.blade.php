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

    $pdfMenuUrl = $company->menu_type_2_pdf
        ? url('img/' . ltrim($company->menu_type_2_pdf, '/'))
        : null;
    $isPdfMenu = (int) $company->menu_type === 2;
    $hasTranslation = $pf['translation'] ?? true;
    $languagesUrl = route('admin.companies.languages', $company);
@endphp

<div class="wn-personalize-panel">

    <div class="wn-personalize-card">
        <h6 class="mb-1"><i class="ri ri-restaurant-line text-primary me-1"></i> Tipo de carta pública</h6>
        <p class="text-muted small mb-3">Elige cómo verán tu carta los clientes.</p>
        <div class="webnu-menu-type" role="radiogroup" aria-label="Tipo de carta">
            <label class="webnu-menu-type__option {{ $company->menu_type == 1 ? 'is-active' : '' }}" for="menu-type-custom">
                <input type="radio" class="d-none" id="menu-type-custom" name="menu_type" value="menu_type_custom" {{ $company->menu_type == 1 ? 'checked' : '' }}>
                <span class="webnu-menu-type__icon" aria-hidden="true"><i class="ri-layout-grid-line"></i></span>
                <span class="webnu-menu-type__copy">
                    <span class="webnu-menu-type__title">Carta digital</span>
                    <span class="webnu-menu-type__desc">Editas platos y secciones aquí.</span>
                </span>
            </label>
            <label class="webnu-menu-type__option {{ $company->menu_type == 2 ? 'is-active' : '' }}" for="menu-type-pdf">
                <input type="radio" class="d-none" id="menu-type-pdf" name="menu_type" value="menu_type_pdf" {{ $company->menu_type == 2 ? 'checked' : '' }}>
                <span class="webnu-menu-type__icon" aria-hidden="true"><i class="ri-file-pdf-line"></i></span>
                <span class="webnu-menu-type__copy">
                    <span class="webnu-menu-type__title">Carta PDF</span>
                    <span class="webnu-menu-type__desc">Muestra un PDF como carta.</span>
                </span>
            </label>
        </div>

        <div id="pdf-menu" data-pdf-url="{{ $pdfMenuUrl ?? '' }}" class="mt-4" style="{{ $isPdfMenu ? '' : 'display:none' }}">
            <strong class="d-block small mb-2">Subir PDF de la carta</strong>
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

    @if((int) $company->menu_type === 1)
        <div class="wn-personalize-card">
            <h6 class="mb-1"><i class="ri ri-star-line text-primary me-1"></i> Destacados del día</h6>
            <p class="text-muted small mb-3">Sube hasta 3 platos a la portada de tu carta.</p>
            @include('admin.sections.partials.daily-highlights-form', ['company' => $company])
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="wn-personalize-card h-100 d-flex flex-column">
                <h6 class="mb-1"><i class="ri ri-palette-line text-primary me-1"></i> Plantilla de diseño</h6>
                <p class="text-muted small mb-3">Personaliza colores, tipografías y estilo visual.</p>
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-auto">
                    <div>
                        <strong class="d-block">{{ $templateLabel }}</strong>
                        @if($templateLocked)
                            <span class="small">@include('admin.partials.plan-pro-badge', ['label' => 'Pro', 'size' => 'xs'])</span>
                        @else
                            <span class="text-muted small">Plantilla actual</span>
                        @endif
                    </div>
                    <a href="{{ route('admin.companies.edit', ['company' => $company, 'step' => 'design']) }}" class="btn btn-outline-primary btn-sm">
                        <i class="ri ri-paint-brush-line me-1"></i> Personalizar
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="wn-personalize-card h-100 d-flex flex-column">
                <h6 class="mb-1"><i class="ti ti-device-tv text-primary me-1"></i> Pantallas TV (TVPik)</h6>
                <p class="text-muted small mb-3">Publica tu carta en pantallas dentro del local.</p>
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-auto">
                    <div>
                        @if($canTvpik && $tvpikLinksCount > 0)
                            <strong class="d-block">{{ $tvpikLinksCount }} pantalla(s)</strong>
                            <span class="text-muted small">vinculadas a esta carta</span>
                        @else
                            <strong class="d-block">Sin pantallas vinculadas</strong>
                            <span class="text-muted small">Publica esta carta en TV</span>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        @component('admin.partials.plan-gated-action', [
                            'feature' => 'tvpik',
                            'enabled' => $canTvpik,
                            'planLabel' => 'Plus',
                            'element' => 'a',
                            'href' => route('admin.tvpik.index'),
                            'class' => 'btn btn-outline-primary btn-sm',
                        ])
                            Gestionar TVs
                        @endcomponent
                        @if($canTvpik && $tvpikLinksCount > 0)
                            <form method="POST" action="{{ route('admin.tvpik.publish-all') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Republicar</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="wn-personalize-card h-100 d-flex flex-column">
                <h6 class="mb-1"><i class="ri ri-translate-2 text-primary me-1"></i> Idiomas</h6>
                <p class="text-muted small mb-3">Ofrece tu carta traducida automáticamente.</p>
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-auto">
                    <div>
                        @if($hasTranslation)
                            <strong class="d-block">Disponible</strong>
                            <span class="text-muted small">Edita los idiomas en los que se muestra tu carta.</span>
                        @else
                            <strong class="d-block">Bloqueado</strong>
                            <span class="text-muted small">Disponible en el plan Plus.</span>
                        @endif
                    </div>
                    @component('admin.partials.plan-gated-action', [
                        'feature' => 'translation',
                        'enabled' => $hasTranslation,
                        'planLabel' => 'Plus',
                        'element' => 'a',
                        'href' => $languagesUrl,
                        'class' => 'btn btn-outline-primary btn-sm',
                        'fallbackHref' => $languagesUrl,
                    ])
                        <i class="ri ri-translate-2 me-1"></i> Gestionar idiomas
                        @unless($hasTranslation)<span class="badge bg-label-warning ms-2">Plus</span>@endunless
                    @endcomponent
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="wn-personalize-card h-100 d-flex flex-column">
                <h6 class="mb-1"><i class="ri ri-information-line text-primary me-1"></i> Datos del negocio</h6>
                <p class="text-muted small mb-3">Nombre, horario, dirección y datos visibles en la carta.</p>
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-auto">
                    <div>
                        <strong class="d-block">{{ $company->name }}</strong>
                        <span class="text-muted small">Edita la ficha de tu restaurante.</span>
                    </div>
                    <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-outline-primary btn-sm">
                        <i class="ri ri-edit-line me-1"></i> Editar negocio
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
