<article class="wn-card-temporada wn-modern-card--interactive">
    <div class="wn-card-temporada__media">
        @include('themes.partials.menu-product-media', ['product' => $product])
        @if(!empty($product->highlight))
            <div class="wn-card-temporada__badge">
                @include('themes.partials.product-highlight-badge', ['product' => $product])
            </div>
        @endif
    </div>
    <div class="wn-card-temporada__body">
        <h3 class="wn-card-temporada__title">{{ $product->name }}</h3>
        @if($product->description)
            <p class="wn-card-temporada__desc">{{ $product->description }}</p>
        @endif
        @include('themes.partials.product-prices', ['product' => $product])
        @if($product->allergens->count())
            <div class="wn-card-temporada__tags">
                @foreach ($product->allergens as $allergen)
                    <span class="wn-diet-tag">{{ $allergen->name }}</span>
                @endforeach
            </div>
        @endif
    </div>
    <button type="button" class="wn-card-temporada__detail" data-toggle="modal" data-target="#wnDish{{ $product->id }}" aria-label="Ver detalle">
        <i class="fas fa-chevron-right" aria-hidden="true"></i>
    </button>
</article>

@include('themes.partials.modern-product-detail-modal', ['product' => $product])
