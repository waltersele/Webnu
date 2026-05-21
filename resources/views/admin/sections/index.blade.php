@extends('admin.layout')

@section('page_title', 'Mi Carta')
@section('page_subtitle', 'Gestiona secciones y platos de ' . $company->name)

@section('page_actions')
    <iframe src="{{ route('see_menu', $company->slug) }}" style="display:none" name="printMenu"></iframe>
    @php
        $pf = $planFeatures ?? [];
        $hasTranslation = $pf['translation'] ?? true;
        $hasMenuScan = $pf['menu_scan'] ?? true;
        $ut = $upgradeTriggers ?? [];
        $languageTrigger = ! $hasTranslation && ($ut['show_language_trigger'] ?? false);
        $languagesUrl = route('admin.companies.languages', $company);
    @endphp
    <a class="btn btn-outline-primary {{ $languageTrigger ? '' : '' }}"
       href="{{ $languagesUrl }}"
       @if ($languageTrigger) data-upgrade-trigger="translation" data-upgrade-fallback-href="{{ $languagesUrl }}" @endif>
        <i class="ri ri-translate-2 me-1"></i> Idiomas
        @if (! $hasTranslation)
            @include('admin.partials.plan-pro-badge', ['label' => 'Plus', 'size' => 'xs'])
        @endif
    </a>
    <a class="btn btn-outline-success {{ ! $hasMenuScan ? 'opacity-75' : '' }}" href="{{ route('admin.menu-scan.create') }}">
        <i class="ri ri-camera-line me-1"></i> Importar desde foto o PDF
        @if (! $hasMenuScan)
            @include('admin.partials.plan-pro-badge', ['label' => 'Plus', 'size' => 'xs'])
        @endif
    </a>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-share-menu">
        <i class="ri ri-share-line me-1"></i> Compartir carta
    </button>
    <div class="btn-group" role="group">
        <a class="btn btn-outline-primary" href="{{ route('see_menu', $company->slug) }}" target="_blank" rel="noopener">
            <i class="ri ri-eye-line me-1"></i> Vista previa
        </a>
        <a class="btn btn-outline-secondary" href="{{ route('admin.menu-print', $company) }}" target="_blank" rel="noopener">
            <i class="ri ri-file-pdf-line me-1"></i> Carta A4 (PDF)
        </a>
        <button type="button" class="btn btn-outline-secondary" onclick="frames['printMenu'].print(); return false;" title="Imprimir la vista web de la carta">
            <i class="ri ri-printer-line me-1"></i> Imprimir web
        </button>
        <a class="btn btn-primary" href="{{ route('admin.qrgenerator', $company) }}" target="_blank" rel="noopener">
            <i class="ri ri-qr-code-line me-1"></i> QR
        </a>
    </div>
@endsection

@section('content')
@php
    $ut = $upgradeTriggers ?? [];
    $showLangBanner = ! ($planFeatures['translation'] ?? true) && ($ut['show_language_trigger'] ?? false);
@endphp
@if ($showLangBanner)
    <div class="alert alert-primary d-flex flex-wrap align-items-center gap-3 mb-4 wn-upgrade-lang-banner">
        <i class="ri-global-line fs-4 shrink-0"></i>
        <div class="flex-grow-1">
            <strong>Clientes internacionales</strong>
            <p class="mb-0 small">{{ $ut['copy']['translation_banner'] ?? 'Activa idiomas en tu carta con Plus.' }}</p>
        </div>
        <button type="button" class="btn btn-sm btn-primary" data-upgrade-trigger="translation">Activar idiomas (Plus)</button>
        <a href="{{ route('admin.companies.languages', $company) }}" class="btn btn-sm btn-label-secondary">Ver idiomas</a>
    </div>
@endif
@include('admin.sections.partials.menu-page-content')

