@php
    $layout = $layout ?? 'horizontal';
    $hasMenuMedia = ! empty($product->display_image ?? $product->image)
        || ! empty($product->display_video ?? $product->video);
    $cardClass = 'wn-modern-card wn-modern-card--' . $layout . ' wn-modern-card--interactive';
    if (! $hasMenuMedia) {
        $cardClass .= ' wn-modern-card--no-media';
    }
@endphp

<article class="{{ $cardClass }}" data-product-id="{{ $product->id }}">
    @if($hasMenuMedia)
        <div class="wn-modern-card__media">
            @include('themes.partials.menu-product-media', ['product' => $product])
            @if (!empty($product->highlight))
                <div class="wn-modern-card__badges">
                    @include('themes.partials.product-highlight-badge', ['product' => $product])
                </div>
            @endif
        </div>
    @endif
    <div class="wn-modern-card__body">
        <div class="wn-modern-card__head">
            <h3 class="wn-modern-card__title">
                {{ $product->name }}
                @if(empty($product->highlight))
                    @include('themes.partials.product-highlight-badge', ['product' => $product])
                @endif
            </h3>
            <div class="wn-modern-card__actions">
                @if(!empty($favoritesEnabled))
                    <button type="button"
                            class="wn-fav-btn"
                            data-fav-toggle
                            data-product-id="{{ $product->id }}"
                            aria-pressed="false"
                            aria-label="{{ config('menu_locales.ui.' . ($menuLocale ?? 'es') . '.favorites_add', 'Añadir a favoritos') }}">
                        <i class="far fa-heart" aria-hidden="true"></i>
                    </button>
                @endif
                <button type="button" class="wn-modern-card__detail-btn" data-toggle="modal" data-target="#wnDish{{ $product->id }}" aria-label="Ver detalle">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        @if($product->description)
            <p class="wn-modern-card__desc">{{ $product->description }}</p>
        @endif
        @include('themes.partials.product-prices', ['product' => $product])
        @include('themes.partials.menu-product-allergens', ['product' => $product])
    </div>
</article>

@include('themes.partials.modern-product-detail-modal', ['product' => $product])
