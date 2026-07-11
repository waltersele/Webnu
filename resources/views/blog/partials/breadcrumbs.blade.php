<nav class="wn-blog-breadcrumbs mb-6" aria-label="{{ __('blog.breadcrumb_blog') }}">
    <ol class="flex flex-wrap items-center gap-2 font-label-md text-text-muted">
        <li>
            <a href="{{ route('blog.index', ['locale' => $locale]) }}"
               class="text-primary font-semibold hover:opacity-80 transition-opacity">
                {{ __('blog.breadcrumb_blog') }}
            </a>
        </li>
        @if($post->category)
            <li class="wn-blog-breadcrumbs__sep" aria-hidden="true">/</li>
            <li>
                <a href="{{ $post->category->publicUrl($locale) }}"
                   class="text-primary font-semibold hover:opacity-80 transition-opacity">
                    {{ $post->category->name }}
                </a>
            </li>
        @endif
        <li class="wn-blog-breadcrumbs__sep" aria-hidden="true">/</li>
        <li class="text-on-background font-medium truncate max-w-[16rem] sm:max-w-md" aria-current="page">
            {{ $translation->title }}
        </li>
    </ol>
</nav>
