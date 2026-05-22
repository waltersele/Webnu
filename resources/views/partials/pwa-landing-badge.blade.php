{{-- Badge «Abrir como app» — visible cuando el navegador permite instalar la PWA --}}
<button type="button"
        class="landing-pwa-install hidden items-center gap-1.5 px-3 py-2 rounded-lg border border-primary/40 bg-primary/5 text-primary text-label-md font-medium hover:bg-primary/10 transition-colors shrink-0"
        data-pwa-install-badge
        data-pwa-install
        aria-hidden="true"
        title="{{ __('landing.nav.install_app_title') }}">
    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">install_mobile</span>
    <span class="hidden sm:inline">{{ __('landing.nav.install_app') }}</span>
</button>