<div class="webnu-menu-modals">
        <div class="modal fade" id="modal-add-section">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Nueva sección</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.sections.store') }}">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Nombre</label>
                                <input type="text" name="name" autofocus value="{{ old('name') }}" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="Ej. Entrantes" required>
                                {!! $errors->first('name', '<span class="error invalid-feedback">:message</span>') !!}
                            </div>
                            <div class="form-group mb-0">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="section_enabled" id="section-add-enabled-switch" value="1" checked>
                                    <label class="form-check-label" for="section-add-enabled-switch">Visible en la carta</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Crear sección</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
            <!-- Modify modal - Seccion -->
            <div class="modal fade" id="modal-modify-section">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Modificar sección</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form role="form" method="POST" action="{{ route('admin.sections.update') }}">
                        {{ csrf_field() }} {{ method_field('PUT') }}
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Nombre Sección</label>
                                <input type="text" name="name" id="modify-name" autofocus value="{{ old('name') }}" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="Nombre sección" required>
                                {!! $errors->first('name', '<span class="error invalid-feedback">:message</span>') !!}
                                <input type="hidden" value="" name="sectionid" id="modify-id">
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="section_enabled" id="section-modify-enabled-switch" value="1" checked="checked">
                                    <label class="form-check-label" for="section-modify-enabled-switch">Habilitado</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->
            <!-- Delete modal - Seccion -->
            <div class="modal fade" id="modal-delete-section">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Eliminar sección</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form role="form" method="POST" action="{{ route('admin.sections.delete') }}">
                        {{ csrf_field() }} {{ method_field('DELETE') }}
                        <div class="modal-body">
                            <div class="mb-3">
                                <p>¿Estás seguro de eliminar la sección?</p>
                                <input type="hidden" value="" name="sectionid" id="delete-id">
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-outline-danger">Eliminar</button>
                        </div>
                    </form>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->
            @include('admin.sections.partials.product-modal', ['mode' => 'add', 'allergens' => $allergens])
            @include('admin.sections.partials.product-modal', ['mode' => 'modify', 'allergens' => $allergens])
            <!-- Delete modal - Producto -->
            <div class="modal fade" id="modal-delete-product">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Eliminar producto</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form role="form" method="POST" action="{{ route('admin.products.delete') }}">
                        {{ csrf_field() }} {{ method_field('DELETE') }}
                        <div class="modal-body">
                            <div class="mb-3">
                                <p>¿Estás seguro de eliminar el producto?</p>
                                <input type="hidden" value="" name="productid" id="product-delete-id">
                                <input type="hidden" value="" name="product_delete_section_id" id="product-delete-section-id">
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-outline-danger">Eliminar</button>
                        </div>
                    </form>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->
</div>
@stop

@push('styles')
    <!-- Select2 -->
    <!-- Jquery UI -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
     <!-- Toastr -->
    <link rel="stylesheet" href="{{asset('adminlte/plugins/toastr/toastr.min.css')}}">
@endpush


@push('scripts')
<!-- Select2 -->
<!-- Jquery UI sortable -->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<!-- Toastr -->
<script src="{{asset('adminlte/plugins/toastr/toastr.min.js')}}"></script>
<script>
    window.WebnuProductMedia = {
        baseUrl: '{{ URL::to('/') }}',
        maxVideoSeconds: {{ config('product_media.max_video_seconds', 30) }}
    };
</script>
<script src="{{ asset('adminlte/js/product-media.js') }}"></script>

<script>
    // $(function () {
    //     $('#sections-table').DataTable({
    //     "paging": true,
    //     "lengthChange": false,
    //     "searching": false,
    //     "ordering": true,
    //     "info": true,
    //     "autoWidth": false,
    //     "responsive": false,
    //     });
    // });
</script>

<script>
// Vista previa PDF (archivo seleccionado antes de guardar)
(function () {
    var pdfObjectUrl = null;
    var $preview = $('#pdf-menu-preview');
    var $filename = $('#pdf-preview-filename');
    var savedPdfUrl = $('#pdf-menu').data('pdf-url') || '';

    function revokePdfObjectUrl() {
        if (pdfObjectUrl) {
            URL.revokeObjectURL(pdfObjectUrl);
            pdfObjectUrl = null;
        }
    }

    function showPdfInPreview(url) {
        var $frame = $('#pdf-preview-frame');
        if (!$frame.length) {
            $preview.empty().append(
                $('<iframe>', {
                    id: 'pdf-preview-frame',
                    title: 'Vista previa del PDF de la carta',
                    src: url + '#toolbar=1&navpanes=0'
                })
            );
        } else {
            $frame.attr('src', url + '#toolbar=1&navpanes=0');
        }
        $('#pdf-preview-empty').remove();
    }

    function showEmptyPreview(message) {
        revokePdfObjectUrl();
        $('#pdf-preview-frame').remove();
        if (!$('#pdf-preview-empty').length) {
            $preview.html(
                '<div class="webnu-pdf-preview__empty" id="pdf-preview-empty">' +
                '<i class="fas fa-file-pdf"></i><p></p></div>'
            );
        }
        $('#pdf-preview-empty p').text(message || 'Aún no hay PDF. Sube uno para ver la vista previa aquí.');
        $filename.hide();
    }

    $('#pdf-menu-file').on('change', function () {
        var file = this.files && this.files[0];
        revokePdfObjectUrl();
        if (!file) {
            if (savedPdfUrl) {
                showPdfInPreview(savedPdfUrl);
                $filename.show();
            } else {
                showEmptyPreview();
            }
            return;
        }
        if (file.type !== 'application/pdf') {
            showEmptyPreview('Selecciona un archivo PDF válido.');
            return;
        }
        pdfObjectUrl = URL.createObjectURL(file);
        showPdfInPreview(pdfObjectUrl);
        $filename.html('<i class="fas fa-file-pdf text-primary"></i> Vista previa: <strong>' + file.name + '</strong> (guarda para publicar)').show();
    });
})();

