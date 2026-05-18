@php
    $isAdd = ($mode ?? 'add') === 'add';
    $prefix = $isAdd ? 'product_add' : 'product_modify';
    $id = $isAdd ? 'product-add' : 'product-modify';
    $portionEnableId = $isAdd ? 'product-add-enable-price-portion-switch' : 'product-modify-enable-price-portion-switch';
    $portionEnableName = $isAdd ? 'product_add_enable_price_portion' : 'product_modify_enable_price_portion';
    $portionPriceId = $isAdd ? 'product-add-price-portion' : 'product-modify-price-portion';
    $portionPriceName = $isAdd ? 'product_add_price_portion' : 'product_modify_price_portion';
    $enabledName = $enabledName ?? ($isAdd ? 'product_add_enabled' : 'product_modify_enabled');
    $enabledId = $enabledId ?? ($isAdd ? 'product-add-enabled-switch' : 'product-modify-enabled');
    $isAddMode = $isAddMode ?? $isAdd;
@endphp

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Datos del plato</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label" for="{{ $id }}-name">Nombre <span class="text-danger">*</span></label>
                <input type="text"
                       name="{{ $prefix }}_name"
                       id="{{ $id }}-name"
                       class="form-control"
                       placeholder="Ej. Ensalada César"
                       value=""
                       {{ $isAdd ? 'autofocus' : '' }}
                       required>
            </div>
            <div class="col-12">
                <label class="form-label" for="{{ $id }}-description">Descripción</label>
                <textarea name="{{ $prefix }}_description"
                          id="{{ $id }}-description"
                          class="form-control"
                          rows="2"
                          placeholder="Ingredientes, notas del plato"></textarea>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Estado</h5>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <div>
                <span class="fw-medium d-block">Visible en la carta</span>
                <small class="text-muted">Si está desactivado, el plato no se muestra al público.</small>
            </div>
            <div class="form-check form-switch mb-0 flex-shrink-0">
                <input type="checkbox"
                       class="form-check-input"
                       name="{{ $enabledName }}"
                       id="{{ $enabledId }}"
                       value="1"
                       {{ $isAddMode ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $enabledId }}">Visible</label>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Precios</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-sm-6">
                <label class="form-label" for="{{ $id }}-price-unit">Precio <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text"
                           name="{{ $prefix }}_price_unit"
                           id="{{ $id }}-price-unit"
                           class="form-control"
                           placeholder="0,00"
                           required>
                    <span class="input-group-text">€</span>
                </div>
            </div>
            <div class="col-sm-6" id="{{ $id }}-portion-wrap">
                <label class="form-label" for="{{ $portionPriceId }}">Media ración</label>
                <div class="input-group {{ $isAdd ? 'hidden' : '' }}" id="{{ $id }}-portion-price-group">
                    <input type="text"
                           name="{{ $portionPriceName }}"
                           id="{{ $portionPriceId }}"
                           class="form-control"
                           placeholder="0,00">
                    <span class="input-group-text">€</span>
                </div>
                <div class="form-check mt-2">
                    <input type="checkbox"
                           class="form-check-input"
                           name="{{ $portionEnableName }}"
                           value="1"
                           id="{{ $portionEnableId }}">
                    <label class="form-check-label" for="{{ $portionEnableId }}">Ofrecer media ración</label>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.sections.partials.product-media', ['mode' => $mode ?? 'add'])

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Opciones</h5>
    </div>
    <div class="card-body">
        @include('admin.sections.partials.product-sale-options', [
            'mode' => $mode ?? 'add',
            'product' => $product ?? null,
        ])
        <hr class="my-4">
        <p class="fw-medium mb-2">Etiqueta en carta</p>
        @include('admin.sections.partials.product-highlight-options', [
            'mode' => $mode ?? 'add',
            'product' => $product ?? null,
        ])
    </div>
</div>

<div class="card mb-0">
    <div class="card-header">
        <h5 class="card-title mb-0">Alérgenos</h5>
    </div>
    <div class="card-body">
        @include('admin.sections.partials.product-allergens-picker', ['mode' => $mode ?? 'add', 'allergens' => $allergens])
    </div>
</div>

@if($isAdd)
    <input type="hidden" name="product_add_section_id" id="product-add-section-id">
@else
    <input type="hidden" name="product_id" id="product-modify-id">
    <input type="hidden" name="product_modify_section_id" id="product-modify-section-id">
@endif
