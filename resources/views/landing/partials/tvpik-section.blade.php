{{-- Sección TVPik unificada: TV con slider + 3 benefits + CTA --}}
<section id="tvpik" class="landing-tvpik-section py-20 md:py-24 bg-surface-container-low rounded-3xl px-6 md:px-10 mb-16 scroll-mt-24">
    <div class="text-center mb-12 md:mb-14 max-w-3xl mx-auto">
        <span class="inline-block bg-orange-500/10 text-orange-700 px-3 py-1 rounded-full text-label-sm font-bold uppercase tracking-wider mb-3">{{ __('landing.tvpik.badge') }}</span>
        <h2 class="font-headline text-headline-xl mb-4">{{ __('landing.tvpik.title') }}</h2>
        <p class="text-body-lg text-text-muted">{{ __('landing.tvpik.subtitle') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1.15fr_1fr] gap-10 lg:gap-14 items-center max-w-6xl mx-auto">
        <div class="order-1 lg:order-1">
            @include('landing.partials.tvpik-showcase', ['variant' => 'section', 'slides' => $tvpikSlides ?? []])
        </div>

        <div class="order-2 lg:order-2 space-y-7">
            <div class="flex gap-4">
                <div class="w-12 h-12 rounded-full bg-orange-500/15 flex items-center justify-center text-orange-700 shrink-0">
                    <span class="material-symbols-outlined">sync</span>
                </div>
                <div>
                    <h4 class="font-headline text-headline-md mb-2">{{ __('landing.tvpik.benefit1_title') }}</h4>
                    <p class="text-text-muted">{{ __('landing.tvpik.benefit1_desc') }}</p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="w-12 h-12 rounded-full bg-orange-500/15 flex items-center justify-center text-orange-700 shrink-0">
                    <span class="material-symbols-outlined">view_carousel</span>
                </div>
                <div>
                    <h4 class="font-headline text-headline-md mb-2">{{ __('landing.tvpik.benefit2_title') }}</h4>
                    <p class="text-text-muted">{{ __('landing.tvpik.benefit2_desc') }}</p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="w-12 h-12 rounded-full bg-orange-500/15 flex items-center justify-center text-orange-700 shrink-0">
                    <span class="material-symbols-outlined">tv</span>
                </div>
                <div>
                    <h4 class="font-headline text-headline-md mb-2">{{ __('landing.tvpik.benefit3_title') }}</h4>
                    <p class="text-text-muted">{{ __('landing.tvpik.benefit3_desc') }}</p>
                </div>
            </div>

            <a href="#pricing" class="inline-flex items-center gap-2 text-orange-700 font-semibold text-label-md hover:underline">
                {{ __('landing.tvpik.pricing_link') }}
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </a>
        </div>
    </div>
</section>
