@php
    $hasMenuMedia = ! empty($product->display_image ?? $product->image)
        || ! empty($product->display_video ?? $product->video);
@endphp

<article class="wn-card-overlay wn-modern-card--interactive{{ $hasMenuMedia ? '' : ' wn-card-overlay--text-only' }}" data-product-id="{{ $product->id }}">
    @if($hasMenuMedia)
        <div class="wn-card-overlay__media">
            @include('themes.partials.menu-product-media', ['product' => $product])
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
                @include('themes.partials.menu-product-allergens', ['product' => $product])
            </div>
        </div>
        <button type="button" class="wn-card-overlay__open" data-toggle="modal" data-target="#wnDish{{ $product->id }}" aria-label="Ver detalle">
            @include('themes.partials.icons.svg-expand')
        </button>
    @else
        <div class="wn-card-overlay__body">
            <div class="wn-card-overlay__head">
                <div class="wn-card-overlay__head-text">
                    @if(!empty($product->highlight))
                        <div class="wn-card-overlay__badge wn-card-overlay__badge--inline">
                            @include('themes.partials.product-highlight-badge', ['product' => $product])
                        </div>
                    @endif
                    <h3 class="wn-card-overlay__title">{{ $product->name }}</h3>
                </div>
                <button type="button" class="wn-card-overlay__open wn-card-overlay__open--inline" data-toggle="modal" data-target="#wnDish{{ $product->id }}" aria-label="Ver detalle">
                    @include('themes.partials.icons.svg-chevron-right')
                </button>
            </div>
            @if($product->description)
                <p class="wn-card-overlay__desc">{{ $product->description }}</p>
            @endif
            <div class="wn-card-overlay__prices">
                @include('themes.partials.product-prices', ['product' => $product])
            </div>
            @include('themes.partials.menu-product-allergens', ['product' => $product])
        </div>
    @endif
</article>

@include('themes.partials.modern-product-detail-modal', ['product' => $product])
