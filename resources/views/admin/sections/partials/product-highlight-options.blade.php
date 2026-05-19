@php
    $isAdd = ($mode ?? 'add') === 'add';
    $prefix = $isAdd ? 'product_add' : 'product_modify';
    $fieldName = $prefix . '_highlight';
    $current = old($fieldName, isset($product) ? ($product->highlight ?? '') : '');
    $options = config('product_highlights.options', []);
@endphp

<div class="webnu-highlight-pills" role="radiogroup" aria-label="Etiqueta en carta">
    <p class="text-muted small mb-2">Muestra una etiqueta en la carta. Solo puedes elegir una.</p>
    <div class="webnu-highlight-pills__row">
        <label class="webnu-highlight-pill">
            <input type="radio"
                   class="webnu-highlight-pill__input"
                   name="{{ $fieldName }}"
                   id="{{ $prefix }}-highlight-none"
                   value=""
                   {{ $current === '' || $current === null ? 'checked' : '' }}>
            <span class="webnu-highlight-pill__face webnu-highlight-pill__face--none">
                <i class="ri-close-circle-line"></i>
                <span>Sin etiqueta</span>
            </span>
        </label>
        @foreach ($options as $key => $option)
            <label class="webnu-highlight-pill webnu-highlight-pill--{{ $key }}">
                <input type="radio"
                       class="webnu-highlight-pill__input"
                       name="{{ $fieldName }}"
                       id="{{ $prefix }}-highlight-{{ $key }}"
                       value="{{ $key }}"
                       {{ (string) $current === (string) $key ? 'checked' : '' }}>
                <span class="webnu-highlight-pill__face">
                    @include('admin.sections.partials.product-highlight-badge', ['highlight' => $key, 'size' => 'sm'])
                    <span>{{ $option['label'] }}</span>
                </span>
            </label>
        @endforeach
    </div>
</div>
