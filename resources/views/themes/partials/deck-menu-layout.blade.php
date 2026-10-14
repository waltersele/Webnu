@php
    $variant = 'deck';
@endphp

<div class="wn-menu-shell wn-menu-shell--deck wn-deck">
    @include('themes.partials.language-switcher')

    <header class="wn-deck-brand">
        @if($company->logo)
            <img class="wn-deck-brand__logo" src="{{ url('img/' . ltrim($company->logo, '/')) }}" alt="{{ $company->name }}">
        @endif
        <h1 class="wn-deck-brand__name">{{ $company->name }}</h1>
        @if($company->chef_name && $company->chef_name !== $company->name)
            <p class="wn-deck-brand__tagline">{{ $company->chef_name }}</p>
        @endif
    </header>

    @include('themes.partials.daily-highlights')

    <nav class="wn-deck-nav" id="sticker" aria-label="Secciones">
        <div class="wn-deck-nav__track">
            @foreach ($sections as $index => $section)
                <a href="#section-{{ $section->id }}"
                   class="wn-deck-nav__chip wn-menu-chip linkTo {{ $index === 0 ? 'is-active' : '' }}"
                   id="{{ $section->id }}">{{ $section->name }}</a>
            @endforeach
        </div>
    </nav>

    <main class="wn-menu-main wn-menu-main--deck">
        @foreach ($sections as $section)
            <section class="wn-deck-section" id="section-{{ $section->id }}">
                <h2 class="wn-deck-section__title">{{ $section->name }}</h2>

                <div class="wn-deck-carousel" data-deck-carousel aria-roledescription="carrusel" aria-label="Platos de {{ $section->name }}">
                    <div class="wn-deck-carousel__track" data-deck-track>
                        @foreach ($section->products as $product)
                            @include('themes.partials.cards.product-deck', ['product' => $product])
                        @endforeach
                    </div>
                    @if($section->products->count() > 1)
                        <div class="wn-deck-dots" data-deck-dots aria-hidden="true">
                            @foreach ($section->products as $dotIndex => $product)
                                <span class="wn-deck-dot {{ $dotIndex === 0 ? 'is-active' : '' }}"></span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <ul class="wn-deck-list">
                    @foreach ($section->products as $product)
                        @php
                            $listPrice = $product->price_unit
                                ? ($product->price_unit . ' �')
                                : ($product->price_portion ? ($product->price_portion . ' �') : null);
                            $listImg = $product->image ? url('img/' . ltrim($product->image, '/')) : null;
                        @endphp
                        <li class="wn-deck-list__item">
                            <button type="button"
                                    class="wn-deck-list__hit"
                                    data-toggle="modal"
                                    data-target="#wnDish{{ $product->id }}">
                                <span class="wn-deck-list__thumb">
                                    @if($listImg)
                                        <img src="{{ $listImg }}" alt="" loading="lazy">
                                    @else
                                        <span>{{ mb_substr($product->name, 0, 1) }}</span>
                                    @endif
                                </span>
                                <span class="wn-deck-list__body">
                                    <span class="wn-deck-list__name">{{ $product->name }}</span>
                                    @if($product->description)
                                        <span class="wn-deck-list__desc">{{ \Illuminate\Support\Str::limit(strip_tags($product->description), 70) }}</span>
                                    @endif
                                </span>
                                @if($listPrice)
                                    <span class="wn-deck-list__price">{{ $listPrice }}</span>
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </main>

    @include('themes.partials.modern-footer', ['variant' => 'deck'])

    @if(!empty($favoritesEnabled) && !empty($favoritesCatalog))
        @include('themes.partials.menu-favorites')
    @endif
</div>
