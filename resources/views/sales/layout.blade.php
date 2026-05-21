<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'Comercial') — Webnu</title>
    <link rel="stylesheet" href="{{ asset('css/webnu-sales.css') }}">
    @stack('styles')
</head>
<body class="wn-sales-body">
    @if (! trim($__env->yieldContent('hide_header')))
    <header class="wn-sales-header">
        <a href="{{ route('sales.dashboard') }}" class="wn-sales-brand">Webnu Comercial</a>
        @auth
            <form method="POST" action="{{ route('sales.logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-link">Salir</button>
            </form>
        @endauth
    </header>
    @endif

    <main class="wn-sales-main">
        @if (session('flash'))
            <div class="wn-sales-flash">{{ session('flash') }}</div>
        @endif
        @if ($errors->any())
            <div class="wn-sales-error">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
