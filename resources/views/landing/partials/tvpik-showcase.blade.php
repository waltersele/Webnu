@php
    $variant = $variant ?? 'section';
    $slides = $slides ?? [];
    $isHero = $variant === 'hero';
@endphp
<div class="landing-tv-show landing-tv-show--{{ $variant }}"
     data-tv-show
     data-tv-autoplay="{{ $isHero ? 'false' : 'true' }}"
     aria-roledescription="carousel"
     aria-label="Plantillas TV disponibles">

    <div class="landing-tv-show__frame">
        <div class="landing-tv-show__bezel">
            <div class="landing-tv-show__screen">
                <span class="landing-tv-show__brand">TVPik</span>
                <span class="landing-tv-show__live"><span></span> {{ __('landing.tvpik.live') }}</span>

                <div class="landing-tv-show__stage" data-tv-stage>
                    @foreach($slides as $idx => $slide)
                        @php
                            $kind = $slide['kind'] ?? 'hero';
                            $isFirst = $idx === 0;
                        @endphp
                        <article class="landing-tv-show__slide landing-tv-show__slide--{{ $kind }} {{ $isFirst ? 'is-active' : '' }}"
                                 data-tv-slide="{{ $idx }}"
                                 data-tv-kind="{{ $kind }}"
                                 role="group"
                                 aria-roledescription="slide"
                                 aria-label="{{ $slide['label'] ?? $slide['tag'] ?? '' }} ({{ $idx + 1 }} / {{ count($slides) }})"
                                 @if(!$isFirst) aria-hidden="true" @endif>

                            @if($kind === 'hero')
                                <img src="{{ $slide['image'] ?? '' }}" alt="" class="landing-tv-show__photo" loading="lazy">
                                <div class="landing-tv-show__overlay landing-tv-show__overlay--hero">
                                    <span class="landing-tv-show__tag">{{ $slide['tag'] ?? '' }}</span>
                                    <h3 class="landing-tv-show__title">{{ $slide['title'] ?? '' }}</h3>
                                    @if(!empty($slide['subtitle']))
                                        <p class="landing-tv-show__sub">{{ $slide['subtitle'] }}</p>
                                    @endif
                                    @if(!empty($slide['price']))
                                        <span class="landing-tv-show__price">{{ $slide['price'] }}</span>
                                    @endif
                                </div>

                            @elseif($kind === 'tapas')
                                <div class="landing-tv-show__tapas">
                                    <header class="landing-tv-show__tapas-head">
                                        <span class="landing-tv-show__tag">{{ $slide['tag'] ?? '' }}</span>
                                        <h3 class="landing-tv-show__title landing-tv-show__title--sm">{{ $slide['title'] ?? '' }}</h3>
                                    </header>
                                    <ul class="landing-tv-show__tapas-grid">
                                        @foreach(($slide['items'] ?? []) as $tapa)
                                            <li class="landing-tv-show__tapa">
                                                <div class="landing-tv-show__tapa-photo" @if(!empty($tapa['image'])) style="background-image:url('{{ $tapa['image'] }}');" @endif></div>
                                                <p class="landing-tv-show__tapa-name">{{ $tapa['name'] ?? '' }}</p>
                                                @if(!empty($tapa['price']))
                                                    <span class="landing-tv-show__tapa-price">{{ $tapa['price'] }}</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                            @elseif($kind === 'daily')
                                <div class="landing-tv-show__daily">
                                    <header class="landing-tv-show__daily-head">
                                        <span class="landing-tv-show__tag">{{ $slide['tag'] ?? '' }}</span>
                                        <h3 class="landing-tv-show__title">{{ $slide['title'] ?? '' }}</h3>
                                    </header>
                                    <ul class="landing-tv-show__daily-list">
                                        @foreach(($slide['items'] ?? []) as $line)
                                            <li>{{ $line['name'] ?? '' }}</li>
                                        @endforeach
                                    </ul>
                                    @if(!empty($slide['price']))
                                        <span class="landing-tv-show__price landing-tv-show__price--xl">{{ $slide['price'] }}</span>
                                    @endif
                                </div>

                            @elseif($kind === 'video')
                                <img src="{{ $slide['image'] ?? '' }}" alt="" class="landing-tv-show__photo landing-tv-show__photo--video" loading="lazy">
                                <span class="landing-tv-show__play" aria-hidden="true"><span class="material-symbols-outlined">play_arrow</span></span>
                                <div class="landing-tv-show__overlay landing-tv-show__overlay--video">
                                    <span class="landing-tv-show__tag">{{ $slide['tag'] ?? '' }}</span>
                                    <h3 class="landing-tv-show__title">{{ $slide['title'] ?? '' }}</h3>
                                    @if(!empty($slide['subtitle']))
                                        <p class="landing-tv-show__sub">{{ $slide['subtitle'] }}</p>
                                    @endif
                                    @if(!empty($slide['price']))
                                        <span class="landing-tv-show__price">{{ $slide['price'] }}</span>
                                    @endif
                                </div>

                            @elseif($kind === 'menu')
                                <div class="landing-tv-show__menu">
                                    <header class="landing-tv-show__menu-head">
                                        <span class="landing-tv-show__tag">{{ $slide['tag'] ?? '' }}</span>
                                        <h3 class="landing-tv-show__title landing-tv-show__title--sm">{{ $slide['title'] ?? '' }}</h3>
                                    </header>
                                    <div class="landing-tv-show__menu-cols">
                                        @foreach(($slide['sections'] ?? []) as $section)
                                            <div class="landing-tv-show__menu-col">
                                                <p class="landing-tv-show__menu-section">{{ $section['name'] ?? '' }}</p>
                                                <ul class="landing-tv-show__menu-list">
                                                    @foreach(($section['items'] ?? []) as $line)
                                                        <li>
                                                            <span>{{ $line['name'] ?? '' }}</span>
                                                            @if(!empty($line['price']))
                                                                <strong>{{ $line['price'] }}</strong>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>

                <span class="landing-tv-show__updated">{{ __('landing.tvpik.updated') }}</span>
            </div>
        </div>
        <div class="landing-tv-show__stand" aria-hidden="true"></div>
    </div>

    @if(count($slides) > 1)
        <div class="landing-tv-show__controls">
            <button type="button" class="landing-tv-show__nav landing-tv-show__nav--prev" data-tv-prev aria-label="{{ __('landing.tvpik.nav_prev') }}">
                <span class="material-symbols-outlined">chevron_left</span>
            </button>
            <div class="landing-tv-show__dots" role="tablist" aria-label="Plantillas">
                @foreach($slides as $idx => $slide)
                    <button type="button"
                            class="landing-tv-show__dot {{ $idx === 0 ? 'is-active' : '' }}"
                            data-tv-dot="{{ $idx }}"
                            role="tab"
                            aria-selected="{{ $idx === 0 ? 'true' : 'false' }}"
                            aria-label="{{ $slide['label'] ?? ('Slide ' . ($idx + 1)) }}">
                        <span>{{ $slide['label'] ?? '' }}</span>
                    </button>
                @endforeach
            </div>
            <button type="button" class="landing-tv-show__nav landing-tv-show__nav--next" data-tv-next aria-label="{{ __('landing.tvpik.nav_next') }}">
                <span class="material-symbols-outlined">chevron_right</span>
            </button>
        </div>
    @endif
</div>
