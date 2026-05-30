@extends('layouts.auth-materio')

@section('title', 'Conectar con TVPik')

@section('content')
    <div class="card webnu-auth-card">
        <div class="card-body">
            <h1 class="webnu-auth-heading">Conectar con TVPik</h1>
            <p class="webnu-auth-subheading">Inicia sesión con tu cuenta Webnu para mostrar tu carta en las pantallas TVPik.</p>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('integrations.tvpik.login') }}" class="webnu-auth-form">
                @csrf
                <input type="hidden" name="redirect_uri" value="{{ $redirect_uri }}">
                <input type="hidden" name="state" value="{{ $state }}">

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="icon-base ri ri-link me-1"></i> Autorizar y conectar
                </button>
            </form>

            <p class="text-muted small mt-3 mb-0">
                Si ya iniciaste sesión en Webnu en otra pestaña, recarga esta página para autorizar sin volver a escribir la contraseña.
            </p>
        </div>
    </div>
@endsection
