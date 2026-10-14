@php
    $priceLabel = app(\App\Services\MenuPriceFormatter::class)->format($product);
    $desc = $product->description ? \Illuminate\Support\Str::limit(strip_tags($product->description), 120) : null;
    $portion = $product->portion ?? $product->weight ?? null;
@endphp
<article class="wn-harbor-item">
    <button type="button"
            class="wn-harbor-item__hit"
            data-toggle="modal"
            data-target="#wnDish{{ $product->id }}"
            aria-label="Ver detalle de {{ $product->name }}">
        <span class="wn-harbor-item__title">{{ $product->name }}</span>
        @if($desc)
            <span class="wn-harbor-item__desc">{{ $desc }}</span>
        @endif
        <span class="wn-harbor-item__meta">
            @if($portion)
                <span>{{ $portion }}</span>
                @if($priceLabel)<span class="wn-harbor-item__sep" aria-hidden="true">|</span>@endif
            @endif
            @if($priceLabel)
                <span class="wn-harbor-item__price">{{ $priceLabel }}</span>
            @endif
        </span>
        <span class="wn-harbor-item__allergens">
            @include('themes.partials.menu-product-allergens', ['product' => $product])
        </span>
    </button>
</article>

@include('themes.partials.modern-product-detail-modal', ['product' => $product])
