@php
    $variant = 'grid';
    $promoSlides = collect();
    foreach ($sections as $section) {
        foreach ($section->products as $product) {
            if (in_array($product->highlight ?? '', ['featured', 'bestseller'], true) && ! empty($product->image)) {
                $promoSlides->push($product);
            }
        }
    }
    if ($promoSlides->isEmpty()) {
        foreach ($sections as $section) {
            foreach ($section->products as $product) {
                if (! empty($product->image)) {
                    $promoSlides->push($product);
                }
            }
        }
    }
    $promoSlides = $promoSlides->unique('id')->take(5)->values();
@endphp

<div class="wn-menu-shell wn-menu-shell--grid wn-grid">
    @include('themes.partials.language-switcher')

    <header class="wn-grid-brand">
        @if($company->logo)
            <img class="wn-grid-brand__logo" src="{{ url('img/' . ltrim($company->logo, '/')) }}" alt="{{ $company->name }}">
        @endif
        <h1 class="wn-grid-brand__name">{{ $company->name }}</h1>
    </header>

    @include('themes.partials.daily-highlights')

    @if($promoSlides->isNotEmpty())
        <div class="wn-grid-promo" aria-label="Destacados">
            <div class="wn-grid-promo__track">
                @foreach($promoSlides as $promo)
                    @php
                        $promoPrice = $promo->price_unit
                            ? ($promo->price_unit . ' �')
                            : ($promo->price_portion ? ($promo->price_portion . ' �') : null);
                        $promoImg = url('img/' . ltrim($promo->image, '/'));
                    @endphp
                    <article class="wn-grid-promo__card">
                        <div class="wn-grid-promo__copy">
                            <p class="wn-grid-promo__eyebrow">Propuesta de la semana</p>
                            <h2 class="wn-grid-promo__title">{{ $promo->name }}</h2>
                            @if($promoPrice)
                                <p class="wn-grid-promo__price">{{ $promoPrice }}</p>
                            @endif
                            <button type="button"
                                    class="wn-grid-promo__cta"
                                    data-toggle="modal"
                                    data-target="#wnDish{{ $promo->id }}">Ver</button>
                        </div>
                        <div class="wn-grid-promo__media">
                            <img src="{{ $promoImg }}" alt="" loading="lazy">
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif

    <p class="wn-grid-menu-label">Nuestro men�</p>

    <nav class="wn-grid-nav" id="sticker" aria-label="Secciones">
        <div class="wn-grid-nav__track">
            @foreach ($sections as $index => $section)
                <a href="#section-{{ $section->id }}"
                   class="wn-grid-nav__chip wn-menu-chip linkTo {{ $index === 0 ? 'is-active' : '' }}"
                   id="{{ $section->id }}">{{ $section->name }}</a>
            @endforeach
        </div>
    </nav>

    <main class="wn-menu-main wn-menu-main--grid">
        @foreach ($sections as $section)
            <section class="wn-grid-section" id="section-{{ $section->id }}">
                <h2 class="wn-grid-section__title">{{ $section->name }}</h2>
                <div class="wn-grid-cards">
                    @foreach ($section->products as $product)
                        @include('themes.partials.cards.product-grid', ['product' => $product])
                    @endforeach
                </div>
            </section>
        @endforeach
    </main>

    @include('themes.partials.modern-footer', ['variant' => 'grid'])

    @if(!empty($favoritesEnabled) && !empty($favoritesCatalog))
        @include('themes.partials.menu-favorites')
    @endif
</div>
