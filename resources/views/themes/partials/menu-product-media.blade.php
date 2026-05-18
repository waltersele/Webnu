@php
    $imagePath = $product->display_image ?? $product->image;
    $videoPath = $product->display_video ?? $product->video;
@endphp

@if ($videoPath)
    <div class="wn-card-reel" data-product-id="{{ $product->id }}">
        <video
            class="wn-card-reel__video"
            src="{{ asset('img/' . $videoPath) }}"
            @if($imagePath) poster="{{ asset('img/' . $imagePath) }}" @endif
            autoplay
            muted
            loop
            playsinline
            preload="metadata"
            aria-label="Vídeo de {{ $product->name }}"
        ></video>
        <span class="wn-card-reel__badge" aria-hidden="true"><i class="fas fa-video"></i> Reel</span>
        <button type="button" class="wn-card-reel__open" data-toggle="modal" data-target="#wnDish{{ $product->id }}" aria-label="Ver {{ $product->name }}">
            <i class="fas fa-expand-alt"></i>
        </button>
    </div>
@elseif ($imagePath)
    <button type="button" class="wn-card-media-link" data-toggle="modal" data-target="#wnDish{{ $product->id }}" aria-label="Ver {{ $product->name }}">
        <img src="{{ asset('img/' . $imagePath) }}" alt="{{ $product->name }}" loading="lazy">
    </button>
@else
    <button type="button" class="wn-card-media-link wn-card-media-link--empty" data-toggle="modal" data-target="#wnDish{{ $product->id }}" aria-label="Ver detalle de {{ $product->name }}">
        <span class="wn-modern-card__placeholder" aria-hidden="true"><i class="fas fa-utensils"></i></span>
    </button>
@endif
