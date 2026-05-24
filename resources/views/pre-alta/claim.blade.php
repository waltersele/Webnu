<!DOCTYPE html>
<html class="scroll-smooth" lang="es">
<head>
    @include('landing.partials.head')
    <title>Activar carta — Webnu</title>
</head>
<body class="bg-background text-on-surface text-body-md min-h-screen flex flex-col">
@php
    $homeUrl = route('home');
@endphp

<nav class="flex justify-between items-center w-full px-margin-mobile md:px-gutter max-w-container-max mx-auto h-20 border-b border-border-subtle">
    <a href="{{ $homeUrl }}" class="inline-flex items-center" title="Webnu">
        @include('partials.brand-logo', ['brandKey' => 'logo', 'brandClass' => 'landing-brand-logo'])
    </a>
</nav>

<main class="flex-1 flex items-center justify-center px-margin-mobile md:px-gutter py-12">
    <div class="w-full max-w-md bg-surface-container-lowest border border-border-subtle p-8 rounded-xl shadow-sm">
        @if($error)
            <h1 class="font-headline text-headline-md text-on-surface mb-4">Enlace no disponible</h1>
            <p class="text-text-muted">{{ $error }}</p>
        @else
            <div class="mb-6 text-center">
                <h1 class="font-headline text-headline-md text-on-surface">Activa tu carta</h1>
                <p class="mt-2 text-label-md text-text-muted">
                    <strong>{{ $registration->restaurant_name }}</strong><br>
                    30 días de prueba · Sin tarjeta
                </p>
            </div>

            <form method="POST" action="{{ route('pre-alta.claim.store', ['token' => $token]) }}" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="text-label-md text-on-surface-variant block mb-1">Tu nombre</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary outline-none @error('name') border-red-500 @enderror"/>
                    @error('name')<p class="mt-1 text-label-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="text-label-md text-on-surface-variant block mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                        class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary outline-none @error('email') border-red-500 @enderror"/>
                    @error('email')<p class="mt-1 text-label-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="text-label-md text-on-surface-variant block mb-1">Contraseña</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                        class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary outline-none @error('password') border-red-500 @enderror"/>
                    @error('password')<p class="mt-1 text-label-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="text-label-md text-on-surface-variant block mb-1">Confirmar contraseña</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                        class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary outline-none"/>
                </div>

                <button type="submit" class="w-full py-3 rounded-lg bg-primary text-on-primary font-semibold hover:opacity-90 transition-opacity">
                    Crear cuenta y entrar al panel
                </button>
            </form>
        @endif
    </div>
</main>
</body>
</html>
