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
    $heroHooks = $heroHooks ?? __('landing.hero.hooks');
    $demoShowcases = $demoShowcases ?? [];
    $tvpikSlides = $tvpikSlides ?? [];
    $templateCount = $templateCount ?? 14;
    $landingReelVideo = $landingReelVideo ?? asset('img/demo/reel-grill-chicken.mp4');
    $landingStats = __('landing.stats');
    if (!empty($templateCount)) {
        $landingStats[2]['value'] = $templateCount . '+';
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
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#tv-menus">{{ __('landing.nav.tv_menus') }}</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#tvpik">{{ __('landing.nav.tvpik') }}</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#process">{{ __('landing.nav.scan') }}</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#pricing">{{ __('landing.nav.pricing') }}</a>
    </div>
    <div class="flex items-center gap-3">
        @include('partials.pwa-landing-badge')
        @include('landing.partials.language-selector')
        @if($isLoggedIn)
            @include('landing.partials.user-menu')
        @else
            <a href="{{ $loginUrl }}" class="px-5 py-2 rounded-lg bg-primary-container text-on-primary text-label-md hover:opacity-90 transition-opacity font-medium">{{ __('landing.nav.login') }}</a>
        @endif
    </div>
</nav>

<main class="max-w-container-max mx-auto px-margin-mobile md:px-gutter">
    {{-- Hero — Webnu carta digital; TVPik como extra --}}
    <section id="inicio" class="py-16 md:py-24 grid grid-cols-1 md:grid-cols-2 gap-12 md:gap-16 items-center">
        <div class="space-y-8">
            <div class="flex flex-wrap gap-3">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-surface-container-high border border-outline-variant text-label-sm text-primary">
                    <span class="material-symbols-outlined text-[16px]">psychology</span>
                    {{ __('landing.hero.badge_scan') }}
                </span>
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-surface-container-high border border-outline-variant text-label-sm text-primary">
                    <span class="material-symbols-outlined text-[16px]">qr_code_2</span>
                    {{ __('landing.hero.badge_qr') }}
                </span>
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-surface-container-high border border-outline-variant text-label-sm text-primary">
                    <span class="material-symbols-outlined text-[16px]">translate</span>
                    {{ __('landing.hero.badge_lang') }}
                </span>
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-500/10 border border-orange-500/25 text-label-sm text-orange-800">
                    <span class="material-symbols-outlined text-[16px]">tv</span>
                    {{ __('landing.hero.badge_tvpik') }}
                </span>
            </div>
            <h1
                id="hero-headline"
                class="font-headline text-headline-xl text-on-surface leading-tight min-h-[3.6em] md:min-h-[2.4em]"
                data-hooks='@json($heroHooks)'
            >{{ $heroHooks[0] }}</h1>
            <p class="text-body-lg text-text-muted max-w-lg">
                {{ __('landing.hero.subtitle') }}
            </p>
            <div class="flex flex-wrap items-center gap-4">
                @if($isLoggedIn)
                    <a href="{{ $panelUrl }}" class="inline-flex items-center gap-2 px-8 py-4 bg-primary text-on-primary text-label-md rounded-lg hover:opacity-90 transition-opacity font-semibold">
                        {{ __('landing.hero.logged_cta') }} <span class="material-symbols-outlined text-[20px]">dashboard</span>
                    </a>
                @else
                    <a href="{{ $registerUrl }}" class="inline-flex items-center gap-2 px-8 py-4 bg-primary text-on-primary text-label-md rounded-lg hover:opacity-90 transition-opacity font-semibold">
                        {{ __('landing.hero.signup_cta') }} <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </a>
                @endif
                <a href="#demos-carta" class="text-label-md text-primary font-medium hover:underline inline-flex items-center gap-1">
                    {{ __('landing.hero.cta_demos') }} <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                </a>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex -space-x-3">
                    <span class="w-11 h-11 rounded-full border-2 border-surface bg-primary-container flex items-center justify-center text-on-primary text-label-sm font-bold">QR</span>
                    <span class="w-11 h-11 rounded-full border-2 border-surface bg-surface-container flex items-center justify-center text-primary text-label-sm font-bold">IA</span>
                    <span class="w-11 h-11 rounded-full border-2 border-surface bg-surface-container-high flex items-center justify-center text-primary text-label-sm font-bold">+</span>
                </div>
                <span class="text-label-md text-text-muted">{{ __('landing.hero.social_proof') }}</span>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-surface-container-lowest border border-border-subtle p-8 rounded-xl shadow-sm">
                @if($isLoggedIn)
                    <div class="mb-6">
                        <h3 class="font-headline text-headline-md text-on-surface">{{ __('landing.hero.logged_title') }}</h3>
                        <p class="mt-2 text-label-sm text-text-muted">{{ __('landing.hero.logged_desc') }}</p>
                    </div>
                    <a href="{{ $panelUrl }}" class="w-full py-4 bg-primary text-on-primary text-label-md rounded-lg hover:opacity-90 transition-opacity font-semibold flex items-center justify-center gap-2">
                        {{ __('landing.hero.logged_cta') }} <span class="material-symbols-outlined text-[20px]">dashboard</span>
                    </a>
                @else
                    <div class="mb-6">
                        <h3 class="font-headline text-headline-md text-on-surface">{{ __('landing.hero.signup_title') }}</h3>
                        <p class="mt-2 text-label-sm text-text-muted">{{ __('landing.hero.signup_desc') }}</p>
                    </div>
                    <form action="{{ $registerUrl }}" method="GET" class="space-y-4">
                        <div>
                            <label class="text-label-md text-on-surface-variant block mb-1">{{ __('landing.hero.email_label') }}</label>
                            <input name="email" required class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary focus:border-primary outline-none" placeholder="{{ __('landing.hero.email_placeholder') }}" type="email" autocomplete="email"/>
                        </div>
                        <button type="submit" class="w-full py-4 bg-primary text-on-primary text-label-md rounded-lg hover:opacity-90 transition-opacity font-semibold flex items-center justify-center gap-2">
                            {{ __('landing.hero.signup_cta') }} <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                        </button>
                        <p class="text-center text-label-sm text-text-muted">{{ __('landing.hero.signup_note') }}</p>
                    </form>
                @endif
            </div>
            @include('landing.partials.hero-addon-tvpik', ['tvpikSlides' => $tvpikSlides])
        </div>
    </section>

    {{-- Métricas --}}
    <section class="py-12 border-y border-border-subtle mb-16">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            @foreach($landingStats as $stat)
                <div><div class="text-headline-lg font-headline text-primary">{{ $stat['value'] }}</div><div class="text-label-md text-text-muted">{{ $stat['label'] }}</div></div>
            @endforeach
        </div>
    </section>

    {{-- 3 cartas demo premium --}}
    <section id="demos-carta" class="py-20 md:py-24">
        <div class="text-center mb-14">
            <span class="inline-block bg-primary/10 text-primary px-3 py-1 rounded-full text-label-sm font-bold uppercase tracking-wider mb-3">{{ __('landing.demos.badge') }}</span>
            <h2 class="font-headline text-headline-xl mb-4">{{ __('landing.demos.title') }}</h2>
            <p class="text-body-lg text-text-muted max-w-2xl mx-auto">
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
    </section>

    <section class="py-20 md:py-24 border-t border-border-subtle">
        <div id="personalizable" class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center max-w-5xl mx-auto" data-customize-presets='@json($landingCustomizePresets ?? [])'>
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
            <div class="space-y-6 order-1 lg:order-2">
                <span class="inline-block bg-primary/10 text-primary px-3 py-1 rounded-full text-label-sm font-bold uppercase tracking-wider">{{ __('landing.customize.badge') }}</span>
                <h3 class="font-headline text-headline-lg">{{ __('landing.customize.title') }}</h3>
                <p class="text-body-lg text-text-muted">
                    {{ __('landing.customize.desc') }}
                </p>
                <ul class="space-y-3 text-label-md text-text-muted">
                    @foreach(__('landing.customize.bullets') as $bullet)
                        <li class="flex gap-3 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span>{{ $bullet }}</li>
                    @endforeach
                </ul>
                <a href="#inicio" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:opacity-90 transition-opacity">
                    {{ __('landing.customize.cta') }} <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                </a>
            </div>
        </div>
    </section>

    {{-- Funciones para el día a día --}}
    <section id="funciones" class="py-20 md:py-24 mb-8">
        <div class="text-center mb-14">
            <span class="inline-block bg-primary/10 text-primary px-3 py-1 rounded-full text-label-sm font-bold uppercase tracking-wider mb-3">{{ __('landing.features.badge') }}</span>
            <h2 class="font-headline text-headline-xl mb-4">{{ __('landing.features.title') }}</h2>
            <p class="text-body-lg text-text-muted max-w-2xl mx-auto">{{ __('landing.features.subtitle') }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($landingFeatures ?? [] as $feat)
                <div class="relative bg-surface-container-lowest border border-border-subtle rounded-xl p-6 hover:border-primary/30 hover:shadow-md transition-all {{ !empty($feat['plan']) ? 'landing-feat--premium' : '' }}">
                    @if(!empty($feat['plan']))
                        <span class="landing-plan-badge landing-plan-badge--{{ $feat['plan'] === 'plus' ? 'plus' : 'pro' }}">
                            {{ $feat['plan'] === 'plus' ? __('landing.features.plan_plus') : __('landing.features.plan_pro') }}
                        </span>
                    @endif
                    <div class="w-11 h-11 rounded-xl bg-primary-fixed flex items-center justify-center text-primary mb-4">
                        <span class="material-symbols-outlined text-[24px]">{{ $feat['icon'] }}</span>
                    </div>
                    <h3 class="font-headline text-headline-md mb-2">{{ $feat['t'] }}</h3>
                    <p class="text-label-md text-text-muted leading-relaxed">{{ $feat['d'] }}</p>
                    @if(!empty($feat['free_note']))
                        <p class="text-label-sm text-primary mt-3 font-medium">{{ $feat['free_note'] }}</p>
                    @elseif(!empty($feat['plan']))
                        <p class="text-label-sm text-text-muted mt-3">{{ __('landing.features.included_in', ['plan' => $feat['plan'] === 'plus' ? __('landing.features.plan_plus') : __('landing.features.plan_pro')]) }}</p>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="mt-12 bg-surface-container border border-border-subtle rounded-2xl p-8 md:p-10 flex flex-col md:flex-row gap-8 items-start md:items-center">
            <div class="w-14 h-14 rounded-2xl bg-primary-container text-on-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[28px]">forum</span>
            </div>
            <div class="flex-1 space-y-2">
                <h3 class="font-headline text-headline-md">{{ __('landing.features.feedback_title') }}</h3>
                <p class="text-body-md text-text-muted max-w-2xl">
                    {{ __('landing.features.feedback_desc') }}
                </p>
            </div>
            <button type="button" id="suggestion-open" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:opacity-90 transition-opacity shrink-0 whitespace-nowrap">
                <span class="material-symbols-outlined text-[20px]">lightbulb</span>
                {{ __('landing.features.feedback_cta') }}
            </button>
        </div>
    </section>

    {{-- Reels --}}
    <section id="reels" class="py-20 md:py-24">
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

    @include('landing.partials.tv-wall-section')

    @include('landing.partials.tvpik-section', ['tvpikSlides' => $tvpikSlides])

    {{-- Testimonios --}}
    <section class="py-16 mb-8">
        <h2 class="text-center font-headline text-headline-xl mb-12">{{ __('landing.testimonials.title') }}</h2>
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

    {{-- Escaneo IA + 3 pasos --}}
    <section id="process" class="py-20 md:py-24">
        <div class="text-center mb-12">
            <span class="inline-block bg-primary/10 text-primary px-3 py-1 rounded-full text-label-sm font-bold uppercase tracking-wider mb-3">{{ __('landing.process.badge') }}</span>
            <h2 class="font-headline text-headline-xl">{{ __('landing.process.title') }}</h2>
            <p class="text-body-lg text-text-muted mt-3 max-w-2xl mx-auto">{{ __('landing.process.subtitle') }} <span class="text-primary font-medium">{{ __('landing.process.plus_note') }}</span></p>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14 items-center max-w-5xl mx-auto mb-16">
            <div class="landing-scan-demo" aria-hidden="true">
                <div class="landing-scan-demo__frame">
                    <p class="landing-scan-demo__section">{{ __('landing.process.scan_demo.section_starters') }}</p>
                    <div class="landing-scan-demo__row"><span>{{ __('landing.process.scan_demo.item1') }}</span><strong>{{ __('landing.process.scan_demo.price1') }}</strong></div>
                    <div class="landing-scan-demo__row"><span>{{ __('landing.process.scan_demo.item2') }}</span><strong>{{ __('landing.process.scan_demo.price2') }}</strong></div>
                    <p class="landing-scan-demo__section landing-scan-demo__section--spaced">{{ __('landing.process.scan_demo.section_mains') }}</p>
                    <div class="landing-scan-demo__row"><span>{{ __('landing.process.scan_demo.item3') }}</span><strong>{{ __('landing.process.scan_demo.price3') }}</strong></div>
                    <div class="landing-scan-demo__row"><span>{{ __('landing.process.scan_demo.item4') }}</span><strong>{{ __('landing.process.scan_demo.price4') }}</strong></div>
                    <div class="landing-scan-demo__scanline" aria-hidden="true"></div>
                </div>
                <p class="landing-scan-demo__badge">
                    <span class="material-symbols-outlined text-[18px]">description</span>
                    {{ __('landing.process.scan_demo.detected') }}
                </p>
            </div>
            <p class="sr-only">{{ __('landing.process.scan_demo.aria') }}</p>
            <div>
                <h3 class="font-headline text-headline-md mb-2">{{ __('landing.process.paths_title') }}</h3>
                <p class="text-body-md text-text-muted mb-6">{{ __('landing.process.paths_subtitle') }}</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    <a href="#inicio" class="landing-scan-path-btn landing-scan-path-btn--primary">
                        <span class="material-symbols-outlined text-[22px]">photo_camera</span>
                        {{ __('landing.process.cta_scan') }}
                    </a>
                    <a href="#inicio" class="landing-scan-path-btn landing-scan-path-btn--primary">
                        <span class="material-symbols-outlined text-[22px]">upload_file</span>
                        {{ __('landing.process.cta_upload') }}
                    </a>
                </div>
                <p class="text-label-sm text-text-muted">{{ __('landing.process.cta_note') }}</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative max-w-5xl mx-auto">
            <div class="hidden md:block absolute top-12 left-1/4 right-1/4 h-0.5 border-t-2 border-dashed border-outline-variant -z-10"></div>
            @foreach($landingSteps ?? [] as $step)
                <div class="text-center space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-primary-container text-on-primary flex items-center justify-center mx-auto">
                        <span class="material-symbols-outlined text-[32px]">{{ $step['icon'] }}</span>
                    </div>
                    <h3 class="font-headline text-headline-md">{{ $step['t'] }}</h3>
                    <p class="text-text-muted px-2">{{ $step['d'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Precios (Free, Pro, Plus) desde config/plans.php --}}
    @php
        $landingPricingPlans = $landingPricingPlans ?? [];
        $landingPricingComparison = $landingPricingComparison ?? [];
        $landingPricingTierOrder = $landingPricingTierOrder ?? ['free', 'pro', 'plus'];
        $landingFranchisePlan = $landingFranchisePlan ?? null;
    @endphp
    <section id="pricing" class="py-20 md:py-24">
        <div class="text-center mb-12">
            <h2 class="font-headline text-headline-xl mb-4">{{ __('landing.pricing.title') }}</h2>
            <p class="text-body-lg text-text-muted max-w-2xl mx-auto">{{ __('landing.pricing.subtitle') }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-{{ max(1, count($landingPricingPlans)) }} gap-8 items-stretch max-w-5xl mx-auto">
            @foreach($landingPricingPlans as $plan)
                <div class="bg-surface-container-lowest {{ !empty($plan['highlight']) ? 'border-2 border-primary md:-translate-y-2 shadow-lg' : 'border border-border-subtle' }} p-8 rounded-xl flex flex-col relative">
                    @if(!empty($plan['badge']))
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary text-on-primary px-4 py-1 rounded-full text-label-sm font-semibold whitespace-nowrap">{{ $plan['badge'] }}</span>
                    @endif
                    <h3 class="font-headline text-headline-md mb-1">{{ $plan['name'] }}</h3>
                    <p class="text-label-sm text-text-muted mb-4">{{ $plan['tagline'] }}</p>
                    <div class="mb-6">
                        @if(($plan['id'] ?? '') === 'franchise' || ($plan['price'] ?? '') === 'A medida')
                            <span class="text-3xl font-bold">{{ $plan['price'] }}</span>
                        @else
                            <span class="text-4xl font-bold">{{ $plan['price'] }}</span><span class="text-text-muted">€</span>
                        @endif
                        <span class="text-text-muted text-label-md"> {{ $plan['period'] }}</span>
                    </div>
                    <ul class="space-y-3 mb-8 flex-grow text-label-md">
                        @foreach($plan['features'] as $feature)
                            <li class="flex gap-2 items-start">
                                <span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span>
                                <span>{!! $feature !!}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ $plan['cta_url'] ?? $registerUrl }}" class="w-full {{ !empty($plan['highlight']) ? 'py-4 bg-primary text-on-primary font-semibold hover:opacity-90' : ($plan['id'] === 'plus' ? 'py-3 border border-primary text-primary font-semibold hover:bg-primary/5' : 'py-3 border border-border-subtle font-medium hover:bg-surface-container') }} rounded-lg text-center transition-colors">{{ $plan['cta'] }}</a>
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

        @if(count($landingPricingComparison) > 0)
            <div class="mt-16 max-w-5xl mx-auto">
                <h3 class="text-center font-headline text-headline-md mb-6">{{ __('landing.pricing.comparison.title') }}</h3>
                <div class="overflow-x-auto rounded-xl border border-border-subtle bg-surface-container-lowest">
                    <table class="w-full text-left text-label-md border-collapse min-w-[36rem]">
                        <thead>
                            <tr class="bg-surface-container">
                                <th class="p-4 border-b border-border-subtle w-2/5"></th>
                                @foreach($landingPricingPlans as $plan)
                                    <th class="p-4 border-b border-border-subtle text-center font-headline text-headline-sm {{ !empty($plan['highlight']) ? 'text-primary' : '' }}">{{ $plan['name'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($landingPricingComparison as $row)
                                <tr class="hover:bg-surface-container/50">
                                    <td class="p-4 border-b border-border-subtle font-medium">{{ $row['label'] }}</td>
                                    @foreach($landingPricingTierOrder as $tierId)
                                        <td class="p-4 border-b border-border-subtle text-center text-text-muted">{{ $row['values'][$tierId] ?? '—' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <p class="text-center text-label-sm text-text-muted mt-8 max-w-xl mx-auto">{{ __('landing.pricing.footnote') }}</p>
    </section>

    {{-- FAQ --}}
    <section class="py-16 max-w-3xl mx-auto">
        <h2 class="text-center font-headline text-headline-xl mb-10">{{ __('landing.faq.title') }}</h2>
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
    <section id="contacto" class="py-16 mb-20">
        <div class="bg-primary rounded-[2rem] p-10 md:p-16 text-center text-on-primary relative overflow-hidden">
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
                    <li><a href="#tv-menus" class="hover:text-primary">{{ __('landing.nav.tv_menus') }}</a></li>
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
@include('partials.pwa-landing-scripts')
</body>
</html>
