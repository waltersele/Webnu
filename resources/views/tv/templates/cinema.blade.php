@extends('tv.layout')

@section('tv_content')
@php
    $presenter = app(\App\Services\TvMenuPresenter::class);
    $slides = $featured->filter(fn ($p) => $presenter->productImageUrl($p))->values();
    if ($slides->isEmpty()) {
        $slides = $highlights->filter(fn ($p) => $presenter->productImageUrl($p))->values();
    }
@endphp

<div class="wn-tv-cinema" data-tv-carousel data-tv-interval="{{ $rotateSeconds ?? 14 }}">
    @forelse($slides as $index => $product)
        @php
            $imgUrl = $presenter->productImageUrl($product);
            $sectionName = optional($product->section)->name;
            $priceLabel = $presenter->formatPrice($product);
        @endphp
        <article class="wn-tv-cinema__slide{{ $index === 0 ? ' is-active' : '' }}" data-tv-carousel-slide>
            <img src="{{ $imgUrl }}" alt="" class="wn-tv-cinema__img" loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
            <div class="wn-tv-cinema__vignette" aria-hidden="true"></div>
            <div class="wn-tv-cinema__caption">
                @if($sectionName)
                    <span class="wn-tv-cinema__category">{{ Str::upper($sectionName) }}</span>
                @endif
                <h2 class="wn-tv-cinema__name">{{ $product->name }}</h2>
            </div>
            @if($priceLabel)
                <p class="wn-tv-cinema__price">{{ $priceLabel }}</p>
            @endif
        </article>
    @empty
        @include('tv.partials.empty', ['message' => 'Añade fotos a tus platos destacados para la plantilla Cinema.'])
    @endforelse
    @if($slides->count() > 1)
        <div class="wn-tv-cinema__dots" data-tv-carousel-dots aria-hidden="true"></div>
    @endif
</div>
@endsection

@push('tv_scripts')
<script src="{{ asset('js/webnu-tv.js') }}"></script>
<script>WebnuTv.initCarousel();</script>
@endpush
