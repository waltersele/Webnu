<div class="wn-menu-shell wn-menu-shell--nocturne wn-menu-shell--no-topbar">
    <button type="button" class="wn-floating-info wn-floating-info--nocturne" id="wn-info-toggle" aria-label="Información del negocio">
        @include('themes.partials.icons.svg-info')
    </button>

    <header class="wn-hero-nocturne">
        <p class="wn-hero-nocturne__brand">{{ strtoupper($company->name) }}</p>
        <h1 class="wn-hero-nocturne__title">{{ $company->name }}</h1>
        @if($company->comments)
            <p class="wn-hero-nocturne__subtitle">{{ $company->comments }}</p>
        @endif
    </header>

    @include('themes.partials.sticky-category-nav', ['variant' => 'nocturne'])

    <main class="wn-menu-main wn-menu-main--nocturne">
        @foreach ($sections as $section)
            <section class="wn-menu-section wn-menu-section--nocturne" id="section-{{ $section->id }}">
                <h2 class="wn-menu-section__title wn-menu-section__title--nocturne">
                    @include('themes.partials.icons.svg-cocktail')
                    {{ $section->name }}
                </h2>
                @foreach ($section->products as $index => $product)
                    @if($index === 0 || !empty($product->highlight))
                        @include('themes.partials.cards.product-overlay', ['product' => $product])
                    @else
                        @include('themes.partials.modern-product-card', ['product' => $product, 'layout' => 'stacked'])
                    @endif
                @endforeach
            </section>
        @endforeach
    </main>

    @include('themes.partials.modern-footer', ['variant' => 'nocturne'])
</div>
