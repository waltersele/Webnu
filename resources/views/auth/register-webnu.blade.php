<!DOCTYPE html>
<html class="scroll-smooth" lang="es">
<head>
    @include('landing.partials.head')
    <title>Crear cuenta — Webnu.es</title>
</head>
<body class="bg-background text-on-surface text-body-md min-h-screen flex flex-col">
@php
    $loginUrl = route('login');
    $homeUrl = route('home');
@endphp

<nav class="flex justify-between items-center w-full px-margin-mobile md:px-gutter max-w-container-max mx-auto h-20 border-b border-border-subtle">
    <a href="{{ $homeUrl }}" class="inline-flex items-center" title="Webnu">
        @include('partials.brand-logo', ['brandKey' => 'logo', 'brandClass' => 'landing-brand-logo'])
    </a>
    <a href="{{ $loginUrl }}" class="text-label-md text-text-muted hover:text-primary transition-colors">¿Ya tienes cuenta? Inicia sesión</a>
</nav>

<main class="flex-1 flex items-center justify-center px-margin-mobile md:px-gutter py-12">
    <div class="w-full max-w-md bg-surface-container-lowest border border-border-subtle p-8 rounded-xl shadow-sm">
        <div class="mb-6 text-center">
            <h1 class="font-headline text-headline-md text-on-surface">Completa tu cuenta</h1>
            <p class="mt-2 text-label-md text-text-muted">Plan gratis · <strong>30 días de Plus gratis</strong> · Sin tarjeta</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="text-label-md text-on-surface-variant block mb-1">Email profesional</label>
                <input id="email" type="email" name="email" value="{{ old('email', $prefillEmail ?? '') }}" required autocomplete="email" autofocus
                    class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary focus:border-primary outline-none @error('email') border-red-500 @enderror"/>
                @error('email')
                    <p class="mt-1 text-label-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="business_name" class="text-label-md text-on-surface-variant block mb-1">Nombre del restaurante</label>
                <input id="business_name" type="text" name="business_name" value="{{ old('business_name') }}" required autocomplete="organization"
                    class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary focus:border-primary outline-none @error('business_name') border-red-500 @enderror"
                    placeholder="Ej. La Brasa de Juan"/>
                @error('business_name')
                    <p class="mt-1 text-label-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="text-label-md text-on-surface-variant block mb-1">Tu nombre</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                    class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary outline-none @error('name') border-red-500 @enderror"
                    placeholder="Nombre y apellidos"/>
                @error('name')
                    <p class="mt-1 text-label-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="text-label-md text-on-surface-variant block mb-1">Contraseña</label>
                <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password"
                    class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary outline-none @error('password') border-red-500 @enderror"
                    placeholder="Mínimo 8 caracteres"/>
                @error('password')
                    <p class="mt-1 text-label-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password-confirm" class="text-label-md text-on-surface-variant block mb-1">Confirmar contraseña</label>
                <input id="password-confirm" type="password" name="password_confirmation" required autocomplete="new-password"
                    class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary outline-none"/>
            </div>

            <label class="flex items-start gap-2 text-label-md text-text-muted cursor-pointer">
                <input type="checkbox" name="privacy_policy" value="1" required class="mt-1 rounded border-border-subtle"/>
                Acepto la política de privacidad
            </label>

            <button type="submit" class="w-full py-4 bg-primary text-on-primary text-label-md rounded-lg hover:opacity-90 font-semibold">
                Crear mi carta gratis
            </button>
        </form>

        <p class="mt-6 text-center text-label-sm text-text-muted">
            <a href="{{ $homeUrl }}" class="text-primary hover:underline">← Volver a la landing</a>
        </p>
    </div>
</main>
</body>
</html>
