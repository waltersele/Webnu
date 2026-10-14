@php
    $variant = 'rail';
@endphp

<div class="wn-menu-shell wn-menu-shell--rail wn-rail">
    @include('themes.partials.language-switcher')

    <header class="wn-rail-brand">
        @if($company->logo)
            <img class="wn-rail-brand__logo" src="{{ url('img/' . ltrim($company->logo, '/')) }}" alt="{{ $company->name }}">
        @endif
        <h1 class="wn-rail-brand__name">{{ $company->name }}</h1>
        @if($company->chef_name && $company->chef_name !== $company->name)
            <p class="wn-rail-brand__tagline">{{ $company->chef_name }}</p>
        @endif
    </header>

    @include('themes.partials.daily-highlights')

    <nav class="wn-rail-nav" id="sticker" aria-label="Secciones">
        <div class="wn-rail-nav__track">
            @foreach ($sections as $index => $section)
                <a href="#section-{{ $section->id }}"
                   class="wn-rail-nav__chip wn-menu-chip linkTo {{ $index === 0 ? 'is-active' : '' }}"
                   id="{{ $section->id }}">{{ $section->name }}</a>
            @endforeach
        </div>
    </nav>

    <main class="wn-menu-main wn-menu-main--rail">
        @foreach ($sections as $section)
            <section class="wn-rail-section" id="section-{{ $section->id }}">
                <h2 class="wn-rail-section__title">
                    <span class="wn-rail-section__rule" aria-hidden="true"></span>
                    <span class="wn-rail-section__label">{{ $section->name }}</span>
                    <span class="wn-rail-section__rule" aria-hidden="true"></span>
                </h2>
                <div class="wn-rail-list">
                    @foreach ($section->products as $product)
                        @include('themes.partials.cards.product-rail', ['product' => $product])
                    @endforeach
                </div>
            </section>
        @endforeach
    </main>

    @include('themes.partials.modern-footer', ['variant' => 'rail'])

    @if(!empty($favoritesEnabled) && !empty($favoritesCatalog))
        @include('themes.partials.menu-favorites')
    @endif
</div>
