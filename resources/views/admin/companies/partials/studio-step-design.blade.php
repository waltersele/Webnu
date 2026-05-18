@php
    $currentTemplate = old('template', $company->template ?: 'lumiere');
    $modernTemplates = collect($templates)->filter(function ($t) {
        return ($t['group'] ?? '') === 'modern';
    });
    $classicTemplates = collect($templates)->filter(function ($t) {
        return ($t['group'] ?? '') === 'classic';
    });
    $templatePresets = $themePresets[$currentTemplate] ?? [];
@endphp

<div class="wn-studio-step d-none" data-step="design">
    <div class="mb-4">
        <h5 class="fw-semibold mb-1">Diseño de la carta</h5>
        <p class="text-muted small mb-0">Elige plantilla y colores. Puedes ver el resultado en el móvil de la derecha.</p>
    </div>

    <input type="hidden" name="template" id="company-template" value="{{ $currentTemplate }}">

    <h6 class="fw-semibold mb-2"><span class="badge bg-label-primary me-1">Recomendadas</span> Plantillas modernas</h6>
    <div class="row g-3 mb-4 wn-template-gallery" data-group="modern">
        @foreach($modernTemplates as $key => $meta)
            @include('admin.companies.partials.studio-template-card', ['key' => $key, 'meta' => $meta, 'selected' => $currentTemplate === $key])
        @endforeach
    </div>

    <div class="accordion mb-4" id="accordion-classic-templates">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-classic">
                    Plantillas clásicas ({{ $classicTemplates->count() }})
                </button>
            </h2>
            <div id="collapse-classic" class="accordion-collapse collapse" data-bs-parent="#accordion-classic-templates">
                <div class="accordion-body pt-3">
                    <div class="row g-3 wn-template-gallery" data-group="classic">
                        @foreach($classicTemplates as $key => $meta)
                            @include('admin.companies.partials.studio-template-card', ['key' => $key, 'meta' => $meta, 'selected' => $currentTemplate === $key])
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border mb-4" id="wn-palette-section">
        <div class="card-body">
            <h6 class="fw-semibold mb-2"><i class="ri-palette-line me-1 text-primary"></i> Paleta de colores</h6>
            <p class="text-muted small">Elige un estilo predefinido o personaliza cada color.</p>

            <div class="wn-preset-chips mb-3" id="wn-preset-chips">
                @foreach($templatePresets as $presetName => $presetColors)
                    <button type="button" class="btn btn-sm btn-outline-secondary wn-preset-btn" data-colors='@json($presetColors)'>
                        {{ $presetName }}
                    </button>
                @endforeach
            </div>
            <p class="text-muted small mb-0 d-none" id="wn-preset-empty">Selecciona una plantilla para ver paletas sugeridas.</p>

            <button class="btn btn-sm btn-link px-0 mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#wn-custom-colors">
                Personalizar colores avanzado
            </button>
            <div class="collapse mt-3" id="wn-custom-colors">
                <div class="row g-3">
                    @foreach($colorKeys as $colorKey => $colorLabel)
                        @php $colorValue = old('theme_' . $colorKey, $themeSettings[$colorKey] ?? '#0074d9'); @endphp
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label small" for="theme_{{ $colorKey }}">{{ $colorLabel }}</label>
                            <div class="input-group wn-color-input">
                                <input type="color" class="form-control form-control-color" id="theme_{{ $colorKey }}_picker" value="{{ $colorValue }}" title="{{ $colorLabel }}">
                                <input type="text" name="theme_{{ $colorKey }}" id="theme_{{ $colorKey }}" class="form-control theme-hex-input font-monospace" value="{{ $colorValue }}" maxlength="7" pattern="#[0-9A-Fa-f]{6}">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-warning d-flex gap-2 mb-0" role="alert">
        <i class="ri-sparkling-line ri-lg flex-shrink-0"></i>
        <div class="small mb-0">Para un aspecto actual, elige <strong>L'Essence</strong> o <strong>Bistro</strong>. La vista previa muestra los cambios al instante; pulsa <strong>Guardar</strong> para publicarlos.</div>
    </div>
</div>
