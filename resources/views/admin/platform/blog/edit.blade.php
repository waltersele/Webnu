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

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2 mb-4">
        <form method="POST" action="{{ route('admin.platform.blog.publish', $post) }}">
            @csrf
            <button type="submit" class="btn btn-success btn-sm">Publicar</button>
        </form>
        <form method="POST" action="{{ route('admin.platform.blog.draft', $post) }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">Borrador</button>
        </form>
        @php $es = $post->translationFor('es'); @endphp
        @if($es && $post->isPubliclyVisible())
            <a href="{{ $es->publicUrl() }}" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">Ver en web</a>
        @endif
        <form method="POST" action="{{ route('admin.platform.blog.destroy', $post) }}" onsubmit="return confirm('¿Eliminar artículo?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
        </form>
    </div>

    @include('admin.platform.blog.partials.post-form', [
        'formAction' => route('admin.platform.blog.update', $post),
        'formMethod' => 'PUT',
        'submitLabel' => 'Guardar cambios',
    ])
</div>
@endsection
