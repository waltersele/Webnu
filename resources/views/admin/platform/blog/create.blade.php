@extends('admin.layout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Nuevo artículo</h4>
        <a href="{{ route('admin.platform.blog.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('admin.platform.blog.partials.post-form', [
        'submitLabel' => 'Crear artículo',
    ])
</div>
@endsection
