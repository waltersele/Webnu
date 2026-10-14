@php
    $variant = 'ambre';
    $spotlightSlides = collect();
    foreach ($sections as $section) {
        foreach ($section->products as $product) {
            if (in_array($product->highlight ?? '', ['featured', 'bestseller'], true)) {
                $spotlightSlides->push($product);
            }
        }
    }
    if ($spotlightSlides->isEmpty()) {
        foreach ($sections as $section) {
            foreach ($section->products as $product) {
                if (! empty($product->image) || ! empty($product->highlight)) {
                    $spotlightSlides->push($product);
                }
            }
        }
    }
    if ($spotlightSlides->isEmpty()) {
        foreach ($sections as $section) {
            if ($section->products->count()) {
                $spotlightSlides->push($section->products->first());
                break;
            }
        }
    }
    $spotlightSlides = $spotlightSlides->unique('id')->take(8)->values();
    $houseLabel = $company->chef_name ?: $company->name;
@endphp

<div class="wn-menu-shell wn-menu-shell--ambre wn-ambre">
    @include('themes.partials.language-switcher')

    @include('themes.partials.daily-highlights')

    <nav class="wn-ambre-nav" id="sticker" aria-label="Secciones">
        <div class="wn-ambre-nav__track">
            @foreach ($sections as $index => $section)
                <a href="#section-{{ $section->id }}"
                   class="wn-ambre-nav__chip linkTo {{ $index === 0 ? 'is-active' : '' }}"
                   id="{{ $section->id }}">{{ $section->name }}</a>
            @endforeach
        </div>
    </nav>

    @if($spotlightSlides->isNotEmpty())
        <section class="wn-ambre-spotlight" data-ambre-spotlight aria-roledescription="carrusel" aria-label="Platos destacados">
            @foreach($spotlightSlides as $slideIndex => $slide)
                @php
                    $priceLabel = $slide->price_unit
                        ? ($slide->price_unit . ' �')
                        : ($slide->price_portion ? ($slide->price_portion . ' �') : null);
                    $img = $slide->image
                        ? url('img/' . ltrim($slide->image, '/'))
                        : null;
                @endphp
                <article class="wn-ambre-spotlight__slide {{ $slideIndex === 0 ? 'is-active' : '' }}"
                         data-ambre-slide
                         data-product-id="{{ $slide->id }}"
                         @if($slideIndex !== 0) hidden @endif>
                    @if($priceLabel)
                        <p class="wn-ambre-spotlight__price">{{ $priceLabel }}</p>
                        @if(($slide->highlight ?? '') === 'featured')
                            <p class="wn-ambre-spotlight__eyebrow">Destacado</p>
                        @endif
                    @endif
                    <h1 class="wn-ambre-spotlight__title">{{ $slide->name }}</h1>
                    <div class="wn-ambre-spotlight__media">
                        @if($spotlightSlides->count() > 1)
                            <button type="button" class="wn-ambre-spotlight__arrow wn-ambre-spotlight__arrow--prev" data-ambre-prev aria-label="Anterior">
                                <span aria-hidden="true">&lsaquo;</span>
                            </button>
                        @endif
                        @if($img)
                            <img src="{{ $img }}" alt="{{ $slide->name }}" class="wn-ambre-spotlight__img" loading="{{ $slideIndex === 0 ? 'eager' : 'lazy' }}">
                        @else
                            <div class="wn-ambre-spotlight__img wn-ambre-spotlight__img--empty" aria-hidden="true">{{ mb_substr($slide->name, 0, 1) }}</div>
                        @endif
                        @if($spotlightSlides->count() > 1)
                            <button type="button" class="wn-ambre-spotlight__arrow wn-ambre-spotlight__arrow--next" data-ambre-next aria-label="Siguiente">
                                <span aria-hidden="true">&rsaquo;</span>
                            </button>
                        @endif
                    </div>
                    <div class="wn-ambre-spotlight__copy">
                        <h2 class="wn-ambre-spotlight__subtitle">{{ $slide->name }}</h2>
                        @if($slide->description)
                            <p class="wn-ambre-spotlight__desc">{{ $slide->description }}</p>
                        @endif
                        @include('themes.partials.menu-product-allergens', ['product' => $slide])
                    </div>
                    <button type="button"
                            class="wn-ambre-spotlight__cta"
                            data-toggle="modal"
                            data-target="#wnDish{{ $slide->id }}">
                        Ver plato
                    </button>
                </article>
            @endforeach
        </section>
    @endif

    <main class="wn-menu-main wn-menu-main--ambre">
        @foreach ($sections as $section)
            <section class="wn-menu-section wn-menu-section--ambre" id="section-{{ $section->id }}">
                <header class="wn-ambre-section-head">
                    <h2 class="wn-ambre-section-head__title">{{ $section->name }}</h2>
                    <p class="wn-ambre-section-head__house">{{ $houseLabel }}</p>
                </header>
                <div class="wn-ambre-grid">
                    @foreach ($section->products as $product)
                        @include('themes.partials.cards.product-ambre', ['product' => $product])
                    @endforeach
                </div>
            </section>
        @endforeach
    </main>

    @include('themes.partials.modern-footer', ['variant' => 'ambre'])

    @if(!empty($favoritesEnabled) && !empty($favoritesCatalog))
        @include('themes.partials.menu-favorites')
    @endif
</div>
