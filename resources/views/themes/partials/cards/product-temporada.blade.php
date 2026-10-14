<article class="wn-card-temporada wn-dish-card"
         data-product-id="{{ $product->id }}"
         data-target="#wnDish{{ $product->id }}"
         tabindex="0">
    <div class="wn-card-temporada__media">
        @include('themes.partials.menu-product-media', ['product' => $product])
        @if(!empty($product->highlight))
            <div class="wn-card-temporada__badge">
                @include('themes.partials.product-highlight-badge', ['product' => $product])
            </div>
        @endif
    </div>
    <div class="wn-card-temporada__body">
        <div class="wn-card-temporada__head">
            <h3 class="wn-card-temporada__title">{{ $product->name }}</h3>
            @include('themes.partials.menu-fav-button', ['product' => $product, 'favClass' => 'wn-fav-btn--action'])
        </div>
        @if($product->description)
            <p class="wn-card-temporada__desc">{{ $product->description }}</p>
        @endif
        @include('themes.partials.product-prices', ['product' => $product])
        <div class="wn-card-temporada__tags">
            @include('themes.partials.menu-product-allergens', ['product' => $product])
        </div>
    </div>
</article>

@include('themes.partials.modern-product-detail-modal', ['product' => $product])
