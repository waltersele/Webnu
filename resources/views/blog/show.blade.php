@extends('blog.layout')

@push('head')
    @if(!empty($breadcrumbSchema))
        <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    @endif
    @if(!empty($faqSchema))
        <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    @endif
    @if(!empty($blogPostingSchema))
        <script type="application/ld+json">{!! json_encode($blogPostingSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    @endif
@endpush

@section('content')
    @if(!empty($preview))
        <div class="mb-6 rounded-2xl border border-amber-300 bg-amber-50 text-amber-900 px-4 py-3 font-label-md">
            Vista previa — el artículo puede no estar publicado. No indexable.
        </div>
    @endif

    @include('blog.partials.breadcrumbs')

    @if($featuredImage)
        <div class="mb-10 rounded-3xl overflow-hidden wn-blog-glass">
            <img src="{{ $featuredImage }}"
                 alt="{{ $featuredImageAlt ?? $translation->title }}"
                 class="w-full max-h-[28rem] object-cover"
                 loading="eager"
                 decoding="async">
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter items-start">
        <article class="lg:col-span-2 min-w-0">
            <header>
                @if($post->category)
                    <a href="{{ $post->category->publicUrl($locale) }}"
                       class="wn-blog-category-badge inline-flex items-center rounded-full bg-primary/10 text-primary px-4 py-1.5 font-label-md font-semibold hover:bg-primary/15 transition-colors mb-4">
                        {{ $post->category->name }}
                    </a>
                @endif

                <div class="flex flex-wrap items-center gap-3 text-label-md text-text-muted mb-4">
                    <span>{{ optional($post->published_at)->translatedFormat('d M Y') }}</span>
                    <span class="inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">schedule</span>
                        {{ __('blog.reading_time', ['min' => $readingTimeMinutes]) }}
                    </span>
                </div>

                <h1 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background leading-tight">
                    {{ $translation->title }}
                </h1>

                @if($translation->excerpt)
                    <p class="mt-4 font-body-lg text-body-lg text-text-muted">{{ $translation->excerpt }}</p>
                @endif
            </header>

            <div class="wn-blog-content mt-10 wn-blog-glass rounded-3xl p-8 md:p-10 bg-surface-container-lowest">
                {!! $translation->renderedBody() !!}
            </div>

            @include('blog.partials.faq', ['faqSchema' => $faqSchema ?? null])
        </article>

        @include('blog.partials.sidebar')
    </div>
@endsection
