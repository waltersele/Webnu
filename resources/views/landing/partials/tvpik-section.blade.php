{{-- Sección TVPik (detalle) — el hero ya muestra la escena animada --}}
<section id="tvpik" class="py-20 md:py-24 bg-surface-container-low rounded-3xl px-6 md:px-10 mb-16 scroll-mt-24">
    <div class="text-center mb-14">
        <span class="inline-block bg-orange-500/10 text-orange-700 px-3 py-1 rounded-full text-label-sm font-bold uppercase tracking-wider mb-3">{{ __('landing.tvpik.badge') }}</span>
        <h2 class="font-headline text-headline-xl mb-4">{{ __('landing.tvpik.title') }}</h2>
        <p class="text-body-lg text-text-muted max-w-2xl mx-auto">
            {{ __('landing.tvpik.subtitle') }}
        </p>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center max-w-5xl mx-auto">
        <div class="space-y-8 order-2 lg:order-1">
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
                    <span class="material-symbols-outlined">dashboard</span>
                </div>
                <div>
                    <h4 class="font-headline text-headline-md mb-2">{{ __('landing.tvpik.benefit2_title') }}</h4>
                    <p class="text-text-muted">{{ __('landing.tvpik.benefit2_desc') }}</p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="w-12 h-12 rounded-full bg-orange-500/15 flex items-center justify-center text-orange-700 shrink-0">
                    <span class="material-symbols-outlined">slideshow</span>
                </div>
                <div>
                    <h4 class="font-headline text-headline-md mb-2">{{ __('landing.tvpik.benefit3_title') }}</h4>
                    <p class="text-text-muted">{{ __('landing.tvpik.benefit3_desc') }}</p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="w-12 h-12 rounded-full bg-orange-500/15 flex items-center justify-center text-orange-700 shrink-0">
                    <span class="material-symbols-outlined">view_carousel</span>
                </div>
                <div>
                    <h4 class="font-headline text-headline-md mb-2">{{ __('landing.tvpik.benefit4_title') }}</h4>
                    <p class="text-text-muted">{{ __('landing.tvpik.benefit4_desc') }}</p>
                </div>
            </div>
            <a href="#pricing" class="inline-flex items-center gap-2 text-orange-700 font-semibold text-label-md hover:underline">
                {{ __('landing.tvpik.pricing_link') }} <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </a>
        </div>

        <div class="order-1 lg:order-2">
            @include('landing.partials.tvpik-showcase', ['variant' => 'section', 'slides' => $tvpikSlides ?? []])
        </div>
    </div>
</section>
