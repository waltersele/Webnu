@php
    $currentTemplate = old('template', $company->template ?: 'lumiere');
    $templatePresets = $themePresets[$currentTemplate] ?? [];
    $allTemplates = collect($templates)->sortBy(function ($meta, $key) {
        return [!empty($meta['recommended']) ? 0 : 1, $meta['label'] ?? $key];
    });
@endphp

<div class="wn-studio-step d-none" data-step="design">
    <div class="mb-3">
        <h5 class="fw-semibold mb-1">Diseño de la carta</h5>
        <p class="text-muted small mb-0">Ajusta colores y tipografías arriba; elige una plantilla abajo. La vista previa de la derecha se actualiza al instante.</p>
    </div>

    <input type="hidden" name="template" id="company-template" value="{{ $currentTemplate }}">

    {{-- Colores y fuentes (primero) --}}
    <div class="card border-0 shadow-sm mb-4 wn-studio-design-panel">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h6 class="fw-semibold mb-0"><i class="ri-palette-line me-1 text-primary"></i> Colores y tipografía</h6>
                    <p class="text-muted small mb-0">Se aplican en la vista previa al momento.</p>
                </div>
            </div>

            <p class="small fw-semibold text-body-secondary mb-2">Paletas rápidas</p>
            <div class="wn-preset-chips mb-3" id="wn-preset-chips">
                @foreach($templatePresets as $presetName => $presetColors)
                    <button type="button"
                        class="btn btn-sm btn-outline-secondary wn-preset-btn {{ $loop->first ? 'active' : '' }}"
                        data-colors='@json($presetColors)'>
                        @foreach(array_slice($presetColors, 0, 3) as $hex)
                            <span class="wn-preset-btn__dot" style="background: {{ $hex }}"></span>
                        @endforeach
                        {{ $presetName }}
                    </button>
                @endforeach
            </div>
            <p class="text-muted small mb-3 {{ count($templatePresets) ? 'd-none' : '' }}" id="wn-preset-empty">Selecciona una plantilla para ver paletas sugeridas.</p>

            <p class="small fw-semibold text-body-secondary mb-2">Colores personalizados</p>
            <div class="row g-2 mb-3 wn-color-grid">
                @foreach($colorKeys as $colorKey => $colorLabel)
                    @php $colorValue = old('theme_' . $colorKey, $themeSettings[$colorKey] ?? '#0074d9'); @endphp
                    <div class="col-6 col-md-4">
                        <label class="wn-color-swatch" for="theme_{{ $colorKey }}_picker" title="{{ $colorLabel }}">
                            <input type="color" class="wn-color-swatch__input" id="theme_{{ $colorKey }}_picker" value="{{ $colorValue }}">
                            <span class="wn-color-swatch__chip" style="background: {{ $colorValue }}"></span>
                            <span class="wn-color-swatch__label">{{ $colorLabel }}</span>
                            <input type="text" name="theme_{{ $colorKey }}" id="theme_{{ $colorKey }}" class="theme-hex-input d-none" value="{{ $colorValue }}" maxlength="7">
                        </label>
                    </div>
                @endforeach
            </div>

            <p class="small fw-semibold text-body-secondary mb-2">Fuentes</p>
            <div class="row g-2">
                @foreach($fontKeys as $fontKey => $fontLabel)
                    @php $fontValue = old('theme_' . $fontKey, $themeSettings[$fontKey] ?? config('company_templates.font_defaults.' . $fontKey, 'inter')); @endphp
                    <div class="col-md-6">
                        <label class="form-label small mb-1" for="theme_{{ $fontKey }}">{{ $fontLabel }}</label>
                        <select name="theme_{{ $fontKey }}" id="theme_{{ $fontKey }}" class="form-select form-select-sm theme-font-select" data-font-role="{{ $fontKey }}">
                            @foreach($fonts as $fontId => $fontMeta)
                                @php
                                    $fontFamily = ($fontMeta['family'] ?? 'Inter') . ', ' . ($fontMeta['category'] ?? 'sans-serif');
                                @endphp
                                <option value="{{ $fontId }}"
                                    {{ $fontValue === $fontId ? 'selected' : '' }}
                                    style="font-family: {{ $fontFamily }};"
                                    data-font-family="{{ $fontFamily }}">
                                    {{ $fontMeta['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Plantillas (todas juntas) --}}
    <p class="small fw-semibold text-body-secondary mb-2">Plantillas ({{ $allTemplates->count() }})</p>
    <div class="row g-3 mb-3 wn-template-gallery">
        @foreach($allTemplates as $key => $meta)
            @php
                $tplLocked = isset($templateAccess)
                    && ! ($templateAccess['can_use_all'] ?? true)
                    && ! in_array($key, $templateAccess['allowed_keys'] ?? [], true)
                    && $key !== $currentTemplate;
            @endphp
            @include('admin.companies.partials.studio-template-card', [
                'key' => $key,
                'meta' => $meta,
                'selected' => $currentTemplate === $key,
                'locked' => $tplLocked,
                'currentTemplate' => $currentTemplate,
            ])
        @endforeach
    </div>

    <p class="text-muted small mb-0">Pulsa <strong>Guardar</strong> al terminar para publicar los cambios en la carta.</p>
</div>

