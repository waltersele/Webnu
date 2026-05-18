@extends('admin.layout')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">Actualizar negocio</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.companies.index') }}">Negocios</a></li>
            <li class="breadcrumb-item active">Actualizar negocio</li>
            </ol>
        </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="card card-success">
            <div class="card-header">
              <h3 class="card-title">Formulario de negocio</h3>
            </div>
            <form role="form" method="POST" action="{{ route('admin.companies.update', $company) }}">
                {{ csrf_field() }} {{ method_field('PUT') }}
                <!-- /.card-header -->
                <div class="card-body">
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
                            <!-- dropzone -->
                            <div class="mb-3">
                                <label>Logo</label>
                                <div class="dropzone"></div>
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
                        <div class="col-sm-12">
                            <!-- textarea -->
                            <div class="mb-3">
                                <label>Observaciones</label>
                            <textarea class="form-control" name="comments" rows="3" placeholder="Observaciones">{{ old('comments', $company->comments) }}</textarea>
                            </div>
                        </div>
                    </div>
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
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">Guardar</button>
                    <a href="{{ route('admin.companies.index', $company) }}" type="button" class="btn btn-default float-right">Cancelar</a>
                </div>
                <!-- /.card-footer -->
            </form>
        </div>
        <!-- /.card -->
    </div>
</div>
@stop

@push('styles')
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.0/dropzone.min.css">
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.0/min/dropzone.min.js"></script>

    <script>
        var myDropzone = new Dropzone('.dropzone', {
            init: function(){
                @if ($company->logo != null)
                    myDropzone = this;
                    var mockFile = { name: 'logo', size: '2000' };
                    myDropzone.emit("addedfile", mockFile);
                    myDropzone.emit("thumbnail", mockFile, '/img/{{ $company->logo }}');
                    myDropzone.emit("complete", mockFile);
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

        myDropzone.on('error', function(file, res){
            console.log(res);
            $('.dz-error-message:last > span').text(res);
        });

        Dropzone.autoDiscover = false;
    </script>
@endpush
