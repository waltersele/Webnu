@php
    $isAdd = ($mode ?? 'add') === 'add';
    $prefix = $isAdd ? 'product_add' : 'product_modify';
    $individualId = $isAdd ? 'product-add-sold-by-piece-switch' : 'product-modify-individual-sale';
    $individualName = $prefix . '_individual_sale';
    $weightId = $isAdd ? 'product-add-weight-sale-switch' : 'product-modify-weight-sale';
    $weightName = $prefix . '_weight_sale';
    $weightLabelName = $prefix . '_weight_unit_label';
    $weightLabelId = $isAdd ? 'product-add-weight-unit-label' : 'product-modify-weight-unit-label';
    $weightWrapId = $isAdd ? 'product-add-weight-label-wrap' : 'product-modify-weight-label-wrap';
    $weightChecked = isset($product) ? (bool) $product->weight_sale : false;
    $weightLabelValue = old($weightLabelName, isset($product) ? ($product->weight_unit_label ?? '') : '');
    $individualChecked = isset($product) ? (bool) $product->individual_sale : false;
@endphp

<div class="webnu-sale-options">
    <div class="form-check mb-2">
        <input type="checkbox"
               class="form-check-input product-sale-type"
               name="{{ $individualName }}"
               value="1"
               id="{{ $individualId }}"
               data-sale-type="unit"
               {{ $individualChecked ? 'checked' : '' }}>
        <label class="form-check-label" for="{{ $individualId }}">Se vende por unidades</label>
    </div>
    <div class="webnu-sale-option-weight">
        <div class="form-check mb-2">
            <input type="checkbox"
                   class="form-check-input product-sale-type"
                   name="{{ $weightName }}"
                   value="1"
                   id="{{ $weightId }}"
                   data-sale-type="weight"
                   {{ $weightChecked ? 'checked' : '' }}>
            <label class="form-check-label" for="{{ $weightId }}">Se vende por peso</label>
        </div>
        <div id="{{ $weightWrapId }}"
             class="webnu-weight-label-wrap ps-4 pb-1 {{ $weightChecked ? '' : 'hidden' }}">
            <label class="form-label small mb-1" for="{{ $weightLabelId }}">Referencia del precio</label>
            <input type="text"
                   name="{{ $weightLabelName }}"
                   id="{{ $weightLabelId }}"
                   class="form-control form-control-sm"
                   placeholder="Ej. 100 gr"
                   value="{{ $weightLabelValue }}"
                   maxlength="64">
            <small class="text-muted">Se muestra junto al precio en la carta (ej. «12,50 € / 100 gr»).</small>
        </div>
    </div>
</div>
