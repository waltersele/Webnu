@php
    $items = old('highlights', $company->resolvedDailyHighlights());
    if (! is_array($items)) {
        $items = [];
    }
    if (count($items) === 0) {
        $items = [['type' => 'spotlight', 'label' => '', 'text' => '', 'price' => '']];
    }
    $hasContent = $company->hasDailySpotlight();
    $collapseOpen = $hasContent ? 'show' : '';
@endphp
<div class="accordion mb-4 wn-daily-highlights-accordion" id="wn-daily-highlights-accordion">
    <div class="accordion-item border shadow-none">
        <h2 class="accordion-header">
            <button class="accordion-button {{ $hasContent ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#wn-daily-highlights-body" aria-expanded="{{ $hasContent ? 'true' : 'false' }}">
                <i class="ri ri-sun-line me-2 text-warning"></i>
                <span class="fw-semibold">Destacados del día</span>
                @if ($hasContent)
                    <span class="badge text-bg-success ms-2">{{ count($company->resolvedDailyHighlights()) }} en carta</span>
                @endif
            </button>
        </h2>
        <div id="wn-daily-highlights-body" class="accordion-collapse collapse {{ $collapseOpen }}" data-bs-parent="#wn-daily-highlights-accordion">
            <div class="accordion-body pt-2">
                <p class="text-muted small mb-3">Especial suelto o menú del día en texto; no hace falta crear platos. Máximo 3 bloques.</p>
                <form method="POST" action="{{ route('admin.companies.daily-highlights', $company) }}" id="wn-daily-highlights-form">
                    @csrf
                    @method('PUT')
                    <div id="wn-daily-highlights-rows" class="d-flex flex-column gap-3">
                        @foreach ($items as $index => $item)
                            @php
                                $type = old('highlights.' . $index . '.type', $item['type'] ?? 'spotlight');
                                $isMenu = $type === 'menu_del_dia';
                            @endphp
                            <div class="wn-daily-highlight-row card border bg-light-subtle">
                                <div class="card-body py-3">
                                    <div class="row g-2 align-items-start">
                                        <div class="col-md-3">
                                            <label class="form-label small mb-1">Tipo</label>
                                            <select name="highlights[{{ $index }}][type]" class="form-select form-select-sm wn-highlight-type">
                                                <option value="spotlight" {{ $type === 'spotlight' ? 'selected' : '' }}>Especial</option>
                                                <option value="menu_del_dia" {{ $type === 'menu_del_dia' ? 'selected' : '' }}>Menú del día</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small mb-1">Etiqueta (opcional)</label>
                                            <input type="text" name="highlights[{{ $index }}][label]" class="form-control form-control-sm" maxlength="80" placeholder="{{ $isMenu ? 'Menú del día' : 'Especial de hoy' }}" value="{{ old('highlights.' . $index . '.label', $item['label'] ?? '') }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small mb-1">Precio €</label>
                                            <input type="text" name="highlights[{{ $index }}][price]" class="form-control form-control-sm" inputmode="decimal" placeholder="Opcional" value="{{ old('highlights.' . $index . '.price', $item['price'] ?? '') }}">
                                        </div>
                                        <div class="col-md-4 d-flex justify-content-end align-items-end">
                                            <button type="button" class="btn btn-sm btn-outline-secondary wn-remove-highlight" title="Quitar fila">&times;</button>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small mb-1 wn-highlight-text-label">{{ $isMenu ? 'Platos (una línea cada uno)' : 'Texto' }}</label>
                                            @if ($isMenu)
                                                <textarea name="highlights[{{ $index }}][text]" class="form-control wn-highlight-text" rows="3" maxlength="2000" placeholder="Ensalada&#10;Secreto ibérico&#10;Tarta del chef">{{ old('highlights.' . $index . '.text', $item['text'] ?? '') }}</textarea>
                                            @else
                                                <input type="text" name="highlights[{{ $index }}][text]" class="form-control wn-highlight-text" maxlength="500" placeholder="Ej: Lubina del mercado a la plancha" value="{{ old('highlights.' . $index . '.text', $item['text'] ?? '') }}">
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('highlights')
                        <p class="text-danger small mt-2 mb-0">{{ $message }}</p>
                    @enderror
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="wn-add-highlight" {{ count($items) >= 3 ? 'disabled' : '' }}>
                            <i class="ri ri-add-line"></i> Añadir destacado
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm">Guardar destacados</button>
                        @if ($hasContent)
                            <button type="submit" name="clear" value="1" class="btn btn-outline-secondary btn-sm">Quitar todos</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<template id="wn-daily-highlight-row-template">
    <div class="wn-daily-highlight-row card border bg-light-subtle">
        <div class="card-body py-3">
            <div class="row g-2 align-items-start">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Tipo</label>
                    <select name="highlights[__INDEX__][type]" class="form-select form-select-sm wn-highlight-type">
                        <option value="spotlight">Especial</option>
                        <option value="menu_del_dia">Menú del día</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Etiqueta (opcional)</label>
                    <input type="text" name="highlights[__INDEX__][label]" class="form-control form-control-sm" maxlength="80">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Precio €</label>
                    <input type="text" name="highlights[__INDEX__][price]" class="form-control form-control-sm" inputmode="decimal" placeholder="Opcional">
                </div>
                <div class="col-md-4 d-flex justify-content-end align-items-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary wn-remove-highlight" title="Quitar fila">&times;</button>
                </div>
                <div class="col-12 wn-highlight-text-wrap">
                    <label class="form-label small mb-1 wn-highlight-text-label">Texto</label>
                    <input type="text" name="highlights[__INDEX__][text]" class="form-control wn-highlight-text" maxlength="500">
                </div>
            </div>
        </div>
    </div>
</template>
