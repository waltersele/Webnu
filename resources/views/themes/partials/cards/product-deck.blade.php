@php
    $priceLabel = app(\App\Services\MenuPriceFormatter::class)->format($product);
    $img = $product->image ? url('img/' . ltrim($product->image, '/')) : null;
    $desc = $product->description ? \Illuminate\Support\Str::limit(strip_tags($product->description), 110) : null;
    $isFeatured = in_array($product->highlight ?? '', ['featured', 'bestseller'], true);
    $featuredLabel = isset($menuLocaleService)
        ? $menuLocaleService->uiLabel('featured', $menuLocale ?? 'es')
        : 'Destacado';
@endphp
<article class="wn-deck-card">
    <div class="wn-deck-card__media">
        @if($img)
            <img src="{{ $img }}" alt="" class="wn-deck-card__img" loading="lazy">
        @else
            <span class="wn-deck-card__placeholder" aria-hidden="true">{{ mb_substr($product->name, 0, 1) }}</span>
        @endif
        @if($isFeatured)
            <span class="wn-deck-card__badge">{{ $featuredLabel }}</span>
        @endif
    </div>
    <div class="wn-deck-card__body">
        <h3 class="wn-deck-card__title">{{ $product->name }}</h3>
        @if($desc)
            <p class="wn-deck-card__desc">{{ $desc }}</p>
        @endif
        <div class="wn-deck-card__allergens">
            @include('themes.partials.menu-product-allergens', ['product' => $product])
        </div>
        <div class="wn-deck-card__footer">
            @if($priceLabel)
                <span class="wn-deck-card__price">{{ $priceLabel }}</span>
            @else
                <span></span>
            @endif
            <button type="button"
                    class="wn-deck-card__cta"
                    data-toggle="modal"
                    data-target="#wnDish{{ $product->id }}"
                    aria-label="Ver detalle de {{ $product->name }}">
                <span aria-hidden="true">+</span>
            </button>
        </div>
    </div>
</article>

@include('themes.partials.modern-product-detail-modal', ['product' => $product])
