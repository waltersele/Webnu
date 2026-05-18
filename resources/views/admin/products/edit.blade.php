@extends('admin.layout')

@section('page_title', 'Editar plato')
@section('page_subtitle')
    {{ $product->section->name ?? 'Carta' }} &bull; #{{ $product->id }}
@endsection

@section('page_actions')
    <a href="{{ route('admin.sections.index') }}#{{ $product->section_id }}" class="btn btn-label-secondary">
        Cancelar
    </a>
    <button type="submit" form="product-edit-form" class="btn btn-primary">
        <i class="ri ri-save-line me-1"></i> Guardar cambios
    </button>
@endsection

@section('content')
<form id="product-edit-form"
      class="webnu-product-edit"
      method="POST"
      action="{{ route('admin.products.update') }}"
      enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <input type="hidden" name="product_id" id="product-modify-id" value="{{ $product->id }}">
    <input type="hidden" name="product_modify_section_id" id="product-modify-section-id" value="{{ $product->section_id }}">

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Datos del plato</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="product-modify-name">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="product_modify_name" id="product-modify-name" class="form-control"
                               value="{{ old('product_modify_name', $product->name) }}" required>
                    </div>
                    <div>
                        <label class="form-label" for="product-modify-description">Descripción detallada</label>
                        <textarea name="product_modify_description" id="product-modify-description" class="form-control" rows="4">{{ old('product_modify_description', $product->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Precios y porciones</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="product-modify-price-unit">Precio ración completa</label>
                            <div class="input-group">
                                <span class="input-group-text">&euro;</span>
                                <input type="text" name="product_modify_price_unit" id="product-modify-price-unit" class="form-control"
                                       value="{{ old('product_modify_price_unit', $product->price_unit) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="product-modify-price-portion">Precio media ración</label>
                            <div class="input-group" id="product-modify-portion-price-group">
                                <span class="input-group-text">&euro;</span>
                                <input type="text" name="product_modify_price_portion" id="product-modify-price-portion" class="form-control"
                                       value="{{ old('product_modify_price_portion', $product->price_portion) }}">
                            </div>
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" name="product_modify_enable_price_portion" value="1"
                                       id="product-modify-enable-price-portion-switch"
                                       {{ $product->price_portion ? 'checked' : '' }}>
                                <label class="form-check-label" for="product-modify-enable-price-portion-switch">Ofrecer media ración</label>
                            </div>
                        </div>
                    </div>
                    <hr class="my-3">
                    <p class="fw-medium mb-2">Tipo de venta</p>
                    @include('admin.sections.partials.product-sale-options', ['mode' => 'modify', 'product' => $product])
                    <hr class="my-4">
                    <p class="fw-medium mb-2">Etiqueta en carta</p>
                    @include('admin.sections.partials.product-highlight-options', ['mode' => 'modify', 'product' => $product])
                </div>
            </div>

            <div class="card mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">Alérgenos</h5>
                </div>
                <div class="card-body">
                    @include('admin.sections.partials.product-allergens-picker', [
                        'mode' => 'modify',
                        'allergens' => $allergens,
                        'selectedAllergenIds' => $product->allergens->pluck('id')->all(),
                    ])
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            @include('admin.sections.partials.product-media', ['mode' => 'modify'])

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Disponibilidad</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-3 p-3 rounded border bg-lighter">
                        <div>
                            <span class="fw-medium d-block">Disponible en carta</span>
                            <small class="text-muted">Controla si el plato es visible al público.</small>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input type="checkbox" class="form-check-input" name="product_modify_enabled" id="product-modify-enabled" value="1"
                                   {{ $product->enabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="product-modify-enabled">ON</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="{{ asset('adminlte/js/product-media.js') }}"></script>
<script>
$(function () {
    var allergenIds = @json($product->allergens->pluck('id'));
    var $picker = $('#product-modify-allergens-picker');
    $picker.find('.webnu-allergen-chip__input').each(function () {
        this.checked = allergenIds.indexOf(parseInt(this.value, 10)) !== -1;
    });

    if (window.WebnuProductMediaUI) {
        window.WebnuProductMediaUI.loadModifyImage(@json($product->image ?? ''));
        window.WebnuProductMediaUI.loadModifyVideo(@json($product->video ?? ''));
    }

    $('#delete-image-product-id').attr('product-id', {{ $product->id }});
    $('#delete-video-product-id').attr('product-id', {{ $product->id }});

    var hasPortion = @json((bool) $product->price_portion);
    $('#product-modify-portion-price-group').toggleClass('hidden', !hasPortion && !$('#product-modify-enable-price-portion-switch').is(':checked'));

    $('#product-modify-enable-price-portion-switch').on('change', function () {
        $('#product-modify-portion-price-group').toggleClass('hidden', !this.checked);
    });
});
</script>
@endpush
