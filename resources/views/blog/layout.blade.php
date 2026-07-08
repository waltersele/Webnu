<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale ?? app()->getLocale()) }}">
<head>
    @include('blog.partials.head')
</head>
<body class="bg-background text-on-surface text-body-md min-h-screen">
    @include('blog.partials.header')
    <main class="mx-auto max-w-5xl px-4 py-10">
        @yield('content')
    </main>
    @include('blog.partials.footer')
</body>
</html>