function setMenuTypePanels(isCustom) {
    if (isCustom) {
        $('#custom-menu').show();
        $('#pdf-menu').hide();
    } else {
        $('#custom-menu').hide();
        $('#pdf-menu').show();
    }
}

$(document).ready(function () {
    setMenuTypePanels($('#menu-type-custom').is(':checked'));
});

$('input[type=radio][name=menu_type]').on('change', function () {
    var isCustom = this.value === 'menu_type_custom';
    $('.webnu-menu-type__option').removeClass('is-active');
    $(this).closest('.webnu-menu-type__option').addClass('is-active');
    setMenuTypePanels(isCustom);
    update_menu_type(this.value);
});
// Actualizar el tipo de menu seleccionado
function update_menu_type(menuType){
    let baseurl = '{{URL::to('/')}}';
    let token = '{{ csrf_token() }}';
    let companyId = '{{ $company->id }}';

    $.ajax({
        url: baseurl+'/admin/sections/update_menu_type',
        type: 'POST',
        dataType: "JSON",
        data: {
                "company_id": companyId,
                "menu_type": menuType,
                "_method": 'PUT',
                "_token": token,
            },
        cache: false,
        success: function(result) {
            toastr.success('Tipo de carta cambiado correctamente.')
        },
        error: function(request,msg,error) { // What to do if we fail
            toastr.error('Se produjo un error al intentar cambiar el tipo de carta.')
        }
    });

    return false;
}


$(document).on('click', '.delete-section-btn', function(e){
    e.preventDefault();
    let deleteId = $(this).attr('data-id');
    $('#delete-id').val(deleteId);
});
$(document).on('click', '.modify-section-btn', function(e){
    e.preventDefault();
    let modifyId = $(this).attr('data-id');
    $('#modify-id').val(modifyId);
    let modifyName = $(this).attr('data-name');
    $('#modify-name').val(modifyName);
    let modifyEnabled = $(this).attr('data-enabled');
    if(modifyEnabled == "1"){
        $('#section-modify-enabled-switch').attr("checked", "checked");
    }
    else{
        $('#section-modify-enabled-switch').removeAttr("checked");
    }
});

