<div class="wn-menu-shell wn-menu-shell--temporada">
    @include('themes.partials.modern-header')

    <header class="wn-hero-temporada">
        <p class="wn-hero-temporada__eyebrow">{{ strtoupper($company->name) }}</p>
        <h1 class="wn-hero-temporada__title">{{ $company->chef_name ?: 'Menú de Temporada' }}</h1>
        <div class="wn-hero-temporada__divider" aria-hidden="true"></div>
        @if($company->comments)
            <p class="wn-hero-temporada__subtitle">{{ $company->comments }}</p>
        @endif
    </header>

    @include('themes.partials.sticky-category-nav', ['variant' => 'temporada'])

    <main class="wn-menu-main wn-menu-main--temporada">
        @foreach ($sections as $section)
            <section class="wn-menu-section wn-menu-section--temporada" id="section-{{ $section->id }}">
                <h2 class="wn-menu-section__title wn-menu-section__title--temporada">
                    <i class="fas fa-utensils wn-section-icon" aria-hidden="true"></i>
                    {{ $section->name }}
                </h2>
                @foreach ($section->products as $product)
                    @include('themes.partials.cards.product-temporada', ['product' => $product])
                @endforeach
            </section>
        @endforeach
    </main>

    @include('themes.partials.modern-footer', ['variant' => 'temporada'])
</div>
