@php
    $thumb = ($tvpikSlides[0]['image'] ?? null) ?: asset('img/productos/brasa-solomillo.jpg');
@endphp
<a href="#tvpik" class="landing-hero-addon group block rounded-xl border border-orange-500/25 bg-orange-500/[0.04] p-4 hover:border-orange-500/40 hover:bg-orange-500/[0.07] transition-colors">
    <div class="flex items-center gap-4">
        <div class="landing-hero-addon__thumb shrink-0 rounded-lg overflow-hidden border border-orange-500/20 w-20 h-14">
            <img src="{{ $thumb }}" alt="" class="w-full h-full object-cover" loading="lazy"/>
        </div>
        <div class="flex-grow min-w-0">
            <span class="text-label-sm font-bold uppercase tracking-wide text-orange-700">{{ __('landing.hero.tvpik_addon_badge') }}</span>
            <p class="font-headline text-headline-sm mt-0.5 mb-0.5 text-on-surface">TVPik</p>
            <p class="text-label-sm text-text-muted mb-0 line-clamp-2">{{ __('landing.hero.tvpik_addon_desc') }}</p>
        </div>
        <span class="material-symbols-outlined text-orange-600 shrink-0 group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
    </div>
</a>
