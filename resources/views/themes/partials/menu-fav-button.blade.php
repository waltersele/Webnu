@if(!empty($favoritesEnabled) && !empty($product))
    @php
        $favLocale = $menuLocale ?? 'es';
        $favLabel = isset($menuLocaleService)
            ? $menuLocaleService->uiLabel('favorites_add', $favLocale)
            : config('menu_locales.ui.' . $favLocale . '.favorites_add', 'Añadir a favoritos');
        $favClass = trim('wn-fav-btn ' . ($favClass ?? ''));
    @endphp
    <button type="button"
            class="{{ $favClass }}"
            data-fav-toggle
            data-product-id="{{ $product->id }}"
            aria-pressed="false"
            aria-label="{{ $favLabel }}">
        @include('themes.partials.icons.svg-heart')
    </button>
@endif
