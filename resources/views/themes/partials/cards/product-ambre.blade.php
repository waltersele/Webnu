@php
    $priceLabel = app(\App\Services\MenuPriceFormatter::class)->format($product);
    $img = $product->image ? url('img/' . ltrim($product->image, '/')) : null;
    $isFeatured = in_array($product->highlight ?? '', ['featured', 'bestseller'], true);
@endphp
<article class="wn-ambre-card{{ $isFeatured ? ' is-featured' : '' }} wn-dish-card"
         data-product-id="{{ $product->id }}"
         data-target="#wnDish{{ $product->id }}"
         tabindex="0">
    <div class="wn-ambre-card__media">
        @if($img)
            <img src="{{ $img }}" alt="" class="wn-ambre-card__img" loading="lazy">
        @else
            <span class="wn-ambre-card__placeholder" aria-hidden="true">{{ mb_substr($product->name, 0, 1) }}</span>
        @endif
        @if(!empty($product->highlight))
            <div class="wn-ambre-card__badge">
                @include('themes.partials.product-highlight-badge', ['product' => $product])
            </div>
        @endif
    </div>
    <div class="wn-ambre-card__meta">
        <div class="wn-ambre-card__meta-text">
            <h3 class="wn-ambre-card__title">{{ $product->name }}</h3>
            @if($priceLabel)
                <p class="wn-ambre-card__price">{{ $priceLabel }}</p>
            @endif
        </div>
        @include('themes.partials.menu-fav-button', ['product' => $product, 'favClass' => 'wn-fav-btn--action'])
    </div>
    <div class="wn-ambre-card__allergens">
        @include('themes.partials.menu-product-allergens', ['product' => $product])
    </div>
</article>

@include('themes.partials.modern-product-detail-modal', ['product' => $product])
