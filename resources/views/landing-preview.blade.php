<!DOCTYPE html>
<html class="scroll-smooth" lang="{{ str_replace('_', '-', $locale ?? app()->getLocale()) }}">
<head>
    @include('landing.partials.head')
</head>
<body class="bg-background text-on-surface text-body-md">
@php
    $isLoggedIn = auth()->check();
    $loginUrl = route('login');
    $registerUrl = route('register');
    $panelUrl = $panelUrl ?? route('admin.dashboard');
    $settingsUrl = $settingsUrl ?? route('admin.settings');
    $logoutUrl = $logoutUrl ?? route('logout');
    $contactPublicEmail = $contactPublicEmail ?? 'hello@webnu.es';
    $demoUrl = url('/carta/demo?lang=en');
    $demoShowcases = $demoShowcases ?? [];
    $tvpikSlides = $tvpikSlides ?? [];
    $templateCount = $templateCount ?? 14;
    $landingReelVideo = $landingReelVideo ?? asset('img/demo/reel-grill-chicken.mp4');
    $landingStats = __('landing.stats');
    // Garantizamos siempre 20+ plantillas para no parecer escasos
    // aunque el catálogo real tenga menos en un momento dado.
    if (isset($landingStats[2])) {
        $landingStats[2]['value'] = max(20, (int) $templateCount) . '+';
    }
@endphp

<nav data-landing-nav class="sticky top-0 z-50 flex justify-between items-center w-full px-margin-mobile md:px-gutter max-w-container-max mx-auto h-20 bg-surface-container-lowest border-b border-border-subtle transition-shadow">
    <a href="#inicio" class="inline-flex items-center shrink-0" title="Webnu">
        @include('partials.brand-logo', ['brandKey' => 'logo', 'brandClass' => 'landing-brand-logo'])
    </a>
    <div class="hidden md:flex items-center gap-8">
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#demos-carta">{{ __('landing.nav.examples') }}</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#funciones">{{ __('landing.nav.features') }}</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#reels">{{ __('landing.nav.reels') }}</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#tvpik">{{ __('landing.nav.tvpik') }}</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#process">{{ __('landing.nav.scan') }}</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#pricing">{{ __('landing.nav.pricing') }}</a>
    </div>
    <div class="flex items-center gap-3">
        @include('landing.partials.language-selector')
        @if($isLoggedIn)
            @include('landing.partials.user-menu')
        @else
            <a href="{{ $loginUrl }}" class="px-5 py-2 rounded-lg bg-primary-container text-on-primary text-label-md hover:opacity-90 transition-opacity font-medium">{{ __('landing.nav.login') }}</a>
        @endif
    </div>
</nav>