//Productos
$(document).on('click', '.product-add-btn', function(){
    let sectionId = $(this).attr('section-id');
    $('#product-add-section-id').val(sectionId);
});
$(document).on('click','.product-modify',function(){
    let dataId = $(this).attr('data-id');
    let dataName = $(this).attr('data-name');
    let dataDescription = $(this).attr('data-description');
    let dataImage = $(this).attr('data-image');
    let dataVideo = $(this).attr('data-video');
    let dataPriceUnit = $(this).attr('data-price-unit');
    let dataPricePortion = $(this).attr('data-price-portion');
    let dataIndividualSale = $(this).attr('data-individual-sale');
    let dataWeightSale = $(this).attr('data-weight-sale');
    let dataEnabled = $(this).attr('data-enabled');
    let sectionId = $(this).attr('data-section-id');
    let dataAllergens = $(this).attr('data-allergens');
    /*console.log('ID: '+dataId);
    console.log('Name: '+dataName);
    console.log('Description: '+dataDescription);
    console.log('Image: '+dataImage);
    console.log('Price unit: '+dataPriceUnit);
    console.log('Price portion: '+dataPricePortion);
    console.log('Individual Sale: '+dataIndividualSale);
    console.log('Enabled: '+dataEnabled);
    console.log('Section ID: '+sectionId);
    console.log(dataAllergens);*/
    $('#product-modify-id').val(dataId);
    $('#product-add-section-id').val(sectionId);
    $('#product-modify-name').val(dataName);
    $('#product-modify-description').val(dataDescription);
    $('#product-modify-price-unit').val(dataPriceUnit);
    $('#product-modify-price-portion').val(dataPricePortion);
    $('#product-modify-section-id').val(sectionId);
    $('#delete-image-product-id').attr('product-id', dataId);
    $('#delete-video-product-id').attr('product-id', dataId);

    if (window.WebnuProductMediaUI) {
        window.WebnuProductMediaUI.loadModifyImage(dataImage || '');
        window.WebnuProductMediaUI.loadModifyVideo(dataVideo || '');
    } else if (dataImage) {
        $('#product-modify-image-ok').attr('src', "{{ URL::to('/') }}/img/" + dataImage);
        $('#product-modify-image-existing').show();
        $('#product-modify-image').closest('.webnu-file-drop').hide();
    } else {
        $('#product-modify-image-ok').attr('src', '');
        $('#product-modify-image-existing').hide();
        $('#product-modify-image').closest('.webnu-file-drop').show();
    }

    $('#product-modify-enabled').prop('checked', dataEnabled == 1 || dataEnabled == '1');

    var hasPortion = dataPricePortion != '' && dataPricePortion != null;
    $('#product-modify-enable-price-portion-switch').prop('checked', hasPortion);
    $('#product-modify-portion-price-group').toggleClass('hidden', !hasPortion);

    $('#product-modify-individual-sale').prop('checked', dataIndividualSale == 1 || dataIndividualSale == '1');
    $('#product-modify-weight-sale').prop('checked', dataWeightSale == 1 || dataWeightSale == '1');
    $('#product-modify-weight-unit-label').val($(this).attr('data-weight-unit-label') || '');
    if (window.syncWeightLabelWrap && document.getElementById('product-modify-weight-sale')) {
        window.syncWeightLabelWrap(document.getElementById('product-modify-weight-sale'));
    }

    var allergenIds = [];
    try {
        var parsed = JSON.parse(dataAllergens || '[]');
        if (Array.isArray(parsed)) {
            allergenIds = parsed.map(function (item) {
                return typeof item === 'object' && item !== null ? item.id : item;
            });
        }
    } catch (e) {
        allergenIds = [];
    }

    var $picker = $('#product-modify-allergens-picker');
    $picker.find('.webnu-allergen-chip__input').prop('checked', false);
    allergenIds.forEach(function (id) {
        $picker.find('.webnu-allergen-chip__input[value="' + id + '"]').prop('checked', true);
    });
});
// Eliminar imagen de producto
$(document).on('click','.product-image-delete',function(){
    let baseurl = '{{URL::to('/')}}';
    let productId = $(this).attr('product-id');
    let token = $(this).attr('data-token');

    $.ajax({
        url: baseurl+'/admin/products/delete_image_product/'+productId,
        type: 'DELETE',
        dataType: "JSON",
        data: {
                "product_id": productId,
                "_method": 'DELETE',
                "_token": token,
            },
        cache: false,
        success: function(result) {
            if (window.WebnuProductMediaUI) {
                window.WebnuProductMediaUI.loadModifyImage('');
            } else {
                $('#product-modify-image-existing').hide();
                $('#product-modify-image').closest('.webnu-file-drop').show();
            }
        },
        error: function(request,msg,error) { // What to do if we fail
            alert('Se produjo un error al intentar eliminar la imagen del producto.');
        }
    });

    return false;
});
$(document).on('click','.product-video-delete',function(){
    let baseurl = '{{URL::to('/')}}';
    let productId = $(this).attr('product-id');
    let token = $(this).attr('data-token');

    $.ajax({
        url: baseurl+'/admin/products/delete_video_product/'+productId,
        type: 'DELETE',
        dataType: "JSON",
        data: {
                "_token": token,
            },
        cache: false,
        success: function(result) {
            $('#product-modify-video-ok').attr('src', '').hide();
            $('#product-modify-video').parent().removeClass('hidden');
        },
        error: function() {
            alert('Se produjo un error al intentar eliminar el vídeo del producto.');
        }
    });

    return false;
});
$(document).on('click','.product-delete',function(){
    let deleteId = $(this).attr('data-id');
    let sectionId = $(this).attr('section-id');
    $('#product-delete-id').val(deleteId);
    $('#product-delete-section-id').val(sectionId);
});

