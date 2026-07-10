<!DOCTYPE html>
<html class="scroll-smooth" lang="{{ str_replace('_', '-', $locale ?? app()->getLocale()) }}">
<head>
    @include('landing.partials.head')
    @include('blog.partials.styles')
    @stack('head')
</head>
<body class="bg-background text-on-surface text-body-md min-h-screen overflow-x-hidden">
    @include('marketing.partials.nav')
    <main class="max-w-container-max mx-auto px-margin-mobile md:px-gutter py-10 md:py-16">
        @yield('content')
    </main>
    @include('marketing.partials.footer')
    <script src="{{ asset('js/marketing-shell.js') }}"></script>
</body>
</html>
