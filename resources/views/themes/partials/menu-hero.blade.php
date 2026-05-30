@php
    $hero = $company->heroConfig();
    $preset = $hero['preset'] ?? 'compact_card';
    $featuredProduct = $featuredProduct ?? null;
    $variant = $variant ?? ($company->template ?? 'basic');

    if ($preset === 'spotlight_dish') {
        if (! $featuredProduct) {
            foreach ($sections ?? [] as $section) {
                foreach ($section->products as $product) {
                    if (! empty($product->highlight)) {
                        $featuredProduct = $product;
                        break 2;
                    }
                }
            }
        }
        if (! $featuredProduct && ! empty($sections)) {
            foreach ($sections as $section) {
                if ($section->products->count()) {
                    $featuredProduct = $section->products->first();
                    break;
                }
            }
        }
        if (! $featuredProduct) {
            $fallbackKey = $hero['fallback'] ?? 'compact_card';
            $fallback = config('company_templates.hero_presets.' . $fallbackKey, []);
            $hero = array_merge($hero, $fallback, ['preset' => $fallbackKey]);
            $preset = $fallbackKey;
        }
    }

    $showHeroBanner = ! in_array($preset, ['minimal_bar', 'spotlight_dish'], true)
        || ($preset === 'spotlight_dish' && $featuredProduct);

    if ($preset === 'minimal_bar') {
        $showHeroBanner = false;
    }

    $heroUrl = $company->background_header
        ? URL::to('/') . '/img/' . $company->background_header
        : asset('img/default-header.jpg');

    $bleed = ! empty($hero['bleed']);
    $logoShape = $hero['logo_shape'] ?? 'rounded';
    $showLogo = ! empty($hero['show_logo']);
    $showChef = ! empty($hero['show_chef']);
    $overlayMode = $company->header_overlay_mode ?: 'dark';
    $textTone = ($company->heroCssVars()['--wn-hero-text-tone'] ?? 'light');
    $heroModifiers = ' wn-menu-hero--overlay-' . $overlayMode . ' wn-menu-hero--text-' . $textTone;
@endphp

@if($preset === 'spotlight_dish' && $featuredProduct)
    <section class="wn-menu-spotlight wn-menu-spotlight--premium">
        <div class="wn-menu-spotlight__media">
            @if(($featuredProduct->display_image ?? $featuredProduct->image) || $featuredProduct->video)
                @include('themes.partials.product-inline-thumb', ['product' => $featuredProduct])
            @else
                <div class="wn-modern-card__placeholder wn-modern-card__placeholder--featured">@include('themes.partials.icons.svg-utensils')</div>
            @endif
            <div class="wn-menu-spotlight__gradient"></div>
            <div class="wn-menu-spotlight__content">
                @if($showLogo)
                    <div class="wn-menu-spotlight__brand">
                        @include('themes.partials.logo-chip', ['company' => $company, 'shape' => $logoShape, 'size' => 'sm'])
                    </div>
                @endif
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
@elseif($preset === 'minimal_bar')
    {{-- Cabecera renderizada en modern-header --}}
@elseif($preset === 'typographic_dark')
    <header class="wn-menu-hero wn-menu-hero--preset-typographic{{ $bleed ? ' wn-menu-hero--bleed' : '' }}{{ $heroModifiers }}" style="--wn-hero-image: url('{{ $heroUrl }}');">
        <div class="wn-menu-hero__overlay wn-menu-hero__overlay--dynamic">
            <div class="wn-menu-hero__typographic">
                @if($showLogo)
                    @include('themes.partials.logo-chip', ['company' => $company, 'shape' => $logoShape, 'size' => 'sm'])
                @endif
                <p class="wn-menu-hero__brand-label">{{ strtoupper($company->name) }}</p>
                <h1 class="wn-menu-hero__title">{{ $company->name }}</h1>
                @if($company->comments)
                    <p class="wn-menu-hero__subtitle">{{ $company->comments }}</p>
                @endif
            </div>
        </div>
    </header>
@else
    @php
        $presetClass = 'wn-menu-hero--preset-' . str_replace('_', '-', $preset);
        $shapeClass = $logoShape === 'circle' ? ' wn-menu-hero--circle' : '';
    @endphp
    <section class="wn-menu-hero {{ $presetClass }}{{ $shapeClass }}{{ $bleed ? ' wn-menu-hero--bleed' : '' }}{{ $heroModifiers }}" style="--wn-hero-image: url('{{ $heroUrl }}');">
        <div class="wn-menu-hero__overlay wn-menu-hero__overlay--dynamic">
            <div class="wn-menu-hero__brand">
                @if($showLogo)
                    @include('themes.partials.logo-chip', [
                        'company' => $company,
                        'shape' => $logoShape,
                        'size' => $logoShape === 'circle' ? 'md' : 'md',
                        'fallbackUrl' => $logoShape === 'circle' ? \App\PlatformSetting::brandUrl('logo') : null,
                    ])
                @endif
                <div class="wn-menu-hero__brand-text">
                    @if($showChef && $company->chef_name)
                        <p class="wn-menu-hero__eyebrow">{{ strtoupper($company->chef_name) }}</p>
                    @endif
                    <h1 class="wn-menu-hero__title">{{ $company->name }}</h1>
                    @if($company->comments)
                        <p class="wn-menu-hero__subtitle">{{ $company->comments }}</p>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
