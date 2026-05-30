@extends('tv.layout')

@section('tv_content')
@php
    $presenter = app(\App\Services\TvMenuPresenter::class);
    $slides = $featured->filter(fn ($p) => ! empty(trim((string) $p->description)))->values();
    if ($slides->isEmpty()) {
        $slides = $featured->values();
    }
@endphp

<div class="wn-tv-signature" data-tv-carousel data-tv-interval="{{ $rotateSeconds ?? 12 }}">
    @forelse($slides as $index => $product)
        @php
            $imgUrl = $presenter->productImageUrl($product);
            $priceLabel = $presenter->formatPrice($product);
            $highlightMeta = $product->highlightMeta();
            $badgeLabel = $highlightMeta['label'] ?? ($product->highlight ? 'Sugerencia del chef' : null);
        @endphp
        <article class="wn-tv-signature__slide{{ $index === 0 ? ' is-active' : '' }}" data-tv-carousel-slide>
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="" class="wn-tv-signature__watermark" aria-hidden="true">
            @endif
            <div class="wn-tv-signature__media">
                @if($imgUrl)
                    <img src="{{ $imgUrl }}" alt="" class="wn-tv-signature__img" loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                @else
                    <div class="wn-tv-signature__placeholder" aria-hidden="true">★</div>
                @endif
            </div>
            <div class="wn-tv-signature__copy">
                @if($badgeLabel)
                    <span class="wn-tv-signature__badge">{{ $badgeLabel }}</span>
                @endif
                <h2 class="wn-tv-signature__name">{{ $product->name }}</h2>
                @if($product->description)
                    <p class="wn-tv-signature__desc">{{ \Illuminate\Support\Str::limit($product->description, 160) }}</p>
                @endif
                @if($priceLabel)
                    <p class="wn-tv-signature__price">{{ $priceLabel }}</p>
                @endif
            </div>
        </article>
    @empty
        @include('tv.partials.empty', ['message' => 'Marca platos como destacados con descripción para Firma del chef.'])
    @endforelse
    @if($slides->count() > 1)
        <div class="wn-tv-signature__dots" data-tv-carousel-dots aria-hidden="true"></div>
    @endif
</div>
@endsection

@push('tv_scripts')
<script src="{{ asset('js/webnu-tv.js') }}"></script>
<script>WebnuTv.initCarousel();</script>
@endpush
