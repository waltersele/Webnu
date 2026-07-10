@extends('blog.layout')

@section('content')
    <header class="max-w-3xl mb-10 md:mb-14">
        <h1 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-4 leading-tight">
            {{ __('blog.heading') }}
        </h1>
        <p class="font-body-lg text-body-lg text-text-muted leading-relaxed">
            {{ __('blog.intro') }}
        </p>
    </header>

    @if($posts->isEmpty())
        <div class="wn-blog-glass rounded-3xl p-10 text-center text-text-muted">
            {{ __('blog.empty') }}
        </div>
    @else
        @php
            $featured = $posts->first();
            $gridPosts = $posts->slice(1);
        @endphp

        @if($featured)
            <section class="mb-16 md:mb-20">
                <article class="group wn-blog-card wn-blog-glass rounded-3xl overflow-hidden transition-transform duration-300 hover:-translate-y-1">
                    <div class="flex flex-col lg:flex-row items-stretch">
                        <a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $featured->slug]) }}"
                           class="lg:w-3/5 h-64 lg:h-auto min-h-[16rem] overflow-hidden block">
                            <img class="wn-blog-card__image w-full h-full object-cover"
                                 src="{{ $blogFeaturedImage($featured->post, 0) }}"
                                 alt="{{ $featured->title }}"
                                 loading="eager"
                                 decoding="async">
                        </a>
                        <div class="lg:w-2/5 p-8 md:p-10 flex flex-col justify-center bg-surface-container-lowest">
                            <div class="flex flex-wrap items-center gap-3 mb-5 text-label-md text-text-muted">
                                <span>{{ optional($featured->post->published_at)->translatedFormat('d M Y') }}</span>
                                <span class="inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">schedule</span>
                                    {{ __('blog.reading_time', ['min' => $blogReadingTime($featured->body)]) }}
                                </span>
                            </div>
                            <h2 class="font-headline-md text-headline-md text-on-background mb-4 leading-snug group-hover:text-primary transition-colors">
                                <a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $featured->slug]) }}">
                                    {{ $featured->title }}
                                </a>
                            </h2>
                            @if($featured->excerpt)
                                <p class="font-body-md text-body-md text-text-muted mb-8 line-clamp-3">{{ $featured->excerpt }}</p>
                            @endif
                            <a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $featured->slug]) }}"
                               class="inline-flex items-center gap-2 text-primary font-label-lg font-bold group/link">
                                {{ __('blog.read_more') }}
                                <span class="material-symbols-outlined transition-transform group-hover/link:translate-x-1">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </article>
            </section>
        @endif

        @if($gridPosts->isNotEmpty())
            <section class="mb-12">
                <h3 class="font-headline-md text-headline-md text-on-background mb-8">{{ __('blog.latest_posts') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter">
                    @foreach($gridPosts as $index => $post)
                        <article class="group wn-blog-card wn-blog-glass rounded-2xl overflow-hidden flex flex-col transition-transform duration-300 hover:-translate-y-1">
                            <a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $post->slug]) }}"
                               class="h-48 overflow-hidden block">
                                <img class="wn-blog-card__image w-full h-full object-cover"
                                     src="{{ $blogFeaturedImage($post->post, $index + 1) }}"
                                     alt="{{ $post->title }}"
                                     loading="lazy"
                                     decoding="async">
                            </a>
                            <div class="p-6 flex flex-col flex-grow bg-surface-container-lowest">
                                <h4 class="font-headline-sm text-headline-sm text-on-background mb-3 group-hover:text-primary transition-colors">
                                    <a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $post->slug]) }}">
                                        {{ $post->title }}
                                    </a>
                                </h4>
                                @if($post->excerpt)
                                    <p class="font-body-sm text-body-sm text-text-muted mb-6 flex-grow line-clamp-3">{{ $post->excerpt }}</p>
                                @endif
                                <div class="pt-4 border-t border-outline-variant flex justify-between items-center text-text-muted font-label-md">
                                    <span>{{ optional($post->post->published_at)->translatedFormat('d M Y') }}</span>
                                    <span class="inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">schedule</span>
                                        {{ __('blog.reading_time', ['min' => $blogReadingTime($post->body)]) }}
                                    </span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if($posts->hasPages())
            <div class="mt-10 flex justify-center">
                {{ $posts->links() }}
            </div>
        @endif
    @endif
@endsection
