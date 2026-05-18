@if($product->price_portion || $product->price_unit)
<div class="wn-prices">
    @if($product->price_portion)
        <span class="wn-price-item">Media: {{ $product->price_portion }} €</span>
    @endif
    @if($product->price_unit)
        <span class="wn-price-item">
            @if($product->price_portion)Entera: @endif
            {{ $product->price_unit }} €
            @include('themes.partials.product-price-suffix', ['product' => $product])
        </span>
    @endif
</div>
@endif
