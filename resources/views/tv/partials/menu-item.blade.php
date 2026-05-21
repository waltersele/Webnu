@php $presenter = $presenter ?? app(\App\Services\TvMenuPresenter::class); @endphp
<li class="wn-tv-menu__item">
    <div class="wn-tv-menu__item-main">
        <span class="wn-tv-menu__item-name">{{ $product->name }}</span>
        @if($product->description)
            <span class="wn-tv-menu__item-desc">{{ \Illuminate\Support\Str::limit($product->description, 80) }}</span>
        @endif
    </div>
    @if($price = $presenter->formatPrice($product))
        <span class="wn-tv-menu__item-price">{{ $price }}</span>
    @endif
</li>
