@extends('layouts.auth-materio')

@section('title', 'Nueva contraseña')

@section('content')
    <a href="{{ route('login') }}" class="webnu-auth-back">
        <i class="icon-base ri ri-arrow-left-s-line"></i> Volver al inicio de sesión
    </a>

    <div class="card webnu-auth-card">
        <div class="card-body">
            <h1 class="webnu-auth-heading">Nueva contraseña</h1>
            <p class="webnu-auth-subheading">Elige una contraseña segura para tu cuenta.</p>

            <form method="POST" action="{{ route('password.update') }}" class="webnu-auth-form">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group input-group-merge @error('email') has-validation @enderror">
                        <input
                            id="email"
                            type="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ $email ?? old('email') }}"
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
                            id="password"
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="············"
                            required
                            autocomplete="new-password"
                        >
                        <span class="input-group-text"><i class="icon-base ri ri-lock-line"></i></span>
                        @error('password')
                            <div class="invalid-feedback d-block" role="alert">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password-confirm" class="form-label">Confirmar contraseña</label>
                    <div class="input-group input-group-merge">
                        <input
                            id="password-confirm"
                            type="password"
                            name="password_confirmation"
                            class="form-control"
                            placeholder="············"
                            required
                            autocomplete="new-password"
                        >
                        <span class="input-group-text"><i class="icon-base ri ri-lock-2-line"></i></span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary d-grid w-100 waves-effect waves-light">
                    Cambiar contraseña
                </button>
            </form>
        </div>
    </div>
@endsection
