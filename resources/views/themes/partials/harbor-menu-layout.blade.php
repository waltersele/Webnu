@php
    $variant = 'harbor';
@endphp

<div class="wn-menu-shell wn-menu-shell--harbor wn-harbor">
    @include('themes.partials.language-switcher')

    <header class="wn-harbor-brand">
        @if($company->logo)
            <img class="wn-harbor-brand__logo" src="{{ url('img/' . ltrim($company->logo, '/')) }}" alt="{{ $company->name }}">
        @endif
        <h1 class="wn-harbor-brand__name">{{ $company->name }}</h1>
        <p class="wn-harbor-brand__menu">Menu</p>
    </header>

    @include('themes.partials.daily-highlights')

    <nav class="wn-harbor-nav" id="sticker" aria-label="Secciones">
        <div class="wn-harbor-nav__track">
            @foreach ($sections as $index => $section)
                <a href="#section-{{ $section->id }}"
                   class="wn-harbor-nav__chip wn-menu-chip linkTo {{ $index === 0 ? 'is-active' : '' }}"
                   id="{{ $section->id }}">{{ $section->name }}</a>
            @endforeach
        </div>
    </nav>

    <main class="wn-menu-main wn-menu-main--harbor">
        @foreach ($sections as $section)
            <section class="wn-harbor-section" id="section-{{ $section->id }}">
                <h2 class="wn-harbor-section__title">
                    <span class="wn-harbor-section__rule" aria-hidden="true"></span>
                    <span>{{ $section->name }}</span>
                    <span class="wn-harbor-section__rule" aria-hidden="true"></span>
                </h2>
                <div class="wn-harbor-list">
                    @foreach ($section->products as $product)
                        @include('themes.partials.cards.product-harbor', ['product' => $product])
                    @endforeach
                </div>
            </section>
        @endforeach
    </main>

    @include('themes.partials.modern-footer', ['variant' => 'harbor'])

    @if(!empty($favoritesEnabled) && !empty($favoritesCatalog))
        @include('themes.partials.menu-favorites')
    @endif
</div>