//Abrimos acordeon en caso de leer en la URL un anchor (para dejar en el mismo sitio tras refrescar)
$( document ).ready(function() {
    //let sectionCollapseId = $(this).attr('collapse-section-id');
    var full_url = window.location.href;
    var parts = full_url.split("#");
    if (parts.length > 1){
        var trgt = parts[1];
        $('[collapse-section-id='+trgt+']').CardWidget('toggle');
        //get the top offset of the target anchor
        var target_offset = $('[collapse-section-id='+trgt+']').offset();
        //var target_offset = $("#"+trgt).offset();
        $('html,body').animate({scrollTop: target_offset.top},'slow');
    }
});
function togglePortionPriceGroup(prefix, checked) {
    $('#' + prefix + '-portion-price-group').toggleClass('hidden', !checked);
}
$("#product-add-enable-price-portion-switch").on('change', function() {
    togglePortionPriceGroup('product-add', $(this).is(':checked'));
});
$("#product-modify-enable-price-portion-switch").on('change', function() {
    togglePortionPriceGroup('product-modify', $(this).is(':checked'));
});

$('#modal-add-product').on('hidden.bs.modal', function () {
    $('#product-add-allergens-picker .webnu-allergen-chip__input').prop('checked', false);
});

//Cambiamos "," por "." al introducir los precios
/*$('#product-add-price-portion').keyup(function(e) {
    if(!isNumberKey(e)){
        $('#product-add-price-portion').val($('#product-add-price-portion').val().slice(0,-1));
    }
});
$('#product-modify-price-portion').keyup(function(e) {
    if(!isNumberKey(e)){
        $('#product-modify-price-portion').val($('#product-modify-price-portion').val().slice(0,-1));
    }
});
$('#product-modify-price-unit').keyup(function(e) {
    if(!isNumberKey(e)){
        $('#product-modify-price-unit').val($('#product-modify-price-unit').val().slice(0,-1));
    }
});
$('#product-add-price-unit').keyup(function(e) {
    if(!isNumberKey(e)){
        $('#product-add-price-unit').val($('#product-add-price-unit').val().slice(0,-1));
    }
});


function isNumberKey(evt) {
    //190 es el código del punto del teclado de texto
    //110 es el código del punto del teclado numérico
    var charCode = (evt.which) ? evt.which : event.keyCode;
    if (charCode != 46 && charCode != 110 && charCode != 190 && charCode > 31
    && (charCode < 48 || charCode > 57))
        return false;

    return true;
}*/

//Ordenar secciones con jquery ui sortable
$(function(){
    //$('.sortable-section').sortable();
    $('#sortable-section').sortable({
        axis: 'y',
        handle: '.webnu-menu-section__drag',
        update: function (event, ui) {
            var newSectionOrder = $(this).sortable('toArray').toString();
            let token = $(this).attr('data-token');
            let baseurl = '{{URL::to('/')}}';
            console.log('NEW SECTION ORDER: '+newSectionOrder);

            $.ajax({
                url: baseurl+'/admin/products/order_section',
                method: 'POST',
                dataType: "JSON",
                data: {
                        "new_section_order": newSectionOrder,
                        "_method": 'PUT',
                        "_token": token,
                    },
                cache: false,
                success: function(result) {
                    toastr.success('Secciones ordenadas correctamente.')
                },
                error: function(request,msg,error) { // What to do if we fail
                    toastr.error('Se produjo un error al intentar ordenar la sección.')
                    return false;
                }
            });

            return true;
        }
    });
    $( "#sortable-section" ).disableSelection();
});

//Ordenar productos con jquery ui sortable
$(function(){
    $('.sortable-product').sortable({
        axis: 'y',
        handle: '.webnu-drag-handle',
        update: function (event, ui) {
            var sectionId = $(this).attr('section-id');
            var newProductOrder = $(this).sortable('toArray').toString();
            let token = $(this).attr('data-token');
            let baseurl = '{{URL::to('/')}}';

            $.ajax({
                url: baseurl+'/admin/products/order_product',
                method: 'POST',
                dataType: "JSON",
                data: {
                        "section_id": sectionId,
                        "new_product_order": newProductOrder,
                        "_method": 'PUT',
                        "_token": token,
                    },
                cache: false,
                success: function(result) {
                    toastr.success('Productos ordenados correctamente.')
                },
                error: function(request,msg,error) { // What to do if we fail
                    toastr.error('Se produjo un error al intentar ordenar la sección.')
                    return false;
                }
            });

            return true;
        }
    });
});

</script>

@endpush

