@extends('blog.layout')

@push('head')
    @if(!empty($collectionSchema))
        <script type="application/ld+json">{!! json_encode($collectionSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    @endif
@endpush

@section('content')
    <header class="max-w-3xl mb-10 md:mb-14">
        <a href="{{ route('blog.index', ['locale' => $locale]) }}"
           class="inline-flex items-center gap-1 text-primary font-label-lg font-semibold hover:opacity-80 transition-opacity mb-4">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            {{ __('blog.back_to_list') }}
        </a>
        <h1 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-4 leading-tight">
            {{ $category->name }}
        </h1>
        <p class="font-body-lg text-body-lg text-text-muted leading-relaxed">
            {{ __('blog.category_description', ['name' => $category->name]) }}
        </p>
    </header>

    @if($posts->isEmpty())
        <div class="wn-blog-glass rounded-3xl p-10 text-center text-text-muted">
            {{ __('blog.empty') }}
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter mb-12">
            @foreach($posts as $index => $post)
                <article class="group wn-blog-card wn-blog-glass rounded-2xl overflow-hidden flex flex-col transition-transform duration-300 hover:-translate-y-1">
                    <a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $post->slug]) }}"
                       class="h-48 overflow-hidden block">
                        <img class="wn-blog-card__image w-full h-full object-cover"
                             src="{{ $blogFeaturedImage($post->post, $index) }}"
                             alt="{{ $blogFeaturedImageAlt($post->post, $post) }}"
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
                        <div class="pt-4 border-t border-outline-variant text-text-muted font-label-md">
                            {{ optional($post->post->published_at)->translatedFormat('d M Y') }}
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @if($posts->hasPages())
            <div class="mt-10 flex justify-center">
                {{ $posts->links() }}
            </div>
        @endif
    @endif
@endsection
