@if(!empty($blogLocales))
    @php
        $currentLocale = $locale ?? config('blog.default', 'es');
        $landingLocales = config('landing.locales', []);
        $context = $languageContext ?? 'index';
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
                    $href = route('blog.index', ['locale' => $code]);
                    $missing = false;

                    if ($context === 'show' && !empty($alternateTranslations)) {
                        $alt = $alternateTranslations->firstWhere('locale', $code);
                        if ($alt) {
                            $href = $alt->publicUrl();
                        } else {
                            $missing = true;
                            $href = route('blog.index', ['locale' => $code]);
                        }
                    } elseif ($context === 'category' && !empty($categorySlug)) {
                        $href = route('blog.category', ['locale' => $code, 'categorySlug' => $categorySlug]);
                    }
                @endphp
                <li role="option" @if($isActive) aria-selected="true" @endif>
                    <a href="{{ $href }}"
                       class="landing-lang-select__option {{ $isActive ? 'is-active' : '' }} {{ $missing ? 'opacity-50' : '' }}"
                       hreflang="{{ $meta['hreflang'] ?? $code }}"
                       lang="{{ $code }}"
                       @if($missing) title="{{ __('blog.translation_missing') }}" @endif>
                        <span class="fi fi-{{ $flag }} fis landing-lang-select__flag" aria-hidden="true"></span>
                        <span>{{ $native }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
