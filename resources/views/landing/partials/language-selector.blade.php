@if(!empty($landingLocales))
    @php
        $currentLocale = $locale ?? 'es';
        $homeUrl = url('/');
        $currentMeta = $landingLocales[$currentLocale] ?? reset($landingLocales);
        $currentFlag = $currentMeta['flag'] ?? $currentLocale;
        $currentNative = $currentMeta['native'] ?? $currentLocale;
    @endphp
    <div class="landing-lang-select" data-landing-lang>
        <button type="button"
                class="landing-lang-select__trigger"
                aria-haspopup="listbox"
                aria-expanded="false"
                aria-label="{{ __('landing.nav.language') }}: {{ $currentNative }}">
            <span class="fi fi-{{ $currentFlag }} fis landing-lang-select__flag" aria-hidden="true"></span>
            <span class="landing-lang-select__label">{{ $currentNative }}</span>
            <span class="material-symbols-outlined landing-lang-select__chevron" aria-hidden="true">expand_more</span>
        </button>
        <ul class="landing-lang-select__menu" role="listbox" hidden>
            @foreach($landingLocales as $code => $meta)
                @php
                    $flag = $meta['flag'] ?? $code;
                    $native = $meta['native'] ?? $code;
                    $isActive = $currentLocale === $code;
                @endphp
                <li role="option" @if($isActive) aria-selected="true" @endif>
                    <a href="{{ $homeUrl }}?lang={{ $code }}"
                       class="landing-lang-select__option {{ $isActive ? 'is-active' : '' }}"
                       hreflang="{{ $meta['hreflang'] ?? $code }}"
                       lang="{{ $code }}">
                        <span class="fi fi-{{ $flag }} fis landing-lang-select__flag" aria-hidden="true"></span>
                        <span>{{ $native }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
