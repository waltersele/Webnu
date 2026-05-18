@extends('admin.layout')

@section('page_title', 'Editar: ' . $company->name)

@section('content')
<ul class="webnu-breadcrumb">
    <li><a href="{{ route('admin.companies.index') }}">Negocios</a></li>
    <li>/</li>
    <li>Editar</li>
</ul>
<div class="webnu-card">
    <form method="POST" action="{{ route('admin.companies.update', $company) }}">
        @csrf
        @method('PUT')
        <div class="webnu-card__body">
                    <div class="row">
                        <div class="col-sm-4">
                            <!-- text input -->
                            <div class="mb-3">
                                <label>Nombre Comercial</label>
                                <input type="text" name="name" value="{{ old('name', $company->name) }}" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="Nombre negocio">
                                {!! $errors->first('name', '<span class="error invalid-feedback">:message</span>') !!}
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <!-- text input -->
                            <div class="mb-3">
                                <label>Nombre Chef</label>
                                <input type="text" name="chef_name" value="{{ old('chef_name', $company->chef_name) }}" class="form-control" placeholder="Nombre chef">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <!-- text input -->
                            <div class="mb-3">
                                <label>Slug</label>
                                <input type="text" name="slug" disabled="disabled" value="{{ old('slug', $company->slug) }}" class="form-control" placeholder="Enlace carta">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <!-- dropzone -->
                            <div class="mb-3">
                                <label>Logo</label>
                                <div class="dropzone dropzone-logo"></div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <!-- dropzone -->
                            <div class="mb-3">
                                <label>Imagen cabecera</label>
                                <div class="dropzone dropzone-header"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label>Dirección</label>
                                <input type="text" name="address" value="{{ old('address', $company->address) }}" class="form-control" placeholder="Dirección">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="mb-3">
                                <label>Código Postal</label>
                                <input type="text" name="postal_code" value="{{ old('postal_code', $company->postal_code) }}" class="form-control" placeholder="Código Postal">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="mb-3">
                                <label>Localidad</label>
                                <input type="text" name="city" value="{{ old('city', $company->city) }}" class="form-control" placeholder="Localidad">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="mb-3">
                                <label>Provincia</label>
                                <input type="text" name="province" value="{{ old('province', $company->province) }}" class="form-control" placeholder="Provincia">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="mb-3">
                                <label>País</label>
                                <input type="text" name="country" value="{{ old('country', $company->country) }}" class="form-control" placeholder="País">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="mb-3">
                                <label>Teléfono</label>
                                <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" class="form-control" placeholder="Teléfono">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="mb-3">
                                <label>Móvil</label>
                                <input type="text" name="mobile_phone" value="{{ old('mobile_phone', $company->mobile_phone) }}" class="form-control" placeholder="Móvil">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="mb-3">
                                <label>Whatsapp</label>
                                <input type="text" name="whatsapp" value="{{ old('whatsapp', $company->whatsapp) }}" class="form-control" placeholder="Whatsapp">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label>E-mail</label>
                                <input type="text" name="email" value="{{ old('email', $company->email) }}" class="form-control" placeholder="E-mail">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label>Web</label>
                                <input type="text" name="web" value="{{ old('web', $company->web) }}" class="form-control" placeholder="Web">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label>Facebook</label>
                                <input type="text" name="facebook" value="{{ old('facebook', $company->facebook) }}" class="form-control" placeholder="Facebook">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label>Instagram</label>
                                <input type="text" name="instagram" value="{{ old('instagram', $company->instagram) }}" class="form-control" placeholder="Instagram">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3">
                            <label > Horario</label>
                            <textarea name="schedule" id="schedule" cols="100" rows="4" class="form-control" placeholder="Introduce aquí tu horario.">{{ old('schedule', $company->schedule) }}</textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-8">
                            <!-- textarea -->
                            <div class="mb-3">
                                <label>Descripción / Slogan</label>
                            <textarea class="form-control" maxlength="80" name="comments" rows="3" placeholder="Descripción / Slogan del negocio">{{ old('comments', $company->comments) }}</textarea>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="mb-3">
                                <label>Plantilla de carta</label>
                                <select class="" name="template" data-placeholder="Seleccionar plantilla" style="width: 100%;">
                                    <option value="basic" {{ $company->template == 'basic' ? 'selected' : '' }}>Básica</option>
                                    <option value="pasion" {{ $company->template == 'pasion' ? 'selected' : '' }}>Pasión</option>
                                    <option value="oriental" {{ $company->template == 'oriental' ? 'selected' : '' }}>Oriental</option>
                                    <option value="visual" {{ $company->template == 'visual' ? 'selected' : '' }}>Visual</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    {{-- ADD RESERVATION --}}

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="reservation" value="{{ old('reservation', $company->reservation) }}" {{ $company->reservation ? 'checked="checked"' : ''}} id="company-reservation-switch">
                                    <label class="form-check-label" for="company-reservation-switch">Permitir reservas</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- END RESERVATION --}}
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="enabled" value="{{ old('enabled', $company->enabled) }}" {{ $company->enabled ? 'checked="checked"' : ''}} id="company-enabled-switch">
                                    <label class="form-check-label" for="company-enabled-switch">Habilitado</label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.card-body -->
                <div class="webnu-card__body border-top">
                    <button type="submit" class="webnu-btn webnu-btn--primary webnu-btn--block">Guardar cambios</button>
                    <hr class="divider">
                    <div class="row">
                        <div class="col-md-6">
                            <a class="text-danger delete-company-btn" data-id="{{ $company->id }}" data-bs-toggle="modal" data-bs-target="#modal-delete-company">
                            <i class="fas fa-trash"></i> Eliminar {{ $company->name}}</a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('admin.companies.index') }}" class="webnu-btn webnu-btn--ghost float-right">Cancelar</a>
                        </div>
                    </div>
                </div>
                <!-- /.card-footer -->
            </form>

            <!-- modal delete -->
            <div class="modal fade" id="modal-delete-company">
                <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <h4 class="modal-title">Eliminar negocio</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form role="form" method="POST" action="{{ route('admin.companies.delete') }}">
                        {{ csrf_field() }} {{ method_field('DELETE') }}
                        <div class="modal-body">
                            <div class="mb-3">
                                <p>¿Estás seguro de eliminar el negocio?</p>
                                <input type="hidden" value="" name="companyid" id="delete-id">
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="submit" class="webnu-btn webnu-btn--secondary">Eliminar negocio</button>
                            <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </form>
                </div>
                <!-- /.modal-content -->
                </div>
            <!-- /.modal-dialog -->
            </div>
            <!-- End modal delete -->