<main class="max-w-container-max mx-auto px-margin-mobile md:px-gutter">
    {{-- Hero — Webnu carta digital. Doble texto rotativo en azul + mockup móvil. --}}
    @php
        $businessCycle = (array) __('landing.hero.business_cycle');
        $featureCycle = (array) __('landing.hero.feature_cycle');
        $businessFirst = $businessCycle[0] ?? 'restaurante';
        $featureFirst = $featureCycle[0] ?? 'traduce automáticamente';
        // Palabra/frase más larga de cada set — la usamos como measure invisible
        // para reservar el ancho del wrapper y evitar saltos del texto vecino.
        $businessLongest = collect($businessCycle)->sortByDesc(fn ($w) => mb_strlen($w))->first() ?? $businessFirst;
        $featureLongest = collect($featureCycle)->sortByDesc(fn ($w) => mb_strlen($w))->first() ?? $featureFirst;
        $phoneHero = asset('img/productos/cocktail-negroni.jpg');
    @endphp
    <section id="inicio" class="pt-8 pb-12 md:py-24 grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-16 items-center">
        <div class="space-y-5 md:space-y-7">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 border border-primary/20 text-label-sm font-bold uppercase tracking-wider text-primary">
                <span class="material-symbols-outlined text-[16px]">bolt</span>
                {{ __('landing.hero.badge_pill') }}
            </span>
            <h1 class="font-headline text-[2.25rem] sm:text-headline-xl md:text-[3.25rem] text-on-surface leading-[1.1] md:leading-[1.08]">
                {{ __('landing.hero.title_lead') }}
                <span class="hero-cycle hero-cycle--typewriter" data-cycle="business" data-cycle-mode="typewriter" data-cycle-items='@json($businessCycle)'>
                    <span class="hero-cycle__measure" aria-hidden="true">{{ $businessLongest }}</span>
                    <span class="hero-cycle__layer">
                        <span class="hero-cycle__text">{{ $businessFirst }}</span><span class="hero-cycle__cursor" aria-hidden="true"></span>
                    </span>
                </span>
                {{ __('landing.hero.title_tail') }}
            </h1>
            <p class="text-body-lg md:text-headline-sm text-text-muted">
                {{ __('landing.hero.platform_lead') }}
                <span class="hero-cycle hero-cycle--slide hero-cycle--feature" data-cycle="feature" data-cycle-mode="slide" data-cycle-items='@json($featureCycle)'>
                    <span class="hero-cycle__measure" aria-hidden="true">{{ $featureLongest }}</span>
                    <span class="hero-cycle__item is-active">{{ $featureFirst }}</span>
                </span>
            </p>
            <p class="text-body-md text-text-muted max-w-xl">
                {{ __('landing.hero.description_short') }}
            </p>
            <div class="flex flex-wrap items-center gap-4">
                @if($isLoggedIn)
                    <a href="{{ $panelUrl }}" class="inline-flex items-center gap-2 px-7 py-4 bg-primary text-on-primary text-label-md rounded-lg hover:opacity-90 transition-opacity font-semibold">
                        {{ __('landing.hero.logged_cta') }} <span class="material-symbols-outlined text-[20px]">dashboard</span>
                    </a>
                @else
                    <a href="{{ $registerUrl }}" class="inline-flex items-center gap-2 px-7 py-4 bg-primary text-on-primary text-label-md rounded-lg hover:opacity-90 transition-opacity font-semibold">
                        {{ __('landing.hero.cta_main_short') }} <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </a>
                @endif
                <a href="#demos-carta" class="inline-flex items-center gap-2 px-6 py-4 rounded-lg bg-surface-container-lowest border border-border-subtle text-label-md text-on-surface font-semibold hover:border-primary/40 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[20px]">play_circle</span>
                    {{ __('landing.hero.cta_demos_short') }}
                </a>
            </div>
            <div class="flex items-center gap-4 pt-2">
                <div class="flex -space-x-3">
                    <span class="w-10 h-10 rounded-full border-2 border-surface bg-primary-container flex items-center justify-center text-on-primary text-label-sm font-bold">QR</span>
                    <span class="w-10 h-10 rounded-full border-2 border-surface bg-surface-container flex items-center justify-center text-primary text-label-sm font-bold">IA</span>
                    <span class="w-10 h-10 rounded-full border-2 border-surface bg-surface-container-high flex items-center justify-center text-primary text-label-sm font-bold">+</span>
                </div>
                <span class="text-label-md text-text-muted">{{ __('landing.hero.social_proof') }}</span>
            </div>
        </div>

        {{-- Mockup móvil con cocktail Negroni + chips flotantes --}}
        <div class="hero-phone-stage relative mx-auto w-full max-w-[360px]">
            <div class="hero-phone" aria-hidden="true">
                <div class="hero-phone__notch"></div>
                <div class="hero-phone__status">
                    <span class="hero-phone__status-time">9:41</span>
                    <span class="hero-phone__status-icons">
                        <span class="material-symbols-outlined">network_wifi</span>
                        <span class="material-symbols-outlined">battery_full</span>
                    </span>
                </div>
                <figure class="hero-phone__hero">
                    <img src="{{ $phoneHero }}" alt="" loading="lazy" />
                    <figcaption>
                        <span class="hero-phone__hero-name">{{ __('landing.hero.phone_brand') }}</span>
                        <span class="hero-phone__hero-tag">{{ __('landing.hero.phone_subtitle') }}</span>
                    </figcaption>
                </figure>
                <div class="hero-phone__body">
                    <div class="hero-phone__section-head">
                        <span class="hero-phone__section-title">{{ __('landing.hero.phone_section') }}</span>
                        <span class="hero-phone__lang-chip">
                            <span class="material-symbols-outlined">translate</span>
                            {{ __('landing.hero.phone_lang_chip') }}
                        </span>
                    </div>
                    <article class="hero-phone__item">
                        <div class="hero-phone__item-media">
                            <img src="{{ $phoneHero }}" alt="" loading="lazy" />
                            <span class="hero-phone__item-play" aria-hidden="true">
                                <span class="material-symbols-outlined">play_arrow</span>
                            </span>
                        </div>
                        <div class="hero-phone__item-body">
                            <p class="hero-phone__item-name">{{ __('landing.hero.phone_item1_name') }}</p>
                            <p class="hero-phone__item-desc">{{ __('landing.hero.phone_item1_desc') }}</p>
                        </div>
                    </article>
                    <article class="hero-phone__item">
                        <div class="hero-phone__item-media hero-phone__item-media--placeholder"></div>
                        <div class="hero-phone__item-body">
                            <div class="hero-phone__item-row">
                                <p class="hero-phone__item-name">{{ __('landing.hero.phone_item2_name') }}</p>
                                <span class="hero-phone__item-price">{{ __('landing.hero.phone_item2_price') }}</span>
                            </div>
                            <p class="hero-phone__item-desc">{{ __('landing.hero.phone_item2_desc') }}</p>
                        </div>
                    </article>
                </div>
            </div>
            <div class="hero-phone-chip hero-phone-chip--ticket" aria-hidden="true">
                <span class="hero-phone-chip__icon"><span class="material-symbols-outlined">trending_up</span></span>
                <span class="hero-phone-chip__body">
                    <span class="hero-phone-chip__label">{{ __('landing.hero.phone_ticket_label') }}</span>
                    <span class="hero-phone-chip__value">{{ __('landing.hero.phone_ticket_value') }}</span>
                </span>
            </div>
            <div class="hero-phone-chip hero-phone-chip--scan" aria-hidden="true">
                <span class="hero-phone-chip__icon hero-phone-chip__icon--accent"><span class="material-symbols-outlined">bolt</span></span>
                <span>{{ __('landing.hero.phone_chip_scan') }}</span>
            </div>
            <div class="hero-phone-chip hero-phone-chip--translation" aria-hidden="true">
                <span class="hero-phone-chip__icon hero-phone-chip__icon--accent"><span class="material-symbols-outlined">translate</span></span>
                <span>{{ __('landing.hero.phone_chip_translation') }}</span>
            </div>
        </div>
    </section>

    {{-- Cartas reales + Estudio visual unificados --}}
    <section id="demos-carta" class="py-12 md:py-24">
        <div class="text-center mb-12 max-w-3xl mx-auto">
            <span class="inline-flex items-center gap-2 bg-primary/10 text-primary px-3 py-1 rounded-full text-label-sm font-bold uppercase tracking-wider mb-3">
                <span>{{ __('landing.demos.badge') }}</span>
                <span aria-hidden="true" class="opacity-40">·</span>
                <span>{{ __('landing.customize.badge') }}</span>
            </span>
            <h2 class="font-headline text-headline-xl mb-4">{{ __('landing.demos.title') }}</h2>
            <p class="text-body-lg text-text-muted">
                {{ __('landing.demos.subtitle') }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
            @foreach($demoShowcases as $demo)
                <article class="rounded-2xl border-2 {{ $demo['accent'] }} overflow-hidden flex flex-col hover:shadow-lg transition-shadow">
                    <div class="aspect-[16/10] overflow-hidden bg-surface-container">
                        <img src="{{ $demo['preview'] }}" alt="{{ $demo['subtitle'] }}" class="w-full h-full object-cover" loading="lazy"/>
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <span class="text-label-sm font-bold text-primary uppercase tracking-wide">{{ $demo['badge'] }}</span>
                        <h3 class="font-headline text-headline-md mt-1 mb-1">{{ $demo['title'] }}</h3>
                        <p class="text-label-md text-text-muted mb-3">{{ $demo['subtitle'] }}</p>
                        <p class="text-body-md text-text-muted mb-4 flex-grow">{{ $demo['desc'] }}</p>
                        <div class="flex flex-wrap gap-2 mb-5">
                            @foreach($demo['tags'] as $tag)
                                <span class="px-2.5 py-1 rounded-full bg-surface-container-high text-label-sm text-on-surface-variant">{{ $tag }}</span>
                            @endforeach
                        </div>
                        <a href="{{ $demo['url'] }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:opacity-90 transition-opacity">
                            {{ __('landing.demos.cta') }} <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Mini bloque "estudio visual": prueba a personalizar en vivo --}}
        <div id="personalizable"
             class="mt-12 md:mt-16 grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)] gap-10 lg:gap-14 items-center max-w-5xl mx-auto"
             data-customize-presets='@json($landingCustomizePresets ?? [])'>
            <div class="landing-customize-wrap order-2 lg:order-1">
                <div id="customize-phone" class="landing-customize-phone" aria-hidden="true">
                    <div class="landing-customize-phone__status">
                        <span></span><span></span><span></span>
                    </div>
                    <div id="customize-header" class="landing-customize-phone__header">
                        <span id="customize-business" class="landing-customize-phone__business">La Brasa del Puerto</span>
                        <span id="customize-template" class="landing-customize-phone__tpl">Básica</span>
                    </div>
                    <div class="landing-customize-phone__section" id="customize-section">Carta · Principales</div>
                    <article class="landing-customize-phone__card">
                        <div class="landing-customize-phone__thumb"></div>
                        <div class="landing-customize-phone__info">
                            <div class="landing-customize-phone__row">
                                <span id="customize-dish" class="landing-customize-phone__dish">Solomillo al Pedro Ximénez</span>
                                <span id="customize-price" class="landing-customize-phone__price">24,50 €</span>
                            </div>
                            <p id="customize-desc" class="landing-customize-phone__desc">Reducción de Pedro Ximénez y patata confitada.</p>
                        </div>
                    </article>
                </div>
                <div class="landing-customize-controls">
                    <div class="landing-customize-controls__row">
                        <span class="landing-customize-controls__label"><span class="material-symbols-outlined text-[16px]">palette</span> {{ __('landing.customize.color') }}</span>
                        <div class="landing-customize-swatches" id="customize-swatches"></div>
                    </div>
                    <div class="landing-customize-controls__row">
                        <span class="landing-customize-controls__label"><span class="material-symbols-outlined text-[16px]">edit</span> {{ __('landing.customize.text') }}</span>
                        <span id="customize-hint" class="landing-customize-hint">{{ ($landingCustomizePresets[0] ?? [])['hint'] ?? '' }}</span>
                    </div>
                </div>
            </div>

            <div class="space-y-5 order-1 lg:order-2">
                <h3 class="font-headline text-headline-md md:text-headline-lg">{{ __('landing.customize.title') }}</h3>
                <p class="text-body-md text-text-muted">{{ __('landing.customize.desc') }}</p>
                <ul class="grid grid-cols-1 gap-2 text-label-md text-text-muted">
                    @foreach(__('landing.customize.bullets') as $bullet)
                        <li class="flex gap-2 items-start">
                            <span class="material-symbols-outlined text-primary text-[18px] shrink-0">check_circle</span>
                            <span>{{ $bullet }}</span>
                        </li>
                    @endforeach
                </ul>
                <a href="#inicio" class="inline-flex items-center gap-2 px-5 py-3 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:opacity-90 transition-opacity">
                    {{ __('landing.customize.cta') }} <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                </a>
            </div>
        </div>
    </section>

    {{-- Funciones · slider compacto, las más llamativas primero --}}
    <section id="funciones" class="py-12 md:py-20 mb-4">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-6">
            <div class="max-w-2xl">
                <span class="inline-block bg-primary/10 text-primary px-3 py-1 rounded-full text-label-sm font-bold uppercase tracking-wider mb-3">{{ __('landing.features.badge') }}</span>
                <h2 class="font-headline text-headline-lg md:text-headline-xl mb-2">{{ __('landing.features.title') }}</h2>
                <p class="text-body-md text-text-muted">{{ __('landing.features.subtitle') }}</p>
            </div>
            <span class="hidden md:inline-flex items-center gap-1 text-label-sm text-text-muted">
                <span class="material-symbols-outlined text-[18px]">swipe</span>
                {{ __('landing.features.swipe_hint') }}
            </span>
        </div>

        @php $featuresList = $landingFeatures ?? []; @endphp

        <div class="wn-feat-slider-wrap" data-feat-slider>
            <button type="button"
                    class="wn-feat-slider__arrow wn-feat-slider__arrow--prev"
                    data-feat-prev
                    aria-label="{{ __('landing.features.nav_prev') }}"
                    aria-controls="wn-feat-slider">
                <span class="material-symbols-outlined">chevron_left</span>
            </button>

            <div id="wn-feat-slider" class="wn-feat-slider" role="list" data-feat-track>
                @foreach($featuresList as $i => $feat)
                    @php
                        $isHighlight = ! empty($feat['highlight']);
                        $planLabel = ! empty($feat['plan'])
                            ? ($feat['plan'] === 'plus' ? __('landing.features.plan_plus') : __('landing.features.plan_pro'))
                            : null;
                    @endphp
                    <article role="listitem"
                             class="wn-feat-card {{ $isHighlight ? 'wn-feat-card--highlight' : '' }}"
                             data-feat-slide="{{ $i }}">
                        @if($planLabel)
                            <span class="wn-feat-card__plan">{{ $planLabel }}</span>
                        @endif
                        <span class="wn-feat-card__icon">
                            <span class="material-symbols-outlined text-[20px]">{{ $feat['icon'] }}</span>
                        </span>
                        <h3 class="wn-feat-card__title">{{ $feat['t'] }}</h3>
                        <p class="wn-feat-card__desc">{{ $feat['d'] }}</p>
                        @if(! empty($feat['free_note']))
                            <span class="text-label-sm font-semibold {{ $isHighlight ? 'text-white/90' : 'text-secondary-container' }} mt-1">{{ $feat['free_note'] }}</span>
                        @endif
                    </article>
                @endforeach
            </div>

            <button type="button"
                    class="wn-feat-slider__arrow wn-feat-slider__arrow--next"
                    data-feat-next
                    aria-label="{{ __('landing.features.nav_next') }}"
                    aria-controls="wn-feat-slider">
                <span class="material-symbols-outlined">chevron_right</span>
            </button>
        </div>

        @if(count($featuresList) > 1)
            <div class="wn-feat-slider__dots" role="tablist" aria-label="{{ __('landing.features.nav_dots') }}" data-feat-dots>
                @foreach($featuresList as $i => $feat)
                    <button type="button"
                            class="wn-feat-slider__dot {{ $i === 0 ? 'is-active' : '' }}"
                            data-feat-dot="{{ $i }}"
                            role="tab"
                            aria-selected="{{ $i === 0 ? 'true' : 'false' }}"
                            aria-label="{{ $feat['t'] }}"></button>
                @endforeach
            </div>
        @endif

        <div class="mt-10 bg-surface-container border border-border-subtle rounded-2xl p-6 md:p-8 flex flex-col md:flex-row gap-6 items-start md:items-center">
            <div class="w-12 h-12 rounded-2xl bg-primary-container text-on-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[24px]">forum</span>
            </div>
            <div class="flex-1 space-y-1">
                <h3 class="font-headline text-headline-sm md:text-headline-md">{{ __('landing.features.feedback_title') }}</h3>
                <p class="text-body-md text-text-muted max-w-2xl">{{ __('landing.features.feedback_desc') }}</p>
            </div>
            <button type="button" id="suggestion-open" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:opacity-90 transition-opacity shrink-0 whitespace-nowrap">
                <span class="material-symbols-outlined text-[20px]">lightbulb</span>
                {{ __('landing.features.feedback_cta') }}
            </button>
        </div>
    </section>

    {{-- Reels --}}
    <section id="reels" class="py-12 md:py-24">
        <div class="text-center mb-14">
            <h2 class="font-headline text-headline-xl mb-4">{{ __('landing.reels.title') }}</h2>
            <p class="text-body-lg text-text-muted max-w-2xl mx-auto">
                {{ __('landing.reels.subtitle') }} <span class="text-primary font-medium">{{ __('landing.reels.plus_note') }}</span>
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div class="flex justify-center">
                <div class="landing-menu-mock w-full max-w-sm">
                    <div class="landing-menu-mock__chrome">
                        <span class="landing-menu-mock__dot"></span>
                        <span class="landing-menu-mock__dot"></span>
                        <span class="landing-menu-mock__dot"></span>
                        <span class="landing-menu-mock__title">{{ __('landing.reels.mock_section') }}</span>
                    </div>
                    <div class="landing-menu-mock__body">
                        <article class="landing-menu-card">
                            <div class="landing-menu-card__media landing-menu-card__media--reel">
                                <video class="landing-menu-card__reel" autoplay muted loop playsinline preload="metadata" poster="{{ asset('img/productos/brasa-solomillo.jpg') }}">
                                    <source src="{{ $landingReelVideo }}" type="video/mp4"/>
                                </video>
                                <span class="landing-menu-card__badge"><i class="material-symbols-outlined text-[14px]">videocam</i> Reel</span>
                            </div>
                            <div class="landing-menu-card__content">
                                <div class="landing-menu-card__head">
                                    <h4>{{ __('landing.reels.dish_name') }}</h4>
                                    <span class="landing-menu-card__price">{{ __('landing.reels.dish_price') }}</span>
                                </div>
                                <p>{{ __('landing.reels.dish_desc') }}</p>
                            </div>
                        </article>
                        <article class="landing-menu-card landing-menu-card--photo">
                            <div class="landing-menu-card__media">
                                <img src="{{ asset('img/productos/brasa-burrata.jpg') }}" alt="Ensalada de burrata" loading="lazy"/>
                            </div>
                            <div class="landing-menu-card__content">
                                <div class="landing-menu-card__head">
                                    <h4>{{ __('landing.reels.dish2_name') }}</h4>
                                    <span class="landing-menu-card__price">{{ __('landing.reels.dish2_price') }}</span>
                                </div>
                                <p>{{ __('landing.reels.dish2_desc') }}</p>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
            <div class="space-y-8">
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined">trending_up</span>
                    </div>
                    <div>
                        <h4 class="font-headline text-headline-md mb-2">{{ __('landing.reels.benefit1_title') }}</h4>
                        <p class="text-text-muted">{{ __('landing.reels.benefit1_desc') }}</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined">restaurant</span>
                    </div>
                    <div>
                        <h4 class="font-headline text-headline-md mb-2">{{ __('landing.reels.benefit2_title') }}</h4>
                        <p class="text-text-muted">{!! __('landing.reels.benefit2_desc', ['cocktails' => '<a href="'.e($demoCocktailsUrl ?? url('/carta/demo-cocktails')).'" target="_blank" class="text-primary font-medium hover:underline">Azul Coctelería</a>']) !!}</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined">speed</span>
                    </div>
                    <div>
                        <h4 class="font-headline text-headline-md mb-2">{{ __('landing.reels.benefit3_title') }}</h4>
                        <p class="text-text-muted">{{ __('landing.reels.benefit3_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('landing.partials.tvpik-section', ['tvpikSlides' => $tvpikSlides])

    {{-- Reseñas + métricas unificadas --}}
    <section class="py-12 md:py-16 mb-4 md:mb-8">
        <div class="text-center mb-10 md:mb-12">
            <h2 class="font-headline text-headline-xl">{{ __('landing.testimonials.title') }}</h2>
            @if(! empty($landingStats))
                <ul class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-6 max-w-4xl mx-auto" aria-label="Métricas">
                    @foreach($landingStats as $stat)
                        <li class="flex flex-col items-center gap-1">
                            <span class="font-headline text-headline-lg text-primary leading-none">{{ $stat['value'] }}</span>
                            <span class="text-label-md text-text-muted">{{ $stat['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($landingTestimonials ?? [] as $t)
                <div class="bg-surface-container-lowest p-8 rounded-xl border border-border-subtle">
                    <div class="flex gap-1 text-primary mb-4">
                        @for($i = 0; $i < 5; $i++)<span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1">star</span>@endfor
                    </div>
                    <p class="text-body-md text-on-surface-variant italic mb-6">"{{ $t['q'] }}"</p>
                    <p class="font-label-md text-on-surface">{{ $t['n'] }}</p>
                    <p class="text-label-sm text-text-muted">{{ $t['r'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Escaneo IA — unificado en 3 pasos reales --}}
    @php
        $steps = $landingSteps ?? [];
        $asideBullets = (array) __('landing.process.aside_bullets');
    @endphp
    <section id="process" class="py-12 md:py-20">
        <div class="text-center mb-10 md:mb-12 max-w-3xl mx-auto">
            <span class="inline-block bg-primary/10 text-primary px-3 py-1 rounded-full text-label-sm font-bold uppercase tracking-wider mb-3">{{ __('landing.process.badge') }}</span>
            <h2 class="font-headline text-headline-lg md:text-headline-xl">{{ __('landing.process.title') }}</h2>
            <p class="text-body-md text-text-muted mt-2">{{ __('landing.process.subtitle') }} <span class="text-primary font-semibold">{{ __('landing.process.plus_note') }}</span></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-6 lg:gap-10 relative max-w-6xl mx-auto">
            <div class="hidden md:block absolute top-7 left-[16.66%] right-[16.66%] h-0.5 border-t-2 border-dashed border-outline-variant -z-10" aria-hidden="true"></div>

            {{-- Paso 1: Foto o PDF con demo del escaneo --}}
            @if(! empty($steps[0]))
                <div class="text-center md:text-left space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-primary text-on-primary flex items-center justify-center mx-auto md:mx-0 shadow-sm">
                        <span class="material-symbols-outlined text-[28px]">{{ $steps[0]['icon'] }}</span>
                    </div>
                    <div>
                        <h3 class="font-headline text-headline-sm mb-1.5">{{ $steps[0]['t'] }}</h3>
                        <p class="text-body-md text-text-muted">{{ $steps[0]['d'] }}</p>
                    </div>
                    <div class="landing-scan-demo landing-scan-demo--compact" aria-hidden="true">
                        <div class="landing-scan-demo__frame">
                            <p class="landing-scan-demo__section">{{ __('landing.process.scan_demo.section_starters') }}</p>
                            <div class="landing-scan-demo__row"><span>{{ __('landing.process.scan_demo.item1') }}</span><strong>{{ __('landing.process.scan_demo.price1') }}</strong></div>
                            <div class="landing-scan-demo__row"><span>{{ __('landing.process.scan_demo.item2') }}</span><strong>{{ __('landing.process.scan_demo.price2') }}</strong></div>
                            <p class="landing-scan-demo__section landing-scan-demo__section--spaced">{{ __('landing.process.scan_demo.section_mains') }}</p>
                            <div class="landing-scan-demo__row"><span>{{ __('landing.process.scan_demo.item3') }}</span><strong>{{ __('landing.process.scan_demo.price3') }}</strong></div>
                            <div class="landing-scan-demo__row"><span>{{ __('landing.process.scan_demo.item4') }}</span><strong>{{ __('landing.process.scan_demo.price4') }}</strong></div>
                            <div class="landing-scan-demo__scanline" aria-hidden="true"></div>
                        </div>
                    </div>
                    <p class="sr-only">{{ __('landing.process.scan_demo.aria') }}</p>
                </div>
            @endif

            {{-- Paso 2: Procesado IA con viñetas "¿Qué hace la IA?" --}}
            @if(! empty($steps[1]))
                <div class="text-center md:text-left space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-primary text-on-primary flex items-center justify-center mx-auto md:mx-0 shadow-sm">
                        <span class="material-symbols-outlined text-[28px]">{{ $steps[1]['icon'] }}</span>
                    </div>
                    <div>
                        <h3 class="font-headline text-headline-sm mb-1.5">{{ $steps[1]['t'] }}</h3>
                        <p class="text-body-md text-text-muted">{{ $steps[1]['d'] }}</p>
                    </div>
                    @if(! empty($asideBullets))
                        <ul class="wn-scan-aside__list wn-scan-aside__list--inline">
                            @foreach($asideBullets as $bullet)
                                <li><span class="material-symbols-outlined">check_circle</span><span>{{ $bullet }}</span></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            {{-- Paso 3: Listo para servir con CTA principal --}}
            @if(! empty($steps[2]))
                <div class="text-center md:text-left space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-primary text-on-primary flex items-center justify-center mx-auto md:mx-0 shadow-sm">
                        <span class="material-symbols-outlined text-[28px]">{{ $steps[2]['icon'] }}</span>
                    </div>
                    <div>
                        <h3 class="font-headline text-headline-sm mb-1.5">{{ $steps[2]['t'] }}</h3>
                        <p class="text-body-md text-text-muted">{{ $steps[2]['d'] }}</p>
                    </div>
                    <a href="{{ $isLoggedIn ? $panelUrl : $registerUrl }}" class="wn-scan-cta wn-scan-cta--block" id="scan-main-cta">
                        <span class="wn-scan-cta__icon"><span class="material-symbols-outlined text-[20px]">photo_camera</span></span>
                        <span>{{ __('landing.process.cta_main') }}</span>
                        <span class="material-symbols-outlined text-[22px]">arrow_forward</span>
                    </a>
                    <p class="text-label-sm text-text-muted">{{ __('landing.process.cta_main_note') }}</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Precios (Free, Pro, Plus) desde config/plans.php --}}
    @php
        $landingPricingPlans = $landingPricingPlans ?? [];
        $landingPricingTierOrder = $landingPricingTierOrder ?? ['free', 'pro', 'plus'];
        $landingFranchisePlan = $landingFranchisePlan ?? null;
    @endphp
    <section id="pricing" class="py-12 md:py-24">
        <div class="text-center mb-12">
            <h2 class="font-headline text-headline-xl mb-4">{{ __('landing.pricing.title') }}</h2>
            <p class="text-body-lg text-text-muted max-w-2xl mx-auto">{{ __('landing.pricing.subtitle') }}</p>
        </div>
        @php
            $planCount = max(1, count($landingPricingPlans));
            $gridCols = $planCount >= 4
                ? 'sm:grid-cols-2 lg:grid-cols-4'
                : ($planCount === 3 ? 'md:grid-cols-3' : 'md:grid-cols-' . $planCount);
        @endphp
        <div class="grid grid-cols-1 {{ $gridCols }} gap-5 md:gap-6 items-stretch max-w-6xl mx-auto">
            @foreach($landingPricingPlans as $plan)
                @php
                    $isHighlight = ! empty($plan['highlight']);
                    $isFranchise = ($plan['id'] ?? '') === 'franchise' || ($plan['price'] ?? '') === 'A medida';
                @endphp
                <div class="bg-surface-container-lowest {{ $isHighlight ? 'border-2 border-primary-container lg:-translate-y-2 shadow-lg' : 'border border-border-subtle' }} p-6 md:p-7 rounded-2xl flex flex-col relative">
                    @if(! empty($plan['badge']))
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary-container text-on-primary px-4 py-1 rounded-full text-label-sm font-semibold whitespace-nowrap shadow">{{ $plan['badge'] }}</span>
                    @endif
                    <span class="text-label-sm uppercase tracking-wider font-bold text-text-muted">{{ strtoupper($plan['name']) }}</span>
                    <div class="mt-2 mb-1">
                        @if($isFranchise)
                            <span class="font-headline text-[2rem] font-bold">{{ $plan['price'] }}</span>
                        @else
                            <span class="font-headline text-[2.5rem] leading-none font-extrabold">{{ $plan['price'] }}</span><span class="text-text-muted text-headline-sm font-semibold">€</span>
                        @endif
                    </div>
                    <p class="text-label-sm text-text-muted mb-3">{{ $plan['period'] }}</p>
                    <p class="text-body-md text-text-muted mb-5 leading-relaxed">{{ $plan['tagline'] }}</p>
                    <ul class="space-y-2 mb-6 flex-grow text-body-md">
                        @foreach($plan['features'] as $feature)
                            @php
                                $plainFeature = trim(strip_tags($feature));
                                $isNegative = (bool) preg_match('/^(sin|no\s|without|no\s+)/i', $plainFeature);
                            @endphp
                            <li class="flex gap-2 items-start">
                                <span class="material-symbols-outlined text-[18px] shrink-0 {{ $isNegative ? 'text-text-muted' : 'text-success-green' }}" style="font-variation-settings: 'FILL' 1;">{{ $isNegative ? 'close' : 'check_circle' }}</span>
                                <span class="{{ $isNegative ? 'text-text-muted' : '' }}">{!! $feature !!}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ $plan['cta_url'] ?? $registerUrl }}" class="w-full rounded-lg text-center text-label-md font-semibold transition-colors {{ $isHighlight ? 'py-3 bg-primary-container text-on-primary hover:opacity-90' : 'py-3 border border-border-subtle hover:bg-surface-container' }}">{{ $plan['cta'] }}</a>
                </div>
            @endforeach
        </div>

        @if(!empty($landingFranchisePlan))
            <div class="mt-8 max-w-3xl mx-auto bg-surface-container-lowest border border-border-subtle rounded-xl p-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <h3 class="font-headline text-headline-md mb-1">{{ $landingFranchisePlan['name'] }}</h3>
                    <p class="text-label-sm text-text-muted mb-3">{{ $landingFranchisePlan['tagline'] }}</p>
                    <ul class="space-y-2 text-label-md">
                        @foreach($landingFranchisePlan['features'] as $feature)
                            <li class="flex gap-2 items-start">
                                <span class="material-symbols-outlined text-primary text-[18px] shrink-0">check_circle</span>
                                <span>{!! $feature !!}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="text-center md:text-right shrink-0">
                    <p class="text-2xl font-bold mb-3">{{ $landingFranchisePlan['price'] }}</p>
                    <a href="{{ $landingFranchisePlan['cta_url'] }}" class="inline-block py-3 px-6 border border-primary text-primary font-semibold rounded-lg hover:bg-primary/5">{{ $landingFranchisePlan['cta'] }}</a>
                </div>
            </div>
        @endif

        <p class="text-center text-label-sm text-text-muted mt-8 max-w-xl mx-auto">{{ __('landing.pricing.footnote') }}</p>
    </section>

    {{-- FAQ --}}
    <section class="py-12 md:py-16 max-w-3xl mx-auto">
        <h2 class="text-center font-headline text-headline-xl mb-8 md:mb-10">{{ __('landing.faq.title') }}</h2>
        <div class="space-y-3">
            @foreach($landingFaq ?? [] as $i => $faq)
                <div class="faq-item border border-border-subtle rounded-xl overflow-hidden {{ $i === 0 ? 'faq-open' : '' }}">
                    <button type="button" class="w-full p-5 flex justify-between items-center text-left hover:bg-surface-container-low transition-colors font-headline text-headline-md" onclick="toggleFAQ(this)">
                        {{ $faq['q'] }}
                        <span class="material-symbols-outlined faq-icon transition-transform">expand_more</span>
                    </button>
                    <div class="faq-content px-5">
                        <p class="pb-5 text-text-muted">{{ $faq['a'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- CTA final --}}
    <section id="contacto" class="py-12 md:py-16 mb-12 md:mb-20">
        <div class="bg-primary rounded-[2rem] p-8 md:p-16 text-center text-on-primary relative overflow-hidden">
            <div class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full -mr-24 -mt-24 blur-3xl"></div>
            <h2 class="font-headline text-headline-xl mb-6 relative z-10">{{ __('landing.cta.title') }}</h2>
            <p class="text-body-lg mb-8 opacity-90 max-w-xl mx-auto relative z-10">{{ __('landing.cta.subtitle') }}</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4 relative z-10">
                <a href="#inicio" class="px-10 py-4 bg-white text-primary font-bold rounded-xl hover:scale-[1.02] transition-transform">{{ __('landing.cta.primary') }}</a>
                <a href="{{ $demoUrl }}" target="_blank" class="px-10 py-4 border border-white/40 rounded-xl font-bold hover:bg-white/10 transition-colors">{{ __('landing.cta.secondary') }}</a>
            </div>
        </div>
    </section>
</main>

<footer class="bg-surface border-t border-border-subtle">
    <div class="max-w-container-max mx-auto px-margin-mobile md:px-gutter py-12 flex flex-col md:flex-row justify-between gap-10">
        <div class="max-w-sm space-y-4">
            <a href="#inicio" class="inline-block">
                @include('partials.brand-logo', ['brandKey' => 'logo', 'brandClass' => 'landing-brand-logo landing-brand-logo--footer'])
            </a>
            <p class="text-text-muted text-body-md">{{ __('landing.footer.tagline') }}</p>
            <p class="text-text-muted text-sm">© {{ date('Y') }} Webnu.es</p>
        </div>
        <div class="grid grid-cols-2 gap-10">
            <div>
                <h5 class="font-label-md font-semibold mb-3">{{ __('landing.footer.product') }}</h5>
                <ul class="space-y-2 text-text-muted text-sm">
                    <li><a href="#funciones" class="hover:text-primary">{{ __('landing.nav.features') }}</a></li>
                    <li><a href="#demos-carta" class="hover:text-primary">{{ __('landing.nav.examples') }}</a></li>
                    <li><a href="#reels" class="hover:text-primary">{{ __('landing.nav.reels') }}</a></li>
                    <li><a href="#tvpik" class="hover:text-primary">{{ __('landing.nav.tvpik') }}</a></li>
                    <li><a href="#process" class="hover:text-primary">{{ __('landing.nav.scan') }}</a></li>
                    <li><a href="#pricing" class="hover:text-primary">{{ __('landing.nav.pricing') }}</a></li>
                </ul>
            </div>
            <div>
                <h5 class="font-label-md font-semibold mb-3">{{ __('landing.footer.account') }}</h5>
                <ul class="space-y-2 text-text-muted text-sm">
                    @if($isLoggedIn)
                        <li><a href="{{ $panelUrl }}" class="hover:text-primary">{{ __('landing.nav.panel') }}</a></li>
                    @else
                        <li><a href="{{ $loginUrl }}" class="hover:text-primary">{{ __('landing.nav.login') }}</a></li>
                    @endif
                    <li><a href="#inicio" class="hover:text-primary">{{ __('landing.footer.home') }}</a></li>
                </ul>
            </div>
            @if(!empty($landingLocales))
                <div>
                    <h5 class="font-label-md font-semibold mb-3">{{ __('landing.nav.language') }}</h5>
                    @include('landing.partials.language-selector')
                </div>
            @endif
        </div>
    </div>
</footer>

<div id="suggestion-modal" class="landing-modal" hidden aria-hidden="true">
    <div class="landing-modal__backdrop" data-suggestion-close></div>
    <div class="landing-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="suggestion-modal-title">
        <button type="button" class="landing-modal__close" data-suggestion-close aria-label="{{ __('landing.suggestion.close') }}">
            <span class="material-symbols-outlined">close</span>
        </button>
        <div class="landing-modal__header">
            <span class="material-symbols-outlined landing-modal__icon">lightbulb</span>
            <h2 id="suggestion-modal-title" class="font-headline text-headline-md">{{ __('landing.suggestion.title') }}</h2>
            <p class="text-label-md text-text-muted">{{ __('landing.suggestion.desc') }}</p>
        </div>
        <form id="suggestion-form" action="{{ route('suggestion') }}" method="POST" class="landing-modal__form space-y-4">
            @csrf
            <div>
                <label for="suggestion-name" class="text-label-md text-on-surface-variant block mb-1">{{ __('landing.suggestion.name') }}</label>
                <input id="suggestion-name" name="name" required maxlength="255" class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary focus:border-primary outline-none" placeholder="{{ __('landing.suggestion.name_placeholder') }}"/>
            </div>
            <div>
                <label for="suggestion-email" class="text-label-md text-on-surface-variant block mb-1">{{ __('landing.suggestion.email') }}</label>
                <input id="suggestion-email" name="email" type="email" required maxlength="255" class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary focus:border-primary outline-none" placeholder="{{ __('landing.suggestion.email_placeholder') }}" autocomplete="email"/>
            </div>
            <div>
                <label for="suggestion-message" class="text-label-md text-on-surface-variant block mb-1">{{ __('landing.suggestion.message') }}</label>
                <textarea id="suggestion-message" name="message" required maxlength="3000" rows="4" class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary focus:border-primary outline-none resize-y min-h-[120px]" placeholder="{{ __('landing.suggestion.message_placeholder') }}"></textarea>
            </div>
            <p id="suggestion-error" class="text-label-sm text-red-600 hidden" role="alert"></p>
            <p id="suggestion-success" class="text-label-sm text-primary font-medium hidden" role="status"></p>
            <button type="submit" id="suggestion-submit" class="w-full py-3 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:opacity-90 transition-opacity flex items-center justify-center gap-2">
                {{ __('landing.suggestion.submit') }} <span class="material-symbols-outlined text-[20px]">send</span>
            </button>
        </form>
    </div>
</div>

<script src="{{ asset('js/landing-preview.js') }}"></script>
</body>
</html>
