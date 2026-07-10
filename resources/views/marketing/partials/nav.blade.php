@php
    $homeUrl = $homeUrl ?? route('home');
    $navActive = $navActive ?? null;
    $languageSelectorPartial = $languageSelectorPartial ?? 'landing.partials.language-selector';
    $isLoggedIn = $isLoggedIn ?? auth()->check();
    $loginUrl = $loginUrl ?? route('login');
    $panelUrl = $panelUrl ?? route('admin.dashboard');
    $settingsUrl = $settingsUrl ?? route('admin.settings');
    $logoutUrl = $logoutUrl ?? route('logout');
    $userDisplayName = $userDisplayName ?? '';
@endphp
<nav data-landing-nav class="sticky top-0 z-50 flex justify-between items-center w-full px-margin-mobile md:px-gutter max-w-container-max mx-auto h-20 bg-surface-container-lowest border-b border-border-subtle transition-shadow">
    <a href="{{ $homeUrl }}" class="inline-flex items-center shrink-0" title="Webnu">
        @include('partials.brand-logo', ['brandKey' => 'logo', 'brandClass' => 'landing-brand-logo'])
    </a>
    <div class="hidden md:flex items-center gap-8">
        <a class="text-text-muted hover:text-primary transition-colors text-label-md {{ $navActive === 'blog' ? '' : '' }}" href="{{ $homeUrl }}#demos-carta">{{ __('landing.nav.examples') }}</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="{{ $homeUrl }}#funciones">{{ __('landing.nav.features') }}</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="{{ $homeUrl }}#reels">{{ __('landing.nav.reels') }}</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="{{ $homeUrl }}#tvpik">{{ __('landing.nav.tvpik') }}</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="{{ $homeUrl }}#process">{{ __('landing.nav.scan') }}</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="{{ $homeUrl }}#pricing">{{ __('landing.nav.pricing') }}</a>
        <a class="{{ $navActive === 'blog' ? 'text-primary font-semibold' : 'text-text-muted hover:text-primary' }} transition-colors text-label-md" href="{{ route('blog.hub') }}">Blog</a>
    </div>
    <div class="flex items-center gap-3">
        @include($languageSelectorPartial)
        @if($isLoggedIn)
            @include('landing.partials.user-menu')
        @else
            <a href="{{ $loginUrl }}" class="px-5 py-2 rounded-lg bg-primary-container text-on-primary text-label-md hover:opacity-90 transition-opacity font-medium">{{ __('landing.nav.login') }}</a>
        @endif
    </div>
</nav>