</div>
@stop

@push('styles')
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.0/dropzone.min.css">
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.0/min/dropzone.min.js"></script>

    <script>
        //Logo
        var dropzoneLogo = new Dropzone('.dropzone-logo', {
            init: function(){
                @if ($company->logo != null)
                    dropzoneLogo = this;
                    var mockFile = { name: 'logo', size: '2000' };
                    dropzoneLogo.emit("addedfile", mockFile);
                    dropzoneLogo.emit("thumbnail", mockFile, '/img/{{ $company->logo }}');
                    dropzoneLogo.emit("complete", mockFile);
                @endif
            },
            addRemoveLinks: true,
            removedfile: function(file) {
                var name = file.name;
                console.log(file.name);
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    type: 'DELETE',
                    url: '/admin/companies/{{ $company->id }}/deletelogo',
                    data: {filename:name},
                    dataType: 'html'
                });
                var fileRef;
                return (fileRef = file.previewElement) != null ?
                      fileRef.parentNode.removeChild(file.previewElement) : void 0;
            },
            url: '/admin/companies/{{ $company->id }}/logo',
            acceptedFiles: 'image/*',
            maxFilesize: 5,
            maxFiles: 1,
            paramName: 'logo',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            dictDefaultMessage: 'Arrastra el logo o haz clic aquí'
        });

        dropzoneLogo.on('error', function(file, res){
            console.log(res);
            $('.dz-error-message:last > span').text(res);
        });

        //Imagen de cabecera
        var dropzoneHeader = new Dropzone('.dropzone-header', {
            init: function(){
                @if ($company->background_header != null)
                    dropzoneHeader = this;
                    var mockFile = { name: 'background_header', size: '2000' };
                    dropzoneHeader.emit("addedfile", mockFile);
                    dropzoneHeader.emit("thumbnail", mockFile, '/img/{{ $company->background_header }}');
                    dropzoneHeader.emit("complete", mockFile);
                @endif
            },
            addRemoveLinks: true,
            removedfile: function(file) {
                var name = file.name;
                console.log(file.name);
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    type: 'DELETE',
                    url: '/admin/companies/{{ $company->id }}/deleteheader',
                    data: {filename:name},
                    dataType: 'html'
                });
                var fileRef;
                return (fileRef = file.previewElement) != null ?
                      fileRef.parentNode.removeChild(file.previewElement) : void 0;
            },
            url: '/admin/companies/{{ $company->id }}/header',
            acceptedFiles: 'image/*',
            maxFilesize: 5,
            maxFiles: 1,
            paramName: 'header',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            dictDefaultMessage: 'Arrastra la imagen o haz clic aquí'
        });

        dropzoneHeader.on('error', function(file, res){
            console.log(res);
            $('.dz-error-message:last > span').text(res);
        });

        Dropzone.autoDiscover = false;
    </script>
@endpush