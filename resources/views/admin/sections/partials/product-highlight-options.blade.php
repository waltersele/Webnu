@php
    $isAdd = ($mode ?? 'add') === 'add';
    $prefix = $isAdd ? 'product_add' : 'product_modify';
    $fieldName = $prefix . '_highlight';
    $current = old($fieldName, isset($product) ? ($product->highlight ?? '') : '');
    $options = config('product_highlights.options', []);
@endphp

<div class="webnu-product-highlight-options">
    <p class="text-muted small mb-3 mb-md-2">Muestra una etiqueta en la carta. Solo puedes elegir una.</p>
    <div class="d-flex flex-column gap-2">
        <div class="form-check">
            <input type="radio"
                   class="form-check-input"
                   name="{{ $fieldName }}"
                   id="{{ $prefix }}-highlight-none"
                   value=""
                   {{ $current === '' || $current === null ? 'checked' : '' }}>
            <label class="form-check-label" for="{{ $prefix }}-highlight-none">Sin etiqueta</label>
        </div>
        @foreach ($options as $key => $option)
            <div class="form-check">
                <input type="radio"
                       class="form-check-input"
                       name="{{ $fieldName }}"
                       id="{{ $prefix }}-highlight-{{ $key }}"
                       value="{{ $key }}"
                       {{ (string) $current === (string) $key ? 'checked' : '' }}>
                <label class="form-check-label d-inline-flex align-items-center gap-2" for="{{ $prefix }}-highlight-{{ $key }}">
                    @include('admin.sections.partials.product-highlight-badge', ['highlight' => $key, 'size' => 'sm'])
                    {{ $option['label'] }}
                </label>
            </div>
        @endforeach
    </div>
</div>
