@extends('admin.layout')

@section('page_title', 'Negocios')

@section('page_actions')
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-add-company">
        <i class="ri ri-add-line me-1"></i> Añadir negocio
    </button>
@endsection

@section('content')
<div class="card">
    <div class="card-datatable table-responsive">
        <table id="companies-table" class="table table-hover border-top mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Población</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($companies as $company)
                    <tr>
                        <td>{{ $company->id }}</td>
                        <td>{{ $company->name }}</td>
                        <td>{{ $company->city }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-sm btn-outline-primary">
                                <i class="ri ri-pencil-line me-1"></i> Editar
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modal-add-company">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Crear negocio</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="{{ route('admin.companies.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nombre comercial</label>
                        <input type="text" name="name" autofocus value="{{ old('name') }}" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="Nombre del negocio" required>
                        {!! $errors->first('name', '<span class="invalid-feedback">:message</span>') !!}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear negocio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-delete-company">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Eliminar negocio</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="{{ route('admin.companies.delete') }}">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>¿Estás seguro de eliminar el negocio?</p>
                    <input type="hidden" name="companyid" id="delete-id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="webnu-btn webnu-btn--ghost" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="webnu-btn webnu-btn--danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('scripts')
<script>
    $(function () {
        $('#companies-table').DataTable({
            paging: true,
            lengthChange: true,
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            responsive: true,
            iDisplayLength: 25,
            order: [[1, 'desc']],
            oLanguage: {
                sProcessing: 'Procesando...',
                sLengthMenu: 'Mostrar _MENU_ registros',
                sZeroRecords: 'No se encontraron resultados',
                sEmptyTable: 'Ningún dato disponible en esta tabla',
                sInfo: 'Mostrando _START_ a _END_ de _TOTAL_',
                sInfoEmpty: 'Sin registros',
                sInfoFiltered: '(filtrado de _MAX_)',
                sSearch: 'Buscar:',
                sLoadingRecords: 'Cargando...',
                oPaginate: {
                    sFirst: 'Primero',
                    sLast: 'Último',
                    sNext: 'Siguiente',
                    sPrevious: 'Anterior'
                }
            }
        });
        jQuery('#companies-table_filter input').keyup(function () {
            $('#companies-table').dataTable().fnFilter(
                jQuery.fn.DataTable.ext.type.search.string(this.value)
            );
        });
    });
    $(document).on('click', '.delete', function () {
        $('#delete-id').val($(this).attr('data-id'));
    });
</script>
@endpush
