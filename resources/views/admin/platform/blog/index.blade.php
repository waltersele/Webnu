@extends('admin.layout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Blog Webnu</h4>
    </div>

    @if(session('flash'))
        <div class="alert alert-success">{{ session('flash') }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Título (ES)</th>
                        <th>Estado</th>
                        <th>Publicado</th>
                        <th>Idiomas</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                        @php
                            $es = $post->translations->firstWhere('locale', 'es');
                        @endphp
                        <tr>
                            <td>{{ $es->title ?? '—' }}</td>
                            <td>
                                <span class="badge bg-label-{{ $post->status === 'published' ? 'success' : 'secondary' }}">
                                    {{ $post->status }}
                                </span>
                            </td>
                            <td>{{ optional($post->published_at)->format('d/m/Y H:i') ?: '—' }}</td>
                            <td>{{ $post->translations->pluck('locale')->implode(', ') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.platform.blog.edit', $post) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No hay artículos. Sonartop los creará vía Content Connector.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $posts->links() }}</div>
</div>
@endsection
