<aside class="wn-blog-sidebar space-y-6 lg:sticky lg:top-24 lg:self-start" aria-label="{{ __('blog.sidebar_nav') }}">
    @if(!empty($sidebarLatestPosts) && $sidebarLatestPosts->isNotEmpty())
        <section class="wn-blog-sidebar__widget wn-blog-glass rounded-2xl p-5 bg-surface-container-lowest">
            <h2 class="font-headline-sm text-headline-sm text-on-background mb-4">
                {{ __('blog.sidebar_latest') }}
            </h2>
            <ul class="space-y-3">
                @foreach($sidebarLatestPosts as $item)
                    <li>
                        <a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $item->slug]) }}"
                           class="block group">
                            <span class="font-label-lg font-semibold text-on-background group-hover:text-primary transition-colors line-clamp-2">
                                {{ $item->title }}
                            </span>
                            <span class="mt-1 block text-label-md text-text-muted">
                                {{ optional($item->post->published_at)->translatedFormat('d M Y') }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if(!empty($sidebarCategories) && $sidebarCategories->isNotEmpty())
        <section class="wn-blog-sidebar__widget wn-blog-glass rounded-2xl p-5 bg-surface-container-lowest">
            <h2 class="font-headline-sm text-headline-sm text-on-background mb-4">
                {{ __('blog.sidebar_categories') }}
            </h2>
            <ul class="space-y-2">
                @foreach($sidebarCategories as $cat)
                    <li>
                        <a href="{{ $cat->publicUrl($locale) }}"
                           class="flex items-center justify-between gap-3 font-label-lg text-on-background hover:text-primary transition-colors">
                            <span>{{ $cat->name }}</span>
                            <span class="text-label-md text-text-muted">{{ $cat->posts_count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if(!empty($sidebarRelatedPosts) && $sidebarRelatedPosts->isNotEmpty())
        <section class="wn-blog-sidebar__widget wn-blog-glass rounded-2xl p-5 bg-surface-container-lowest">
            <h2 class="font-headline-sm text-headline-sm text-on-background mb-4">
                {{ __('blog.sidebar_related') }}
            </h2>
            <ul class="space-y-3">
                @foreach($sidebarRelatedPosts as $item)
                    <li>
                        <a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $item->slug]) }}"
                           class="block group">
                            <span class="font-label-lg font-semibold text-on-background group-hover:text-primary transition-colors line-clamp-2">
                                {{ $item->title }}
                            </span>
                            @if($item->post->category)
                                <span class="mt-1 block text-label-md text-primary">{{ $item->post->category->name }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</aside>
