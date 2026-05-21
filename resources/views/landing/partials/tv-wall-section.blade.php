{{-- Cartas en TV — fast food / restaurante (ilustración + Webnu) --}}
<section id="tv-menus" class="landing-tv-wall py-20 md:py-24 mb-16 scroll-mt-24">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-10 md:mb-14">
            <span class="inline-block bg-primary/10 text-primary px-3 py-1 rounded-full text-label-sm font-bold uppercase tracking-wider mb-3">
                {{ __('landing.tv_wall.badge') }}
            </span>
            <h2 class="font-headline text-headline-xl mb-4">{{ __('landing.tv_wall.title') }}</h2>
            <p class="text-body-lg text-text-muted max-w-2xl mx-auto">
                {{ __('landing.tv_wall.subtitle') }}
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14 items-center">
            <div class="landing-tv-wall__scene" aria-hidden="true">
                <div class="landing-tv-wall__banner">
                    <span class="landing-tv-wall__banner-text">{{ __('landing.tv_wall.banner') }}</span>
                </div>
                <div class="landing-tv-wall__screens">
                    <div class="landing-tv-wall__screen landing-tv-wall__screen--menu">
                        <p class="landing-tv-wall__screen-title">{{ __('landing.tv_wall.screen1_title') }}</p>
                        <ul class="landing-tv-wall__menu-list">
                            @foreach(__('landing.tv_wall.screen1_items') as $item)
                                <li><span>{{ $item['name'] }}</span><strong>{{ $item['price'] }}</strong></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="landing-tv-wall__screen landing-tv-wall__screen--combo">
                        <p class="landing-tv-wall__screen-kicker">{{ __('landing.tv_wall.screen2_kicker') }}</p>
                        <div class="landing-tv-wall__combo-photo"></div>
                        <p class="landing-tv-wall__combo-price">{{ __('landing.tv_wall.screen2_price') }}</p>
                    </div>
                    <div class="landing-tv-wall__screen landing-tv-wall__screen--drinks">
                        <p class="landing-tv-wall__screen-title">{{ __('landing.tv_wall.screen3_title') }}</p>
                        <div class="landing-tv-wall__drinks-row">
                            <span></span><span></span><span></span>
                        </div>
                        <ul class="landing-tv-wall__menu-list landing-tv-wall__menu-list--compact">
                            @foreach(__('landing.tv_wall.screen3_items') as $item)
                                <li><span>{{ $item['name'] }}</span><strong>{{ $item['price'] }}</strong></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="landing-tv-wall__screen landing-tv-wall__screen--combo landing-tv-wall__screen--mirror">
                        <p class="landing-tv-wall__screen-kicker">{{ __('landing.tv_wall.screen2_kicker') }}</p>
                        <div class="landing-tv-wall__combo-photo"></div>
                    </div>
                </div>
                <div class="landing-tv-wall__counter"></div>
                <div class="landing-tv-wall__brand">
                    @include('partials.brand-logo', ['brandKey' => 'logo', 'brandClass' => 'landing-tv-wall__logo'])
                    <span>{{ __('landing.tv_wall.powered') }}</span>
                </div>
            </div>

            <div class="space-y-6">
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined">restaurant_menu</span>
                    </div>
                    <div>
                        <h4 class="font-headline text-headline-md mb-2">{{ __('landing.tv_wall.benefit1_title') }}</h4>
                        <p class="text-text-muted">{{ __('landing.tv_wall.benefit1_desc') }}</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined">bolt</span>
                    </div>
                    <div>
                        <h4 class="font-headline text-headline-md mb-2">{{ __('landing.tv_wall.benefit2_title') }}</h4>
                        <p class="text-text-muted">{{ __('landing.tv_wall.benefit2_desc') }}</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined">hd</span>
                    </div>
                    <div>
                        <h4 class="font-headline text-headline-md mb-2">{{ __('landing.tv_wall.benefit3_title') }}</h4>
                        <p class="text-text-muted">{{ __('landing.tv_wall.benefit3_desc') }}</p>
                    </div>
                </div>
                <a href="#tvpik" class="inline-flex items-center gap-2 text-primary font-semibold text-label-md hover:underline">
                    {{ __('landing.tv_wall.cta') }}
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </a>
            </div>
        </div>
    </div>
</section>
