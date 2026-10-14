@php
    $variant = 'lista';
    $locale = $menuLocale ?? 'es';
    $ui = $menuLocaleService ?? null;
    $menuLabel = $ui ? $ui->uiLabel('menu', $locale) : 'Carta';
    $sectionsLabel = $ui ? $ui->uiLabel('sections', $locale) : 'Secciones';
@endphp

<div class="wn-menu-shell wn-menu-shell--lista wn-lista">
    @include('themes.partials.language-switcher')

    <header class="wn-lista-brand">
        @if($company->logo)
            <img class="wn-lista-brand__logo" src="{{ url('img/' . ltrim($company->logo, '/')) }}" alt="{{ $company->name }}">
        @endif
        <h1 class="wn-lista-brand__name">{{ $company->name }}</h1>
        @if($company->chef_name && $company->chef_name !== $company->name)
            <p class="wn-lista-brand__chef">{{ $company->chef_name }}</p>
        @endif
        <p class="wn-lista-brand__menu">{{ $menuLabel }}</p>
    </header>

    @include('themes.partials.daily-highlights')

    <nav class="wn-lista-nav" id="sticker" aria-label="{{ $sectionsLabel }}">
        <div class="wn-lista-nav__track">
            @foreach ($sections as $index => $section)
                <a href="#section-{{ $section->id }}"
                   class="wn-lista-nav__chip wn-menu-chip linkTo {{ $index === 0 ? 'is-active' : '' }}"
                   id="{{ $section->id }}">{{ $section->name }}</a>
            @endforeach
        </div>
    </nav>

    <main class="wn-menu-main wn-menu-main--lista">
        @foreach ($sections as $section)
            <section class="wn-lista-section" id="section-{{ $section->id }}">
                <h2 class="wn-lista-section__title">{{ $section->name }}</h2>
                <div class="wn-lista-list">
                    @foreach ($section->products as $product)
                        @include('themes.partials.cards.product-lista', ['product' => $product])
                    @endforeach
                </div>
            </section>
        @endforeach
    </main>

    @include('themes.partials.modern-footer', ['variant' => 'lista'])

    @if(!empty($favoritesEnabled) && !empty($favoritesCatalog))
        @include('themes.partials.menu-favorites')
    @endif
</div>
