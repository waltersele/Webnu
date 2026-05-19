@php
    $isAdd = ($mode ?? 'add') === 'add';
    $modalId = $isAdd ? 'modal-add-product' : 'modal-modify-product';
    $formId = $isAdd ? 'modal-add-product-form' : 'modal-modify-product-form';
    $action = $isAdd ? route('admin.products.store') : route('admin.products.update');
    $title = $isAdd ? 'Nuevo plato' : 'Editar plato';
    $submitLabel = $isAdd ? 'Añadir plato' : 'Guardar cambios';
    $enabledName = $isAdd ? 'product_add_enabled' : 'product_modify_enabled';
    $enabledId = $isAdd ? 'product-add-enabled-switch' : 'product-modify-enabled';
@endphp
<div class="modal fade webnu-product-modal" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="{{ $modalId }}-title">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form enctype="multipart/form-data" method="POST" id="{{ $formId }}" class="webnu-product-modal__form" action="{{ $action }}">
                @csrf
                @if(!$isAdd)
                    @method('PUT')
                @endif
                <div class="modal-body">
                    @include('admin.sections.partials.product-form-fields', [
                        'mode' => $mode ?? 'add',
                        'allergens' => $allergens ?? collect(),
                        'enabledName' => $enabledName,
                        'enabledId' => $enabledId,
                        'isAddMode' => $isAdd,
                    ])
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

