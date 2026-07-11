@extends('admin.layout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Editar categoría</h4>
        <a href="{{ route('admin.platform.blog.categories.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
    </div>

    <form method="POST" action="{{ route('admin.platform.blog.categories.update', $category) }}" class="card mb-3">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug) }}">
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>

    @if(!$category->posts()->exists())
        <form method="POST" action="{{ route('admin.platform.blog.categories.destroy', $category) }}" onsubmit="return confirm('¿Eliminar categoría?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar categoría</button>
        </form>
    @endif
</div>
@endsection
