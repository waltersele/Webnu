@php
    $variant = 'swipe';
    $mapsUrl = null;
    if ($company->address || $company->city) {
        $mapsQuery = trim(implode(', ', array_filter([
            $company->address,
            $company->postal_code,
            $company->city,
            $company->province,
            $company->country,
        ])));
        if ($mapsQuery !== '') {
            $mapsUrl = 'https://maps.google.com/?q=' . rawurlencode($mapsQuery);
        }
    }
    $phone = $company->phone ?: $company->mobile_phone;
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
        @if($mapsUrl || $phone)
            <div class="wn-swipe-brand__actions">
                @if($mapsUrl)
                    <a class="wn-swipe-action" href="{{ $mapsUrl }}" target="_blank" rel="noopener" aria-label="Ubicacion">
                        @include('themes.partials.icons.svg-map-pin')
                    </a>
                @endif
                @if($phone)
                    <a class="wn-swipe-action" href="tel:{{ $phone }}" aria-label="Telefono">
                        @include('themes.partials.icons.svg-phone')
                    </a>
                @endif
            </div>
        @endif
    </header>

    @include('themes.partials.daily-highlights')

    <p class="wn-swipe-menu-label">Menu</p>

    <nav class="wn-swipe-nav" id="sticker" aria-label="Secciones">
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
                <div class="wn-swipe-rail" data-swipe-rail tabindex="0" aria-label="Platos de {{ $section->name }}">
                    <div class="wn-swipe-rail__track">
                        @foreach ($section->products as $product)
                            @include('themes.partials.cards.product-swipe', ['product' => $product])
                        @endforeach
                    </div>
                </div>
            </section>
        @endforeach
    </main>

    @include('themes.partials.modern-footer', ['variant' => 'swipe'])

    @if(!empty($favoritesEnabled) && !empty($favoritesCatalog))
        @include('themes.partials.menu-favorites')
    @endif
</div>
