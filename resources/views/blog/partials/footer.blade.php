<footer class="border-t border-slate-200 bg-white">
    <div class="mx-auto flex max-w-5xl flex-col gap-2 px-4 py-8 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
        <p>&copy; {{ date('Y') }} Webnu</p>
        <div class="flex gap-4">
            <a href="{{ route('home') }}" class="hover:text-slate-900">{{ __('blog.back_home') }}</a>
            <a href="{{ route('blog.index', ['locale' => $locale ?? config('blog.default')]) }}" class="hover:text-slate-900">{{ __('blog.title') }}</a>
        </div>
    </div>
</footer>
