@extends('tv.layout')

@section('tv_content')
@php
    $presenter = app(\App\Services\TvMenuPresenter::class);
    $slides = $highlights->filter(fn ($p) => ! empty($p->image))->values();
@endphp

<div class="wn-tv-hero" data-tv-carousel data-tv-interval="{{ $rotateSeconds ?? 10 }}">
    @forelse($slides as $index => $product)
        @php
            $imgUrl = $presenter->productImageUrl($product);
            $sectionName = optional($product->section)->name;
            $priceLabel = $presenter->formatPrice($product);
        @endphp
        <article class="wn-tv-hero__slide{{ $index === 0 ? ' is-active' : '' }}" data-tv-carousel-slide>
            <img src="{{ $imgUrl }}" alt="" class="wn-tv-hero__img" loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
            <div class="wn-tv-hero__shade" aria-hidden="true"></div>

            <div class="wn-tv-hero__topbar">
                <span class="wn-tv-hero__brand">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="" class="wn-tv-hero__brand-logo">
                    @else
                        <i class="ti ti-toggle-right-filled"></i>
                    @endif
                    <span>{{ $company->name }}</span>
                </span>
                <span class="wn-tv-hero__gallery">
                    <i class="ti ti-star-filled"></i> Galería de platos
                </span>
            </div>

            <div class="wn-tv-hero__caption">
                @if($sectionName)
                    <span class="wn-tv-hero__category">{{ Str::upper($sectionName) }}</span>
                @endif
                <h2 class="wn-tv-hero__name">{{ $product->name }}</h2>
                @if($priceLabel)
                    <p class="wn-tv-hero__price">{{ $priceLabel }}</p>
                @endif
            </div>
        </article>
    @empty
        @include('tv.partials.empty', ['message' => 'Añade fotos a tus platos destacados para mostrarlos a pantalla completa.'])
    @endforelse
    @if($slides->count() > 1)
        <div class="wn-tv-hero__dots" data-tv-carousel-dots aria-hidden="true"></div>
    @endif
</div>
@endsection

@push('tv_scripts')
<script src="{{ asset('js/webnu-tv.js') }}"></script>
<script>WebnuTv.initCarousel();</script>
@endpush
