@php
    $favUi = config('menu_locales.ui.' . ($menuLocale ?? 'es'), config('menu_locales.ui.es', []));
    $defaultLocale = $favoritesCatalog['defaultLocale'] ?? 'es';
    $menuLocaleCode = $favoritesCatalog['menuLocale'] ?? ($menuLocale ?? 'es');
    $defaultLocaleLabel = $favoritesCatalog['localeLabels'][$defaultLocale] ?? 'Original';
    $menuLocaleLabel = $favoritesCatalog['localeLabels'][$menuLocaleCode] ?? strtoupper($menuLocaleCode);
@endphp

<script type="application/json" id="webnu-favorites-catalog">@json($favoritesCatalog)</script>

<div id="webnu-favorites-root"
     class="wn-favorites"
     data-ui-labels="{{ json_encode([
         'title' => $favUi['favorites_title'] ?? 'Mis favoritos',
         'empty' => $favUi['favorites_empty'] ?? 'Aún no has añadido platos.',
         'hint' => $favUi['favorites_hint'] ?? 'Muestra esta pantalla al camarero para indicar qué quieres pedir.',
         'remove' => $favUi['favorites_remove'] ?? 'Quitar',
         'menuLocaleLabel' => $menuLocaleLabel,
         'defaultLocaleLabel' => $defaultLocaleLabel,
         'openList' => $favUi['favorites_open'] ?? 'Mis favoritos',
     ]) }}">

    <div class="wn-favorites-bar" role="region" aria-label="{{ $favUi['favorites_open'] ?? 'Mis favoritos' }}">
        <button type="button" class="wn-favorites-bar__btn" data-fav-open aria-expanded="false">
            @include('themes.partials.icons.svg-heart')
            <span>{{ $favUi['favorites_open'] ?? 'Mis favoritos' }}</span>
            <span class="wn-favorites-bar__badge" data-fav-count hidden>0</span>
        </button>
    </div>

    <div class="wn-favorites-panel" data-fav-panel hidden aria-hidden="true">
        <div class="wn-favorites-panel__screen" role="dialog" aria-modal="true" aria-labelledby="wn-favorites-title">
            <header class="wn-favorites-panel__head">
                <h2 id="wn-favorites-title">{{ $favUi['favorites_title'] ?? 'Mis favoritos' }}</h2>
                <button type="button" class="wn-favorites-panel__close" data-fav-close aria-label="Cerrar">
                    @include('themes.partials.icons.svg-times')
                </button>
            </header>
            <p class="wn-favorites-panel__hint">{{ $favUi['favorites_hint'] ?? 'Muestra esta pantalla al camarero para indicar qué quieres pedir.' }}</p>
            <ul class="wn-favorites-list" data-fav-list></ul>
            <p class="wn-favorites-empty" data-fav-empty>{{ $favUi['favorites_empty'] ?? 'Aún no has añadido platos.' }}</p>
        </div>
    </div>

    <template id="wn-fav-icon-remove">@include('themes.partials.icons.svg-times')</template>
    <template id="wn-fav-icon-placeholder">@include('themes.partials.icons.svg-utensils')</template>
</div>
