@php
    $priceLabel = app(\App\Services\MenuPriceFormatter::class)->format($product);
    $img = $product->image ? url('img/' . ltrim($product->image, '/')) : null;
    $desc = $product->description ? \Illuminate\Support\Str::limit(strip_tags($product->description), 90) : null;
@endphp
<article class="wn-swipe-card wn-dish-card"
         data-product-id="{{ $product->id }}"
         data-target="#wnDish{{ $product->id }}"
         tabindex="0">
    <div class="wn-swipe-card__media">
        @if($img)
            <img src="{{ $img }}" alt="" class="wn-swipe-card__img" loading="lazy">
        @else
            <span class="wn-swipe-card__placeholder" aria-hidden="true">{{ mb_substr($product->name, 0, 1) }}</span>
        @endif
        @if(!empty($product->highlight))
            <div class="wn-swipe-card__badge">
                @include('themes.partials.product-highlight-badge', ['product' => $product])
            </div>
        @endif
    </div>
    <div class="wn-swipe-card__body">
        <h3 class="wn-swipe-card__title">{{ $product->name }}</h3>
        @if($desc)
            <p class="wn-swipe-card__desc">{{ $desc }}</p>
        @endif
        <div class="wn-swipe-card__allergens">
            @include('themes.partials.menu-product-allergens', ['product' => $product])
        </div>
        <div class="wn-swipe-card__footer">
            @if($priceLabel)
                <span class="wn-swipe-card__price">{{ $priceLabel }}</span>
            @else
                <span></span>
            @endif
            @include('themes.partials.menu-fav-button', ['product' => $product, 'favClass' => 'wn-fav-btn--action'])
        </div>
    </div>
</article>

@include('themes.partials.modern-product-detail-modal', ['product' => $product])
