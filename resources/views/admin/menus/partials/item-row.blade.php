@php
    static $itemRowIdx = 1;
    $idx = $itemRowIdx++;
    $product = $item->product;
    $imageUrl = $item->imageUrl();
    $sectionClientId = $sectionClientId ?? '';
@endphp
<li class="wn-item-row {{ $product ? 'wn-item-row--product' : 'wn-item-row--label' }}" data-item-row>
    <i class="ti ti-grip-vertical wn-item-row__handle" aria-hidden="true"></i>

    <button type="button"
            class="wn-item-row__photo {{ $imageUrl ? 'has-image' : '' }}"
            data-add-photo
            title="{{ $imageUrl ? 'Cambiar foto' : 'Añadir foto' }}"
            aria-label="{{ $imageUrl ? 'Cambiar foto' : 'Añadir foto' }}">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="" data-item-photo>
        @else
            <i class="ri ri-camera-line"></i>
        @endif
    </button>

    <input type="hidden" name="items[{{ $idx }}][section_client_id]" value="{{ $sectionClientId }}">
    <input type="hidden" name="items[{{ $idx }}][position]" value="{{ $item->position ?? $idx }}">
    <input type="hidden" name="items[{{ $idx }}][image]" value="{{ $item->image }}" data-item-image-input>

    @if($product)
        <input type="hidden" name="items[{{ $idx }}][product_id]" value="{{ $product->id }}">
        <span class="wn-item-row__name">{{ $product->name }}</span>
    @else
        <input type="text"
               class="wn-item-row__input"
               name="items[{{ $idx }}][label]"
               value="{{ $item->label }}"
               maxlength="200"
               placeholder="Ej. Fruta de temporada"
               required>
    @endif

    <button type="button" class="wn-item-row__remove" data-remove aria-label="Eliminar">
        <i class="ri ri-close-line"></i>
    </button>
</li>
