@extends('layouts.auth-materio')

@section('title', 'Iniciar sesión')

@section('content')
    <a href="{{ route('home') }}" class="webnu-auth-back">
        <i class="icon-base ri ri-arrow-left-s-line"></i> Volver al inicio
    </a>

    <div class="card webnu-auth-card">
        <div class="card-body">
            <h1 class="webnu-auth-heading">Iniciar sesión</h1>
            <p class="webnu-auth-subheading">Accede a tu panel de carta digital</p>

            <form method="POST" action="{{ route('login') }}" class="webnu-auth-form">
                @csrf

                <div class="mb-3">
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

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group input-group-merge @error('password') has-validation @enderror">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="············"
                            required
                            autocomplete="current-password"
                        >
                        <span class="input-group-text"><i class="icon-base ri ri-lock-line"></i></span>
                        @error('password')
                            <div class="invalid-feedback d-block" role="alert">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input
                            type="checkbox"
                            class="form-check-input"
                            id="remember"
                            name="remember"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="remember">Recordar contraseña</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary d-grid w-100 waves-effect waves-light">
                    Entrar
                </button>
            </form>

            @if (Route::has('password.request'))
                <div class="webnu-auth-footer-links">
                    <a href="{{ route('password.request') }}">¿Olvidaste la contraseña?</a>
                </div>
            @endif
        </div>
    </div>
@endsection
