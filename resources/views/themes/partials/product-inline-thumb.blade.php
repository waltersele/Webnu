@php
    $imagePath = $product->display_image ?? $product->image;
@endphp

@if ($product->video)
    <a href="#" class="product-video-play-link wn-card-media-link" data-toggle="modal" data-target="#wnDish{{ $product->id }}" title="Ver plato">
        @if ($imagePath)
            <img class="img-responsive" src="{{ asset('img/' . $imagePath) }}" alt="{{ $product->name }}">
            <span class="wn-card-media-play">@include('themes.partials.icons.svg-play')</span>
        @else
            <span class="wn-modern-card__placeholder wn-modern-card__placeholder--featured">@include('themes.partials.icons.svg-play')</span>
        @endif
    </a>
@elseif ($imagePath)
    <img class="img-responsive" src="{{ asset('img/' . $imagePath) }}" alt="{{ $product->name }}">
@endif
