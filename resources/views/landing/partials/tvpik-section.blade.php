{{-- Sección TVPik: TV protagonista centrada con cards flotantes de ventajas --}}
<section id="tvpik" class="landing-tvpik-section py-12 md:py-20 bg-surface-container-low rounded-3xl px-6 md:px-10 mb-12 scroll-mt-24">

    {{-- Cabecera --}}
    <div class="text-center mb-10 md:mb-14 max-w-3xl mx-auto">
        <span class="inline-block bg-orange-500/10 text-orange-700 px-3 py-1 rounded-full text-label-sm font-bold uppercase tracking-wider mb-3">{{ __('landing.tvpik.badge') }}</span>
        <h2 class="font-headline text-headline-xl mb-3">{{ __('landing.tvpik.title') }}</h2>
        <p class="text-body-md md:text-body-lg text-text-muted">{{ __('landing.tvpik.subtitle') }}</p>
    </div>

    {{-- TV + cards flotantes --}}
    <div class="landing-tv-stage">
        @include('landing.partials.tvpik-showcase', ['variant' => 'section', 'slides' => $tvpikSlides ?? []])

        {{-- Card: Estado sincronizado (top-left) --}}
        <div class="tv-feat-card tv-feat-card--sync" aria-hidden="true">
            <span class="tv-feat-card__icon tv-feat-card__icon--blue">
                <span class="material-symbols-outlined">sync</span>
            </span>
            <div class="tv-feat-card__text">
                <span class="tv-feat-card__label">{{ __('landing.tvpik.feat_sync_label') }}</span>
                <span class="tv-feat-card__value">{{ __('landing.tvpik.feat_sync_value') }}</span>
            </div>
        </div>

        {{-- Card: Actualización de precio (bottom-right) --}}
        <div class="tv-feat-card tv-feat-card--price" aria-hidden="true">
            <span class="tv-feat-card__icon tv-feat-card__icon--orange">
                <span class="material-symbols-outlined">currency_exchange</span>
            </span>
            <div class="tv-feat-card__text">
                <span class="tv-feat-card__label">{{ __('landing.tvpik.feat_price_label') }}</span>
                <span class="tv-feat-card__price-row">
                    <span class="tv-feat-card__price-old">14,90€</span>
                    <span class="material-symbols-outlined tv-feat-card__arrow">trending_down</span>
                    <span class="tv-feat-card__price-new">12,90€</span>
                </span>
            </div>
        </div>

        {{-- Card: Control desde móvil (top-right) --}}
        <div class="tv-feat-card tv-feat-card--mobile" aria-hidden="true">
            <span class="tv-feat-card__icon tv-feat-card__icon--dark">
                <span class="material-symbols-outlined">smartphone</span>
            </span>
            <div class="tv-feat-card__text">
                <span class="tv-feat-card__label">{{ __('landing.tvpik.feat_mobile_label') }}</span>
                <span class="tv-feat-card__value">{{ __('landing.tvpik.feat_ticket_value') }}</span>
            </div>
        </div>
    </div>

    {{-- 4 beneficios en columnas debajo --}}
    <div class="landing-tvpik-benefits">
        <div class="landing-tvpik-benefit">
            <span class="landing-tvpik-benefit__icon">
                <span class="material-symbols-outlined">sync</span>
            </span>
            <div>
                <h4 class="landing-tvpik-benefit__title">{{ __('landing.tvpik.benefit1_title') }}</h4>
                <p class="landing-tvpik-benefit__desc">{{ __('landing.tvpik.benefit1_desc') }}</p>
            </div>
        </div>
        <div class="landing-tvpik-benefit">
            <span class="landing-tvpik-benefit__icon">
                <span class="material-symbols-outlined">view_carousel</span>
            </span>
            <div>
                <h4 class="landing-tvpik-benefit__title">{{ __('landing.tvpik.benefit2_title') }}</h4>
                <p class="landing-tvpik-benefit__desc">{{ __('landing.tvpik.benefit2_desc') }}</p>
            </div>
        </div>
        <div class="landing-tvpik-benefit">
            <span class="landing-tvpik-benefit__icon">
                <span class="material-symbols-outlined">tv</span>
            </span>
            <div>
                <h4 class="landing-tvpik-benefit__title">{{ __('landing.tvpik.benefit3_title') }}</h4>
                <p class="landing-tvpik-benefit__desc">{{ __('landing.tvpik.benefit3_desc') }}</p>
            </div>
        </div>
        <div class="landing-tvpik-benefit">
            <span class="landing-tvpik-benefit__icon">
                <span class="material-symbols-outlined">payments</span>
            </span>
            <div>
                <h4 class="landing-tvpik-benefit__title">{{ __('landing.tvpik.benefit_sales_title') }}</h4>
                <p class="landing-tvpik-benefit__desc">{{ __('landing.tvpik.benefit_sales_desc') }}</p>
            </div>
        </div>
    </div>
</section>
