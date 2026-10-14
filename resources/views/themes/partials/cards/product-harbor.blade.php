@php
    $priceLabel = app(\App\Services\MenuPriceFormatter::class)->format($product);
    $desc = $product->description ? \Illuminate\Support\Str::limit(strip_tags($product->description), 120) : null;
    $portion = $product->portion ?? $product->weight ?? null;
@endphp
<article class="wn-harbor-item wn-dish-card"
         data-product-id="{{ $product->id }}"
         data-target="#wnDish{{ $product->id }}"
         tabindex="0">
    <div class="wn-harbor-item__main">
        <h3 class="wn-harbor-item__title">{{ $product->name }}</h3>
        @if($desc)
            <p class="wn-harbor-item__desc">{{ $desc }}</p>
        @endif
        <div class="wn-harbor-item__meta">
            @if($portion)
                <span>{{ $portion }}</span>
                @if($priceLabel)<span class="wn-harbor-item__sep" aria-hidden="true">|</span>@endif
            @endif
            @if($priceLabel)
                <span class="wn-harbor-item__price">{{ $priceLabel }}</span>
            @endif
        </div>
        <div class="wn-harbor-item__allergens">
            @include('themes.partials.menu-product-allergens', ['product' => $product])
        </div>
    </div>
    @include('themes.partials.menu-fav-button', ['product' => $product, 'favClass' => 'wn-fav-btn--action'])
</article>

@include('themes.partials.modern-product-detail-modal', ['product' => $product])
