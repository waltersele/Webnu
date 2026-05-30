@php
    $variant = $variant ?? $company->template;
    $cardLayout = $cardLayout ?? 'horizontal';
    $hero = $company->heroConfig();
    $preset = $hero['preset'] ?? 'compact_card';

    $featuredProduct = $featuredProduct ?? null;
    if ($preset === 'spotlight_dish' && ! $featuredProduct) {
        foreach ($sections as $section) {
            foreach ($section->products as $product) {
                if (! empty($product->highlight)) {
                    $featuredProduct = $product;
                    break 2;
                }
            }
        }
        if (! $featuredProduct) {
            foreach ($sections as $section) {
                if ($section->products->count()) {
                    $featuredProduct = $section->products->first();
                    break;
                }
            }
        }
    }

    $effectivePreset = $preset;
    if ($preset === 'spotlight_dish' && ! $featuredProduct) {
        $effectivePreset = $hero['fallback'] ?? 'compact_card';
    }

    $hasHeroBanner = $effectivePreset !== 'minimal_bar';
    $usesTopHeader = $effectivePreset === 'minimal_bar';
    $usesStickyNav = in_array($variant, ['nocturne', 'catalogo', 'temporada', 'atelier', 'maison'], true);

    $shellClass = 'wn-menu-shell';
    if ($variant) {
        $shellClass .= ' wn-menu-shell--' . $variant;
    }
    if ($hasHeroBanner) {
        $shellClass .= ' wn-menu-shell--no-topbar';
    }
@endphp

<div class="{{ $shellClass }}">
    @include('themes.partials.language-switcher')

    @if($usesTopHeader)
        @include('themes.partials.modern-header')
    @endif

    @if($variant === 'catalogo' && $usesTopHeader)
        <header class="wn-hero-catalogo wn-hero-catalogo--inline">
            <h1 class="wn-hero-catalogo__title">{{ $company->chef_name ?: $company->name }}</h1>
            @if($company->comments)
                <p class="wn-hero-catalogo__subtitle">{{ $company->comments }}</p>
            @endif
        </header>
    @endif

    @include('themes.partials.daily-highlights')

    @include('themes.partials.menu-hero', [
        'featuredProduct' => $featuredProduct,
        'variant' => $variant,
    ])

    @if($hasHeroBanner)
        <button type="button" class="wn-floating-info{{ $variant === 'nocturne' ? ' wn-floating-info--nocturne' : '' }}{{ $variant === 'atelier' ? ' wn-floating-info--atelier' : '' }}{{ $variant === 'maison' ? ' wn-floating-info--maison' : '' }}" id="wn-info-toggle" aria-label="Información del negocio">
            @include('themes.partials.icons.svg-info')
        </button>
    @endif

    @if($usesStickyNav)
        @include('themes.partials.sticky-category-nav', ['variant' => $variant])
    @else
        <nav class="wn-menu-nav {{ in_array($variant, ['bistro', 'basic', 'visual', 'mar', 'elegance', 'temporada', 'catalogo', 'saffron', 'pizza', 'fastfood'], true) ? 'wn-menu-nav--light' : '' }}" id="sticker" aria-label="Secciones">
            <div class="wn-menu-nav__track">
                @foreach ($sections as $index => $section)
                    <a href="#" class="wn-menu-chip linkTo {{ $index === 0 ? 'is-active' : '' }}" id="{{ $section->id }}">{{ $section->name }}</a>
                @endforeach
            </div>
        </nav>
    @endif

    @php
        $lightSurface = in_array($variant, ['bistro', 'basic', 'visual', 'fastfood', 'pizza', 'mar', 'elegance', 'temporada', 'catalogo', 'saffron'], true);
        $mainClass = 'wn-menu-main' . ($lightSurface ? ' wn-menu-main--light' : '');
        if ($variant) {
            $mainClass .= ' wn-menu-main--' . $variant;
        }
    @endphp
    <main class="{{ $mainClass }}">
        @foreach ($sections as $section)
            <section class="wn-menu-section{{ $variant ? ' wn-menu-section--' . $variant : '' }}" id="section-{{ $section->id }}">
                <h2 class="wn-menu-section__title{{ $variant ? ' wn-menu-section__title--' . $variant : '' }}">
                    @if($variant === 'lumiere')
                        @include('themes.partials.icons.svg-lumiere-diamond')
                    @elseif($variant === 'fastfood')
                        @include('themes.partials.icons.svg-fastfood-bolt')
                    @elseif($variant === 'saffron')
                        @include('themes.partials.icons.svg-saffron-leaf')
                    @elseif($variant === 'velvet')
                        @include('themes.partials.icons.svg-velvet-wine')
                    @elseif($variant === 'maison')
                        @include('themes.partials.icons.svg-maison-mark')
                    @elseif(in_array($variant, ['nocturne', 'temporada'], true))
                        @include('themes.partials.icons.svg-' . ($variant === 'nocturne' ? 'cocktail' : 'utensils'))
                    @endif
                    {{ $section->name }}
                </h2>
                @foreach ($section->products as $index => $product)
                    @if((in_array($variant, ['nocturne', 'maison'], true) && ($index === 0 || ! empty($product->highlight))) || $variant === 'atelier')
                        @include('themes.partials.cards.product-overlay', ['product' => $product])
                    @elseif($variant === 'temporada')
                        @include('themes.partials.cards.product-temporada', ['product' => $product])
                    @elseif($variant === 'catalogo')
                        @include('themes.partials.cards.product-catalogo', ['product' => $product])
                    @else
                        @include('themes.partials.modern-product-card', ['product' => $product, 'layout' => $cardLayout])
                    @endif
                @endforeach
            </section>
        @endforeach
    </main>

    @include('themes.partials.modern-footer', ['variant' => $variant])

    @if(!empty($favoritesEnabled) && !empty($favoritesCatalog))
        @include('themes.partials.menu-favorites')
    @endif
</div>
