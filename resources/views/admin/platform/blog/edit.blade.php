@extends('admin.layout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Editar artículo #{{ $post->id }}</h4>
        <a href="{{ route('admin.platform.blog.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
    </div>

    @if(session('flash'))
        <div class="alert alert-success">{{ session('flash') }}</div>
    @endif

    <div class="d-flex gap-2 mb-4">
        <form method="POST" action="{{ route('admin.platform.blog.publish', $post) }}">
            @csrf
            <button type="submit" class="btn btn-success btn-sm">Publicar</button>
        </form>
        <form method="POST" action="{{ route('admin.platform.blog.draft', $post) }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">Borrador</button>
        </form>
        <form method="POST" action="{{ route('admin.platform.blog.destroy', $post) }}" onsubmit="return confirm('¿Eliminar artículo?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
        </form>
    </div>

    <form method="POST" action="{{ route('admin.platform.blog.update', $post) }}">
        @csrf
        @method('PUT')

        <ul class="nav nav-tabs mb-3">
            @foreach($locales as $locale)
                <li class="nav-item">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }}" type="button" data-bs-toggle="tab" data-bs-target="#tab-{{ $locale }}">
                        {{ strtoupper($locale) }}
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach($locales as $locale)
                @php $tr = $post->translations->firstWhere('locale', $locale); @endphp
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $locale }}">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" name="translations[{{ $locale }}][slug]" class="form-control" value="{{ old('translations.'.$locale.'.slug', $tr->slug ?? '') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Título</label>
                                <input type="text" name="translations[{{ $locale }}][title]" class="form-control" value="{{ old('translations.'.$locale.'.title', $tr->title ?? '') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Extracto</label>
                                <textarea name="translations[{{ $locale }}][excerpt]" class="form-control" rows="2">{{ old('translations.'.$locale.'.excerpt', $tr->excerpt ?? '') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contenido (HTML o Markdown)</label>
                                <textarea name="translations[{{ $locale }}][body]" class="form-control" rows="12">{{ old('translations.'.$locale.'.body', $tr->body ?? '') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Meta title</label>
                                <input type="text" name="translations[{{ $locale }}][meta_title]" class="form-control" value="{{ old('translations.'.$locale.'.meta_title', $tr->meta_title ?? '') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Meta description</label>
                                <textarea name="translations[{{ $locale }}][meta_description]" class="form-control" rows="2">{{ old('translations.'.$locale.'.meta_description', $tr->meta_description ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary mt-3">Guardar cambios</button>
    </form>
</div>
@endsection
