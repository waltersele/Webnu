@php
    $priceLabel = app(\App\Services\MenuPriceFormatter::class)->format($product);
    $img = $product->image ? url('img/' . ltrim($product->image, '/')) : null;
    $desc = $product->description ? \Illuminate\Support\Str::limit(strip_tags($product->description), 100) : null;
@endphp
<article class="wn-rail-item wn-dish-card"
         data-product-id="{{ $product->id }}"
         data-target="#wnDish{{ $product->id }}"
         tabindex="0">
    <span class="wn-rail-item__thumb">
        @if($img)
            <img src="{{ $img }}" alt="" loading="lazy">
        @else
            <span class="wn-rail-item__placeholder" aria-hidden="true">{{ mb_substr($product->name, 0, 1) }}</span>
        @endif
    </span>
    <span class="wn-rail-item__body">
        <span class="wn-rail-item__row">
            <span class="wn-rail-item__title">{{ $product->name }}</span>
            @if($priceLabel)
                <span class="wn-rail-item__price">{{ $priceLabel }}</span>
            @endif
            @include('themes.partials.menu-fav-button', ['product' => $product, 'favClass' => 'wn-fav-btn--action'])
        </span>
        @if($desc)
            <span class="wn-rail-item__desc">{{ $desc }}</span>
        @endif
        <span class="wn-rail-item__allergens">
            @include('themes.partials.menu-product-allergens', ['product' => $product])
        </span>
    </span>
</article>

@include('themes.partials.modern-product-detail-modal', ['product' => $product])
