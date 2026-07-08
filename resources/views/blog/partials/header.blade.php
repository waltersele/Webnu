<header class="border-b border-slate-200 bg-white/90 backdrop-blur">
    <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4">
        <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold text-slate-900">
            <img src="{{ \App\PlatformSetting::brandUrl('isotipo') }}" alt="Webnu" class="h-8 w-8">
            <span>Webnu</span>
        </a>
        <nav class="flex items-center gap-3 text-sm">
            @foreach($blogLocales ?? config('blog.locales', []) as $code => $meta)
                <a href="{{ route('blog.index', ['locale' => $code]) }}"
                   class="rounded-full px-3 py-1 {{ ($locale ?? '') === $code ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                    {{ strtoupper($code) }}
                </a>
            @endforeach
            <a href="{{ route('register') }}" class="rounded-full bg-blue-700 px-4 py-2 font-semibold text-white hover:bg-blue-800">
                {{ __('blog.cta_register') }}
            </a>
        </nav>
    </div>
</header>
