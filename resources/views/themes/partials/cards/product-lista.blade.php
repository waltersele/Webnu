@php
    $priceLabel = app(\App\Services\MenuPriceFormatter::class)->format($product);
    $desc = $product->description ? \Illuminate\Support\Str::limit(strip_tags($product->description), 140) : null;
@endphp
<article class="wn-lista-item wn-dish-card"
         data-product-id="{{ $product->id }}"
         data-target="#wnDish{{ $product->id }}"
         tabindex="0">
    <div class="wn-lista-item__row">
        <h3 class="wn-lista-item__title">{{ $product->name }}</h3>
        <span class="wn-lista-item__dots" aria-hidden="true"></span>
        @if($priceLabel)
            <span class="wn-lista-item__price">{{ $priceLabel }}</span>
        @endif
        @include('themes.partials.menu-fav-button', ['product' => $product, 'favClass' => 'wn-fav-btn--action'])
    </div>
    @if($desc)
        <p class="wn-lista-item__desc">{{ $desc }}</p>
    @endif
    <div class="wn-lista-item__allergens">
        @include('themes.partials.menu-product-allergens', ['product' => $product])
    </div>
</article>

@include('themes.partials.modern-product-detail-modal', ['product' => $product])
