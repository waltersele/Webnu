<div class="wn-menu-shell wn-menu-shell--catalogo">
    @include('themes.partials.modern-header')

    <header class="wn-hero-catalogo">
        <h1 class="wn-hero-catalogo__title">{{ $company->chef_name ?: 'Catálogo' }}</h1>
        @if($company->comments)
            <p class="wn-hero-catalogo__subtitle">{{ $company->comments }}</p>
        @else
            <p class="wn-hero-catalogo__subtitle">Selección para iniciar su experiencia.</p>
        @endif
    </header>

    @include('themes.partials.sticky-category-nav', ['variant' => 'catalogo'])

    <main class="wn-menu-main wn-menu-main--catalogo">
        @foreach ($sections as $section)
            <section class="wn-menu-section wn-menu-section--catalogo" id="section-{{ $section->id }}">
                <h2 class="wn-menu-section__title wn-menu-section__title--catalogo">{{ $section->name }}</h2>
                @foreach ($section->products as $product)
                    @include('themes.partials.cards.product-catalogo', ['product' => $product])
                @endforeach
            </section>
        @endforeach
    </main>

    @include('themes.partials.modern-footer', ['variant' => 'catalogo'])
</div>
