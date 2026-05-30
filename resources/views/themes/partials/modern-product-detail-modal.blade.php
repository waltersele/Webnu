@php
    $imagePath = $product->display_image ?? $product->image;
    $videoPath = $product->display_video ?? $product->video;
    $hasMedia = $videoPath || $imagePath;
@endphp

<div class="modal fade wn-dish-modal" id="wnDish{{ $product->id }}" tabindex="-1" role="dialog" aria-labelledby="wnDishLabel{{ $product->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content wn-dish-modal__content">
            <button type="button" class="wn-dish-modal__close" data-dismiss="modal" aria-label="Cerrar">
                @include('themes.partials.icons.svg-times')
            </button>
            @if(!empty($favoritesEnabled))
                <button type="button"
                        class="wn-fav-btn wn-fav-btn--modal"
                        data-fav-toggle
                        data-product-id="{{ $product->id }}"
                        aria-pressed="false"
                        aria-label="{{ config('menu_locales.ui.' . ($menuLocale ?? 'es') . '.favorites_add', 'Añadir a favoritos') }}">
                    @include('themes.partials.icons.svg-heart')
                </button>
            @endif

            @if($hasMedia)
                <div class="wn-dish-modal__hero">
                    @if($videoPath)
                        @include('themes.partials.product-modal-media', ['product' => $product])
                    @else
                        <img src="{{ asset('img/' . $imagePath) }}" alt="{{ $product->name }}">
                    @endif
                    <div class="wn-dish-modal__hero-shade" aria-hidden="true"></div>
                    <div class="wn-dish-modal__hero-text">
                        <h3 class="wn-dish-modal__title" id="wnDishLabel{{ $product->id }}">{{ $product->name }}</h3>
                        @include('themes.partials.product-highlight-badge', ['product' => $product])
                    </div>
                </div>
            @endif

            <div class="wn-dish-modal__body{{ $hasMedia ? '' : ' wn-dish-modal__body--solo' }}">
                @if(!$hasMedia)
                    <h3 class="wn-dish-modal__title wn-dish-modal__title--solo" id="wnDishLabel{{ $product->id }}">
                        {{ $product->name }}
                        @include('themes.partials.product-highlight-badge', ['product' => $product])
                    </h3>
                @endif

                @if($product->description)
                    <p class="wn-dish-modal__desc">{{ $product->description }}</p>
                @else
                    <p class="wn-dish-modal__desc wn-dish-modal__desc--muted">Sin descripción adicional.</p>
                @endif

                @include('themes.partials.product-prices', ['product' => $product])
                @include('themes.partials.menu-product-allergens', ['product' => $product])
            </div>
        </div>
    </div>
</div>
