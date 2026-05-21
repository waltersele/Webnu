@extends('tv.layout')

@section('tv_content')
@php $presenter = app(\App\Services\TvMenuPresenter::class); @endphp
<div class="wn-tv-video" data-tv-carousel data-tv-interval="{{ $rotateSeconds ?? 15 }}" data-tv-video-mode>
    @php
        $videoSlides = $videos->filter(function ($p) use ($presenter) {
            return (bool) $presenter->productVideoUrl($p);
        });
    @endphp
    @forelse($videoSlides as $index => $product)
        @if($videoUrl = $presenter->productVideoUrl($product))
            @php $poster = $presenter->productImageUrl($product); @endphp
            <div class="wn-tv-video__slide{{ $index === 0 ? ' is-active' : '' }}" data-tv-carousel-slide data-tv-video-slide>
                <video class="wn-tv-video__player"
                       muted
                       playsinline
                       loop
                       preload="{{ $index === 0 ? 'metadata' : 'none' }}"
                       @if($poster) poster="{{ $poster }}" @endif
                       @if($index === 0) src="{{ $videoUrl }}" @else data-src="{{ $videoUrl }}" @endif></video>
                <div class="wn-tv-video__caption">
                    <h2>{{ $product->name }}</h2>
                    @if($price = $presenter->formatPrice($product))
                        <p class="wn-tv-video__price">{{ $price }}</p>
                    @endif
                </div>
            </div>
        @endif
    @empty
        @include('tv.partials.empty', ['message' => 'Añade vídeos cortos a tus platos para mostrarlos en TV.'])
    @endforelse
    @if($videoSlides->count() > 1)
        <div class="wn-tv-video__progress" data-tv-carousel-dots aria-hidden="true"></div>
    @endif
</div>
@endsection

@push('tv_scripts')
<script src="{{ asset('js/webnu-tv.js') }}"></script>
<script>WebnuTv.initCarousel({ video: true, lazyVideo: true });</script>
@endpush
