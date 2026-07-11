@extends('blog.layout')

@push('head')
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

    <article>
        <a href="{{ route('blog.index', ['locale' => $locale]) }}"
           class="inline-flex items-center gap-1 text-primary font-label-lg font-semibold hover:opacity-80 transition-opacity">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            {{ __('blog.back_to_list') }}
        </a>

        @if($featuredImage)
            <div class="mt-8 mb-10 rounded-3xl overflow-hidden wn-blog-glass">
                <img src="{{ $featuredImage }}"
                     alt="{{ $featuredImageAlt ?? $translation->title }}"
                     class="w-full max-h-[28rem] object-cover"
                     loading="eager"
                     decoding="async">
            </div>
        @endif

        <header class="max-w-3xl">
            <div class="flex flex-wrap items-center gap-3 text-label-md text-text-muted mb-4">
                @if($post->category)
                    <a href="{{ $post->category->publicUrl($locale) }}"
                       class="text-primary font-semibold hover:opacity-80 transition-opacity">
                        {{ $post->category->name }}
                    </a>
                @endif
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

        <div class="wn-blog-content mt-10 max-w-3xl wn-blog-glass rounded-3xl p-8 md:p-10 bg-surface-container-lowest">
            {!! $translation->renderedBody() !!}
        </div>

        @include('blog.partials.faq', ['faqSchema' => $faqSchema ?? null])
    </article>
@endsection
