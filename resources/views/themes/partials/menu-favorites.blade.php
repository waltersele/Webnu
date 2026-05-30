@php
    $favUi = config('menu_locales.ui.' . ($menuLocale ?? 'es'), config('menu_locales.ui.es', []));
    $defaultLocaleLabel = $favoritesCatalog['localeLabels'][$favoritesCatalog['defaultLocale'] ?? 'es'] ?? 'Original';
@endphp

<script type="application/json" id="webnu-favorites-catalog">@json($favoritesCatalog)</script>

<div id="webnu-favorites-root"
     class="wn-favorites"
     data-ui-labels="{{ json_encode([
         'title' => $favUi['favorites_title'] ?? 'Mis favoritos',
         'empty' => $favUi['favorites_empty'] ?? 'Aún no has añadido platos.',
         'showWaiter' => $favUi['favorites_show_waiter'] ?? 'Mostrar al camarero',
         'closeWaiter' => $favUi['favorites_close_waiter'] ?? 'Volver',
         'remove' => $favUi['favorites_remove'] ?? 'Quitar',
         'originalLabel' => ($favUi['favorites_original_label'] ?? 'En :locale') . '',
         'defaultLocaleLabel' => $defaultLocaleLabel,
         'openList' => $favUi['favorites_open'] ?? 'Mis favoritos',
     ]) }}">

    <div class="wn-favorites-bar" role="region" aria-label="{{ $favUi['favorites_title'] ?? 'Mis favoritos' }}">
        <button type="button" class="wn-favorites-bar__btn" data-fav-open aria-expanded="false">
            @include('themes.partials.icons.svg-heart')
            <span>{{ $favUi['favorites_open'] ?? 'Mis favoritos' }}</span>
            <span class="wn-favorites-bar__badge" data-fav-count hidden>0</span>
        </button>
    </div>

    <div class="wn-favorites-panel" data-fav-panel hidden aria-hidden="true">
        <div class="wn-favorites-panel__backdrop" data-fav-close tabindex="-1"></div>
        <div class="wn-favorites-panel__sheet" role="dialog" aria-modal="true" aria-labelledby="wn-favorites-title">
            <header class="wn-favorites-panel__head">
                <h2 id="wn-favorites-title">{{ $favUi['favorites_title'] ?? 'Mis favoritos' }}</h2>
                <button type="button" class="wn-favorites-panel__close" data-fav-close aria-label="Cerrar">
                    @include('themes.partials.icons.svg-times')
                </button>
            </header>
            <div class="wn-favorites-panel__actions">
                <button type="button" class="wn-favorites-panel__waiter-btn" data-fav-waiter-mode>
                    {{ $favUi['favorites_show_waiter'] ?? 'Mostrar al camarero' }}
                </button>
            </div>
            <ul class="wn-favorites-list" data-fav-list></ul>
            <p class="wn-favorites-empty" data-fav-empty>{{ $favUi['favorites_empty'] ?? 'Aún no has añadido platos.' }}</p>
        </div>
    </div>
</div>
