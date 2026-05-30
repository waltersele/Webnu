@php
    $presenter = $presenter ?? app(\App\Services\TvMenuPresenter::class);
    $compact = $compact ?? false;
    $rootClass = trim('wn-tv-video-zone' . ($compact ? ' wn-tv-video-zone--compact' : '') . ' ' . ($rootClass ?? ''));
    $interval = $interval ?? ($rotateSeconds ?? 15);
    $videoSlides = ($videos ?? collect())->filter(function ($p) use ($presenter) {
        return (bool) $presenter->productVideoUrl($p);
    });
@endphp
<div class="{{ $rootClass }} wn-tv-video" data-tv-carousel data-tv-interval="{{ $interval }}" data-tv-video-mode>
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
                @if(($showCaption ?? true) && ! $compact)
                    <div class="wn-tv-video__caption">
                        <h2>{{ $product->name }}</h2>
                        @if($price = $presenter->formatPrice($product))
                            <p class="wn-tv-video__price">{{ $price }}</p>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    @empty
        <div class="wn-tv-video-zone__empty">
            <p>{{ $emptyMessage ?? 'Añade vídeos cortos a tus platos para mostrarlos aquí.' }}</p>
        </div>
    @endforelse
    @if($videoSlides->count() > 1)
        <div class="wn-tv-video__progress" data-tv-carousel-dots aria-hidden="true"></div>
    @endif
</div>
