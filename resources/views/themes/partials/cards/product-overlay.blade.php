@php
    $mediaUrl = null;
    if (!empty($product->display_image ?? $product->image)) {
        $mediaUrl = asset('img/' . ($product->display_image ?? $product->image));
    } elseif (!empty($product->display_video ?? $product->video)) {
        $mediaUrl = asset('img/' . ($product->display_video ?? $product->video));
    }
@endphp

<article class="wn-card-overlay wn-modern-card--interactive" data-product-id="{{ $product->id }}">
    <div class="wn-card-overlay__media">
        @if($mediaUrl)
            @include('themes.partials.menu-product-media', ['product' => $product])
        @else
            <div class="wn-card-overlay__placeholder"><i class="fas fa-cocktail" aria-hidden="true"></i></div>
        @endif
        <div class="wn-card-overlay__shade"></div>
        <div class="wn-card-overlay__content">
            @if(!empty($product->highlight))
                <div class="wn-card-overlay__badge">
                    @include('themes.partials.product-highlight-badge', ['product' => $product])
                </div>
            @endif
            <h3 class="wn-card-overlay__title">{{ $product->name }}</h3>
            @if($product->description)
                <p class="wn-card-overlay__desc">{{ $product->description }}</p>
            @endif
            <div class="wn-card-overlay__prices">
                @include('themes.partials.product-prices', ['product' => $product])
            </div>
        </div>
    </div>
    <button type="button" class="wn-card-overlay__open" data-toggle="modal" data-target="#wnDish{{ $product->id }}" aria-label="Ver detalle">
        <i class="fas fa-expand-alt" aria-hidden="true"></i>
    </button>
</article>

@include('themes.partials.modern-product-detail-modal', ['product' => $product])
