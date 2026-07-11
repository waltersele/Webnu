@extends('admin.layout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Categorías del blog</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.platform.blog.index') }}" class="btn btn-outline-secondary btn-sm">Artículos</a>
            <a href="{{ route('admin.platform.blog.categories.create') }}" class="btn btn-primary btn-sm">Nueva categoría</a>
        </div>
    </div>

    @if(session('flash'))
        <div class="alert alert-success">{{ session('flash') }}</div>
    @endif
    @if(session('flash_error'))
        <div class="alert alert-danger">{{ session('flash_error') }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Artículos</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td><code>{{ $category->slug }}</code></td>
                            <td>{{ $category->posts_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.platform.blog.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No hay categorías.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $categories->links() }}</div>
</div>
@endsection
