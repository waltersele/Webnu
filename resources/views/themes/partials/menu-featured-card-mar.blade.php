@php
    $featuredLabel = config('menu_locales.ui.' . ($menuLocale ?? 'es') . '.featured_dish', 'Plato estrella');
    $hasMenuMedia = ! empty($product->display_image ?? $product->image)
        || ! empty($product->display_video ?? $product->video);
    $showCardFooter = ! empty($favoritesEnabled) || $product->allergens->count() > 0;
@endphp

<section class="wn-menu-featured-card wn-menu-featured-card--mar" aria-label="{{ $featuredLabel }}">
    <article class="wn-menu-featured-card__inner wn-modern-card--interactive{{ $hasMenuMedia ? ' wn-menu-featured-card__inner--has-media' : '' }}"
             data-product-id="{{ $product->id }}">
        @if($hasMenuMedia)
            <div class="wn-menu-featured-card__media">
                @include('themes.partials.menu-product-media', ['product' => $product])
                <span class="wn-modern-card__detail-hint" aria-hidden="true">
                    @include('themes.partials.icons.svg-search')
                </span>
            </div>
        @endif
        <div class="wn-menu-featured-card__content">
            <span class="wn-menu-featured-card__eyebrow">{{ $featuredLabel }}</span>
            <h2 class="wn-menu-featured-card__title">{{ $product->name }}</h2>
            @if($product->description)
                <p class="wn-menu-featured-card__desc">{{ $product->description }}</p>
            @endif
            <div class="wn-menu-featured-card__foot">
                @include('themes.partials.product-prices', ['product' => $product])
            </div>
            @if($showCardFooter)
                <div class="wn-menu-featured-card__footer">
                    <div class="wn-modern-card__allergens-slot">
                        @include('themes.partials.menu-product-allergens', ['product' => $product])
                    </div>
                    @if(!empty($favoritesEnabled))
                        <div class="wn-modern-card__fav-slot">
                            <button type="button"
                                    class="wn-fav-btn"
                                    data-fav-toggle
                                    data-product-id="{{ $product->id }}"
                                    aria-pressed="false"
                                    aria-label="{{ config('menu_locales.ui.' . ($menuLocale ?? 'es') . '.favorites_add', 'Añadir a favoritos') }}">
                                @include('themes.partials.icons.svg-heart')
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </article>
</section>

@include('themes.partials.modern-product-detail-modal', ['product' => $product])
