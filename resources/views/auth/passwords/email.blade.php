@extends('layouts.auth-materio')

@section('title', 'Recuperar contraseña')

@section('content')
    <a href="{{ route('login') }}" class="webnu-auth-back">
        <i class="icon-base ri ri-arrow-left-s-line"></i> Volver al inicio de sesión
    </a>

    <div class="card webnu-auth-card">
        <div class="card-body">
            <h1 class="webnu-auth-heading">Recuperar contraseña</h1>

            @if (session('status'))
                <div class="alert alert-success" role="alert">{{ session('status') }}</div>
            @else
                <p class="webnu-auth-subheading">Te enviaremos un enlace para restablecer tu contraseña.</p>

                <form method="POST" action="{{ route('password.email') }}" class="webnu-auth-form">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group input-group-merge @error('email') has-validation @enderror">
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}"
                                placeholder="tu@restaurante.com"
                                required
                                autocomplete="email"
                                autofocus
                            >
                            <span class="input-group-text"><i class="icon-base ri ri-mail-line"></i></span>
                            @error('email')
                                <div class="invalid-feedback d-block" role="alert">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary d-grid w-100 waves-effect waves-light">
                        Enviar enlace
                    </button>
                </form>
            @endif

            <div class="webnu-auth-footer-links">
                <a href="{{ route('login') }}">Volver a iniciar sesión</a>
            </div>
        </div>
    </div>
@endsection
