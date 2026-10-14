@php
    $variant = 'swipe';
    $locale = $menuLocale ?? 'es';
    $ui = $menuLocaleService ?? null;
    $menuLabel = $ui ? $ui->uiLabel('menu', $locale) : 'Carta';
    $sectionsLabel = $ui ? $ui->uiLabel('sections', $locale) : 'Secciones';
@endphp

<div class="wn-menu-shell wn-menu-shell--swipe wn-swipe">
    @include('themes.partials.language-switcher')

    <header class="wn-swipe-brand">
        @if($company->logo)
            <img class="wn-swipe-brand__logo" src="{{ url('img/' . ltrim($company->logo, '/')) }}" alt="{{ $company->name }}">
        @endif
        <h1 class="wn-swipe-brand__name">{{ $company->name }}</h1>
        @if($company->chef_name && $company->chef_name !== $company->name)
            <p class="wn-swipe-brand__chef">{{ $company->chef_name }}</p>
        @endif
    </header>

    @include('themes.partials.daily-highlights')

    <p class="wn-swipe-menu-label">{{ $menuLabel }}</p>

    <nav class="wn-swipe-nav" id="sticker" aria-label="{{ $sectionsLabel }}">
        <div class="wn-swipe-nav__track">
            @foreach ($sections as $index => $section)
                <a href="#section-{{ $section->id }}"
                   class="wn-swipe-nav__chip linkTo {{ $index === 0 ? 'is-active' : '' }}"
                   id="{{ $section->id }}">{{ $section->name }}</a>
            @endforeach
        </div>
    </nav>

    <main class="wn-menu-main wn-menu-main--swipe">
        @foreach ($sections as $section)
            <section class="wn-swipe-section" id="section-{{ $section->id }}">
                <h2 class="wn-swipe-section__title">{{ $section->name }}</h2>
                <div class="wn-swipe-rail" data-swipe-rail tabindex="0" aria-label="{{ $ui ? $ui->uiLabel('dishes_of', $locale, ['name' => $section->name]) : 'Platos de '.$section->name }}">
                    <div class="wn-swipe-rail__track">
                        @foreach ($section->products as $product)
                            @include('themes.partials.cards.product-swipe', ['product' => $product])
                        @endforeach
                    </div>
                </div>
                @if($section->products->count() > 1)
                    <div class="wn-carousel-dots" data-swipe-dots role="tablist" aria-label="{{ $section->name }}">
                        @foreach ($section->products as $dotIndex => $dotProduct)
                            <button type="button"
                                    class="wn-carousel-dots__dot {{ $dotIndex === 0 ? 'is-active' : '' }}"
                                    data-swipe-dot="{{ $dotIndex }}"
                                    role="tab"
                                    aria-selected="{{ $dotIndex === 0 ? 'true' : 'false' }}"
                                    aria-label="{{ $dotProduct->name }}"></button>
                        @endforeach
                    </div>
                @endif
            </section>
        @endforeach
    </main>

    @include('themes.partials.modern-footer', ['variant' => 'swipe'])

    @if(!empty($favoritesEnabled) && !empty($favoritesCatalog))
        @include('themes.partials.menu-favorites')
    @endif
</div>
