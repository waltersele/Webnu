@if(!empty($blogLocales))
    @php
        $currentLocale = $locale ?? config('blog.default', 'es');
        $landingLocales = config('landing.locales', []);
    @endphp
    <div class="landing-lang-select" data-landing-lang>
        @php
            $currentMeta = $blogLocales[$currentLocale] ?? reset($blogLocales);
            $landingCurrent = $landingLocales[$currentLocale] ?? [];
            $currentFlag = $landingCurrent['flag'] ?? $currentLocale;
            $currentNative = $landingCurrent['native'] ?? ($currentMeta['label'] ?? $currentLocale);
        @endphp
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
            @foreach($blogLocales as $code => $meta)
                @php
                    $landingMeta = $landingLocales[$code] ?? [];
                    $flag = $landingMeta['flag'] ?? $code;
                    $native = $landingMeta['native'] ?? ($meta['label'] ?? $code);
                    $isActive = $currentLocale === $code;
                @endphp
                <li role="option" @if($isActive) aria-selected="true" @endif>
                    <a href="{{ route('blog.index', ['locale' => $code]) }}"
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
