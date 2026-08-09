<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>@yield('title', 'Legal') — Webnu</title>
    <link rel="icon" type="image/png" href="{{ \App\PlatformSetting::brandUrl('favicon') }}"/>
    <meta name="theme-color" content="#004ac6">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <style>
        body { font-family: Inter, system-ui, sans-serif; }
    </style>
</head>
<body class="bg-[#f9f9ff] text-[#141B2B]">
    <header class="border-b border-gray-200 bg-white">
        <div class="max-w-3xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ url('/') }}" class="font-semibold text-[#004ac6]">Webnu</a>
            <a href="{{ url('/') }}" class="text-sm text-gray-600 hover:text-[#004ac6]">← Volver al inicio</a>
        </div>
    </header>
    <main class="max-w-3xl mx-auto px-4 py-10 prose prose-slate">
        @yield('content')
    </main>
    <footer class="border-t border-gray-200 mt-12 py-6 text-center text-sm text-gray-500">
        © {{ date('Y') }} Webnu.es —
        <a href="{{ route('legal.privacy') }}" class="hover:text-[#004ac6]">Privacidad</a> ·
        <a href="{{ route('legal.terms') }}" class="hover:text-[#004ac6]">Términos</a> ·
        <a href="#" data-manage-cookies class="hover:text-[#004ac6]">Gestionar cookies</a>
    </footer>
    @include('partials.measurement-head')
</body>
</html>
