@php
    $priceLabel = $product->price_unit
        ? ($product->price_unit . ' �')
        : ($product->price_portion ? ($product->price_portion . ' �') : null);
    $img = $product->image ? url('img/' . ltrim($product->image, '/')) : null;
    $desc = $product->description ? \Illuminate\Support\Str::limit(strip_tags($product->description), 70) : null;
@endphp
<article class="wn-grid-card">
    <button type="button"
            class="wn-grid-card__hit"
            data-toggle="modal"
            data-target="#wnDish{{ $product->id }}"
            aria-label="Ver detalle de {{ $product->name }}">
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
            @if($priceLabel)
                <span class="wn-grid-card__price">{{ $priceLabel }}</span>
            @endif
        </span>
    </button>
</article>

@include('themes.partials.modern-product-detail-modal', ['product' => $product])
