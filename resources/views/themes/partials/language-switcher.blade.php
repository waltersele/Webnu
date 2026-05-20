@php
    $menuLocale = $menuLocale ?? ($company->defaultLocale() ?? 'es');
    $publicLocales = isset($menuLocaleService)
        ? $menuLocaleService->publicLocalesForCompany($company)
        : $company->publicLocales();
    $preserveQuery = request()->except('lang');
@endphp

@if(count($publicLocales) > 1 && isset($menuLocaleService))
    <div class="wn-lang-switcher" role="navigation" aria-label="Idioma de la carta">
        <div class="wn-lang-switcher__track">
            @foreach($publicLocales as $code)
                @php
                    $meta = $menuLocaleService->localeMeta($code);
                    $isActive = $code === $menuLocale;
                    $url = $menuLocaleService->menuUrl($company, $code, $preserveQuery);
                @endphp
                <a href="{{ $url }}"
                   class="wn-lang-chip {{ $isActive ? 'is-active' : '' }}"
                   hreflang="{{ $code }}"
                   lang="{{ $code }}"
                   @if($isActive) aria-current="true" @endif>
                    <span class="wn-lang-chip__code">{{ $meta['flag'] ?? strtoupper($code) }}</span>
                    <span class="wn-lang-chip__label">{{ $meta['native'] ?? strtoupper($code) }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endif
