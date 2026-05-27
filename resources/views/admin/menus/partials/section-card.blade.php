@php
    $sid = isset($section) && $section ? $section->id : ($clientId ?? '__SID__');
    $sectionName = isset($section) && $section ? $section->name : '';
    $sectionPosition = isset($section) && $section ? $section->position : 0;
    $items = $items ?? collect();
@endphp
<article class="wn-section-card" data-section-card data-section-id="{{ $sid }}">
    <header class="wn-section-card__head">
        <i class="ti ti-grip-vertical wn-section-card__handle" aria-hidden="true"></i>
        <input type="text"
               class="form-control form-control-sm wn-section-card__name"
               name="sections[{{ $sid }}][name]"
               value="{{ $sectionName }}"
               maxlength="80"
               placeholder="Ej. Primer plato, Postre, Aperitivos, Tabla de quesos..."
               required>
        <input type="hidden" name="sections[{{ $sid }}][position]" value="{{ $sectionPosition }}" data-section-position>
        <button type="button"
                class="btn btn-sm btn-outline-danger wn-section-card__remove"
                data-remove-section
                title="Eliminar sección"
                aria-label="Eliminar sección">
            <i class="ri ri-delete-bin-line"></i>
        </button>
    </header>

    <ul class="wn-section-card__list" data-section-items>
        @foreach($items as $item)
            @include('admin.menus.partials.item-row', ['item' => $item, 'sectionClientId' => $sid])
        @endforeach
    </ul>

    <footer class="wn-section-card__foot">
        <button type="button"
                class="btn btn-sm btn-outline-primary"
                data-add-product
                data-section-id="{{ $sid }}">
            <i class="ri ri-add-line"></i> Plato de la carta
        </button>
        <button type="button"
                class="btn btn-sm btn-outline-secondary"
                data-add-label
                data-section-id="{{ $sid }}">
            <i class="ri ri-text"></i> Texto libre
        </button>
    </footer>
</article>
