@php
    $priceLabel = $product->price_unit
        ? ($product->price_unit . ' �')
        : ($product->price_portion ? ($product->price_portion . ' �') : null);
    $img = $product->image ? url('img/' . ltrim($product->image, '/')) : null;
    $desc = $product->description ? \Illuminate\Support\Str::limit(strip_tags($product->description), 100) : null;
@endphp
<article class="wn-rail-item">
    <button type="button"
            class="wn-rail-item__hit"
            data-toggle="modal"
            data-target="#wnDish{{ $product->id }}"
            aria-label="Ver detalle de {{ $product->name }}">
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
            </span>
            @if($desc)
                <span class="wn-rail-item__desc">{{ $desc }}</span>
            @endif
            <span class="wn-rail-item__allergens">
                @include('themes.partials.menu-product-allergens', ['product' => $product])
            </span>
        </span>
    </button>
</article>

@include('themes.partials.modern-product-detail-modal', ['product' => $product])
