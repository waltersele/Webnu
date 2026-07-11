@extends('admin.layout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Blog Webnu</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.platform.blog.categories.index') }}" class="btn btn-outline-secondary btn-sm">Categorías</a>
            <a href="{{ route('admin.platform.blog.create') }}" class="btn btn-primary btn-sm">Nuevo artículo</a>
        </div>
    </div>

    @if(session('flash'))
        <div class="alert alert-success">{{ session('flash') }}</div>
    @endif

    <form method="GET" class="card mb-4">
        <div class="card-body row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Buscar (título ES)</label>
                <input type="text" name="q" class="form-control" value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    @foreach([\App\BlogPost::STATUS_DRAFT, \App\BlogPost::STATUS_PUBLISHED, \App\BlogPost::STATUS_SCHEDULED] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Categoría</label>
                <select name="category_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Título (ES)</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th>Publicado</th>
                        <th>Idiomas</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                        @php $es = $post->translations->firstWhere('locale', 'es'); @endphp
                        <tr>
                            <td>{{ $es->title ?? '—' }}</td>
                            <td>{{ $post->category->name ?? '—' }}</td>
                            <td>
                                @php
                                    $badge = 'secondary';
                                    if ($post->status === \App\BlogPost::STATUS_PUBLISHED) {
                                        $badge = 'success';
                                    } elseif ($post->status === \App\BlogPost::STATUS_SCHEDULED) {
                                        $badge = 'warning';
                                    }
                                @endphp
                                <span class="badge bg-label-{{ $badge }}">{{ $post->status }}</span>
                            </td>
                            <td>{{ optional($post->published_at)->format('d/m/Y H:i') ?: '—' }}</td>
                            <td>{{ $post->translations->pluck('locale')->implode(', ') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.platform.blog.edit', $post) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No hay artículos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $posts->links() }}</div>
</div>
@endsection
