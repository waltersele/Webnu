@extends('tv.layout')

@section('tv_content')
@php
    $presenter = app(\App\Services\TvMenuPresenter::class);
    $heroSlides = $featured->filter(fn ($p) => $presenter->productImageUrl($p))->values();
    if ($heroSlides->isEmpty()) {
        $heroSlides = $highlights->filter(fn ($p) => $presenter->productImageUrl($p))->values();
    }
    $tickerItems = $highlights->map(function ($product) use ($presenter) {
        $price = $presenter->formatPrice($product);

        return trim($product->name . ($price ? ' · ' . $price : ''));
    })->filter()->values();
    if ($tickerItems->isEmpty()) {
        $tickerItems = $featured->map(function ($product) use ($presenter) {
            $price = $presenter->formatPrice($product);

            return trim($product->name . ($price ? ' · ' . $price : ''));
        })->filter()->values();
    }
@endphp

@if($heroSlides->isEmpty() && $tickerItems->isEmpty())
    @include('tv.partials.empty', ['message' => 'Añade platos con foto para el hero y destacados para el ticker Marquee.'])
@else
    <div class="wn-tv-marquee">
        <div class="wn-tv-marquee__hero" data-tv-carousel data-tv-interval="{{ $rotateSeconds ?? 8 }}">
            @forelse($heroSlides as $index => $product)
                @php
                    $imgUrl = $presenter->productImageUrl($product);
                    $priceLabel = $presenter->formatPrice($product);
                @endphp
                <article class="wn-tv-marquee__hero-slide{{ $index === 0 ? ' is-active' : '' }}" data-tv-carousel-slide>
                    <img src="{{ $imgUrl }}" alt="" class="wn-tv-marquee__hero-img" loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                    <div class="wn-tv-marquee__hero-shade" aria-hidden="true"></div>
                    <div class="wn-tv-marquee__hero-caption">
                        <h2 class="wn-tv-marquee__hero-name">{{ $product->name }}</h2>
                        @if($priceLabel)
                            <p class="wn-tv-marquee__hero-price">{{ $priceLabel }}</p>
                        @endif
                    </div>
                </article>
            @empty
                <div class="wn-tv-marquee__hero-fallback" aria-hidden="true"></div>
            @endforelse
            @if($heroSlides->count() > 1)
                <div class="wn-tv-marquee__dots" data-tv-carousel-dots aria-hidden="true"></div>
            @endif
        </div>
        @if($tickerItems->isNotEmpty())
            @php $tickerLine = $tickerItems->implode('  ·  '); @endphp
            <div class="wn-tv-marquee__ticker" aria-hidden="true">
                <div class="wn-tv-marquee__ticker-track">
                    <span class="wn-tv-marquee__ticker-text">{{ $tickerLine }}</span>
                    <span class="wn-tv-marquee__ticker-text">{{ $tickerLine }}</span>
                </div>
            </div>
        @endif
    </div>
@endif
@endsection

@push('tv_scripts')
<script src="{{ asset('js/webnu-tv.js') }}"></script>
<script>WebnuTv.initCarousel();</script>
@endpush
