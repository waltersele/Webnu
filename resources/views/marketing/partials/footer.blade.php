@php
    $homeUrl = $homeUrl ?? route('home');
    $languageSelectorPartial = $languageSelectorPartial ?? 'landing.partials.language-selector';
    $isLoggedIn = $isLoggedIn ?? auth()->check();
    $loginUrl = $loginUrl ?? route('login');
    $panelUrl = $panelUrl ?? route('admin.dashboard');
    $landingLocales = $landingLocales ?? config('landing.locales', []);
@endphp
<footer class="bg-surface border-t border-border-subtle">
    <div class="max-w-container-max mx-auto px-margin-mobile md:px-gutter py-12 flex flex-col md:flex-row justify-between gap-10">
        <div class="max-w-sm space-y-4">
            <a href="{{ $homeUrl }}" class="inline-block">
                @include('partials.brand-logo', ['brandKey' => 'logo', 'brandClass' => 'landing-brand-logo landing-brand-logo--footer'])
            </a>
            <p class="text-text-muted text-body-md">{{ __('landing.footer.tagline') }}</p>
            <p class="text-text-muted text-sm">© {{ date('Y') }} Webnu.es</p>
        </div>
        <div class="grid grid-cols-2 gap-10">
            <div>
                <h5 class="font-label-md font-semibold mb-3">{{ __('landing.footer.product') }}</h5>
                <ul class="space-y-2 text-text-muted text-sm">
                    <li><a href="{{ $homeUrl }}#funciones" class="hover:text-primary">{{ __('landing.nav.features') }}</a></li>
                    <li><a href="{{ $homeUrl }}#demos-carta" class="hover:text-primary">{{ __('landing.nav.examples') }}</a></li>
                    <li><a href="{{ $homeUrl }}#reels" class="hover:text-primary">{{ __('landing.nav.reels') }}</a></li>
                    <li><a href="{{ $homeUrl }}#tvpik" class="hover:text-primary">{{ __('landing.nav.tvpik') }}</a></li>
                    <li><a href="{{ $homeUrl }}#process" class="hover:text-primary">{{ __('landing.nav.scan') }}</a></li>
                    <li><a href="{{ $homeUrl }}#pricing" class="hover:text-primary">{{ __('landing.nav.pricing') }}</a></li>
                    <li><a href="{{ route('blog.hub') }}" class="hover:text-primary">Blog</a></li>
                </ul>
            </div>
            <div>
                <h5 class="font-label-md font-semibold mb-3">Legal</h5>
                <ul class="space-y-2 text-text-muted text-sm">
                    <li><a href="{{ route('legal.privacy') }}" class="hover:text-primary">Política de privacidad</a></li>
                    <li><a href="{{ route('legal.terms') }}" class="hover:text-primary">Términos y condiciones</a></li>
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
                    <li><a href="{{ $homeUrl }}" class="hover:text-primary">{{ __('landing.footer.home') }}</a></li>
                </ul>
            </div>
            @if(!empty($blogLocales ?? $landingLocales))
                <div>
                    <h5 class="font-label-md font-semibold mb-3">{{ __('landing.nav.language') }}</h5>
                    @include($languageSelectorPartial)
                </div>
            @endif
        </div>
    </div>
</footer>
