@php
    $priceLabel = app(\App\Services\MenuPriceFormatter::class)->format($product);
    $img = $product->image ? url('img/' . ltrim($product->image, '/')) : null;
    $desc = $product->description ? \Illuminate\Support\Str::limit(strip_tags($product->description), 70) : null;
@endphp
<article class="wn-grid-card wn-dish-card"
         data-product-id="{{ $product->id }}"
         data-target="#wnDish{{ $product->id }}"
         tabindex="0">
    <span class="wn-grid-card__thumb">
        @if($img)
            <img src="{{ $img }}" alt="" loading="lazy">
        @else
            <span class="wn-grid-card__placeholder" aria-hidden="true">{{ mb_substr($product->name, 0, 1) }}</span>
        @endif
    </span>
    <span class="wn-grid-card__title">{{ $product->name }}</span>
    @if($desc)
        <span class="wn-grid-card__desc">{{ $desc }}</span>
    @endif
    <span class="wn-grid-card__footer">
        <span class="wn-grid-card__allergens">
            @include('themes.partials.menu-product-allergens', ['product' => $product])
        </span>
        <span class="wn-grid-card__footer-end">
            @if($priceLabel)
                <span class="wn-grid-card__price">{{ $priceLabel }}</span>
            @endif
            @include('themes.partials.menu-fav-button', ['product' => $product, 'favClass' => 'wn-fav-btn--action'])
        </span>
    </span>
</article>

@include('themes.partials.modern-product-detail-modal', ['product' => $product])
