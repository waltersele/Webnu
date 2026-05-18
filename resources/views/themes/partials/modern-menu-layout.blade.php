@php
    $variant = $variant ?? $company->template;
    $cardLayout = $cardLayout ?? 'horizontal';
    $heroMode = $heroMode ?? 'compact';
    $featuredProduct = $featuredProduct ?? null;

    if ($heroMode === 'spotlight' && !$featuredProduct) {
        foreach ($sections as $section) {
            foreach ($section->products as $product) {
                if (!empty($product->highlight)) {
                    $featuredProduct = $product;
                    break 2;
                }
            }
        }
        if (!$featuredProduct) {
            foreach ($sections as $section) {
                if ($section->products->count()) {
                    $featuredProduct = $section->products->first();
                    break;
                }
            }
        }
    }

    $hasHeroBanner = ($heroMode === 'spotlight' && $featuredProduct)
        || ($heroMode === 'spotlight' && $company->background_header)
        || $heroMode === 'dark'
        || ($heroMode === 'light' && $company->background_header)
        || in_array($heroMode, ['compact', 'circle'], true);
@endphp

<div class="wn-menu-shell{{ $hasHeroBanner ? ' wn-menu-shell--no-topbar' : '' }}">
    @if(!$hasHeroBanner)
        @include('themes.partials.modern-header')
    @endif

    @if($heroMode === 'spotlight' && $featuredProduct)
        <section class="wn-menu-spotlight">
            <div class="wn-menu-spotlight__media">
                @if(($featuredProduct->display_image ?? $featuredProduct->image) || $featuredProduct->video)
                    @include('themes.partials.product-inline-thumb', ['product' => $featuredProduct])
                @else
                    <div class="wn-modern-card__placeholder wn-modern-card__placeholder--featured"><i class="fas fa-utensils"></i></div>
                @endif
                <div class="wn-menu-spotlight__gradient"></div>
                <div class="wn-menu-spotlight__content">
                    @if(!empty($featuredProduct->highlight))
                        <div class="wn-menu-spotlight__badge">
                            @include('themes.partials.product-highlight-badge', ['product' => $featuredProduct])
                        </div>
                    @endif
                    <h2>{{ $featuredProduct->name }}</h2>
                    @if($featuredProduct->description)
                        <p>{{ \Illuminate\Support\Str::limit($featuredProduct->description, 100) }}</p>
                    @endif
                </div>
            </div>
        </section>
    @elseif($heroMode === 'spotlight' && $company->background_header)
        <section class="wn-menu-hero wn-menu-hero--light" style="--wn-hero-image: url('{{ URL::to('/') . '/img/' . $company->background_header }}')">
            <div class="wn-menu-hero__overlay wn-menu-hero__overlay--light">
                @if($company->comments)<p class="wn-menu-hero__subtitle">{{ $company->comments }}</p>@endif
            </div>
        </section>
    @elseif($heroMode === 'dark')
        @php
            $heroBgUrl = $company->background_header
                ? URL::to('/') . '/img/' . $company->background_header
                : asset('img/demo/demo-01.jpg');
        @endphp
        <section class="wn-menu-hero wn-menu-hero--dark" style="--wn-hero-image: url('{{ $heroBgUrl }}')">
            <div class="wn-menu-hero__overlay">
                @if($company->chef_name)
                    <p class="wn-menu-hero__eyebrow">{{ strtoupper($company->chef_name) }}</p>
                @endif
                <h1 class="wn-menu-hero__title">{{ $company->name }}</h1>
                @if($company->comments)
                    <p class="wn-menu-hero__subtitle">{{ $company->comments }}</p>
                @endif
            </div>
        </section>
    @elseif($heroMode === 'light' && $company->background_header)
        <section class="wn-menu-hero wn-menu-hero--light" style="--wn-hero-image: url('{{ URL::to('/') . '/img/' . $company->background_header }}')">
            <div class="wn-menu-hero__overlay wn-menu-hero__overlay--light">
                @if($company->comments)<p class="wn-menu-hero__subtitle">{{ $company->comments }}</p>@endif
            </div>
        </section>
    @elseif(in_array($heroMode, ['compact', 'circle'], true))
        <section class="wn-menu-hero wn-menu-hero--{{ $heroMode }}" @if($company->background_header) style="--wn-hero-image: url('{{ URL::to('/') . '/img/' . $company->background_header }}')" @else style="--wn-hero-image: url('{{ asset('img/default-header.jpg') }}')" @endif>
            <div class="wn-menu-hero__overlay wn-menu-hero__overlay--{{ $heroMode }}">
                @if($heroMode === 'circle')
                    <div class="wn-menu-hero__logo-ring">
                        <img src="{{ $company->logo ? asset('img/'.$company->logo) : asset('img/front/logo.png') }}" alt="{{ $company->name }}">
                    </div>
                @else
                    <img class="wn-menu-hero__logo-inline" src="{{ $company->logo ? asset('img/'.$company->logo) : asset('img/front/logo.png') }}" alt="{{ $company->name }}">
                @endif
                <h1 class="wn-menu-hero__title wn-menu-hero__title--compact">{{ $company->name }}</h1>
                @if($company->chef_name)
                    <p class="wn-menu-hero__eyebrow wn-menu-hero__eyebrow--muted">{{ $company->chef_name }}</p>
                @endif
            </div>
        </section>
    @endif

    @if($hasHeroBanner)
        <button type="button" class="wn-floating-info" id="wn-info-toggle" aria-label="Información del negocio">
            <i class="fas fa-info-circle" aria-hidden="true"></i>
        </button>
    @endif

    <nav class="wn-menu-nav {{ in_array($variant, ['bistro', 'basic', 'visual'], true) ? 'wn-menu-nav--light' : '' }}" id="sticker" aria-label="Secciones">
        <div class="wn-menu-nav__track">
            @foreach ($sections as $index => $section)
                <a href="#" class="wn-menu-chip linkTo {{ $index === 0 ? 'is-active' : '' }}" id="{{ $section->id }}">{{ $section->name }}</a>
            @endforeach
        </div>
    </nav>

    @php $lightSurface = in_array($variant, ['bistro', 'basic', 'visual'], true); @endphp
    <main class="wn-menu-main {{ $lightSurface ? 'wn-menu-main--light' : '' }}">
        @foreach ($sections as $section)
            <section class="wn-menu-section" id="section-{{ $section->id }}">
                <h2 class="wn-menu-section__title">{{ $section->name }}</h2>
                @foreach ($section->products as $product)
                    @include('themes.partials.modern-product-card', ['product' => $product, 'layout' => $cardLayout])
                @endforeach
            </section>
        @endforeach
    </main>

    @include('themes.partials.modern-footer', ['variant' => $variant])
</div>

