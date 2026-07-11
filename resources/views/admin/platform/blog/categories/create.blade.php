@extends('admin.layout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Nueva categoría</h4>
        <a href="{{ route('admin.platform.blog.categories.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
    </div>

    <form method="POST" action="{{ route('admin.platform.blog.categories.store') }}" class="card">
        @csrf
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Slug (opcional)</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug') }}">
            </div>
            <button type="submit" class="btn btn-primary">Crear</button>
        </div>
    </form>
</div>
@endsection
