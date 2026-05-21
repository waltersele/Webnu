@php
    $variant = $variant ?? 'section';
    $slides = $slides ?? [];
    $sceneClass = 'landing-tvpik-scene' . ($variant === 'hero' ? ' landing-tvpik-scene--hero' : '');
    $first = $slides[0] ?? ['image' => '', 'tag' => '', 'title' => '', 'price' => ''];
@endphp
<div class="{{ $sceneClass }}"
     data-tvpik-root
     data-tvpik-slides='@json($slides)'
     data-tvpik-publishing="{{ __('landing.tvpik.phone_publishing') }}"
     data-tvpik-synced="{{ __('landing.tvpik.phone_synced') }}"
     aria-hidden="{{ $variant === 'hero' ? 'false' : 'true' }}">
    <div class="landing-tvpik-scene__ambience"></div>

    <div class="landing-tvpik-phone" data-tvpik-phone>
        <div class="landing-tvpik-phone__bar">
            <span></span><span></span><span></span>
            <span class="landing-tvpik-phone__label">{{ __('landing.tvpik.phone_label') }}</span>
        </div>
        <div class="landing-tvpik-phone__body">
            <span class="landing-tvpik-phone__chip"><span class="material-symbols-outlined text-[14px]">tv</span> {{ __('landing.tvpik.phone_chip') }}</span>
            <p class="landing-tvpik-phone__action" data-tvpik-action>{{ $first['action'] ?? '' }}</p>
            <span class="landing-tvpik-phone__status" data-tvpik-phone-status>{{ __('landing.tvpik.phone_publishing') }}</span>
        </div>
    </div>

    <div class="landing-tvpik-sync" data-tvpik-sync aria-hidden="true">
        <span class="landing-tvpik-sync__dot"></span>
        <span class="landing-tvpik-sync__line"></span>
        <span class="landing-tvpik-sync__label">{{ __('landing.tvpik.sync_label') }}</span>
    </div>

    <div class="landing-tvpik-tv" data-tvpik-tv>
        <div class="landing-tvpik-tv__mount"></div>
        <div class="landing-tvpik-tv__unit">
            <div class="landing-tvpik-tv__bezel">
                <div class="landing-tvpik-tv__screen landing-tvpik-tv__screen--warm" data-tvpik-screen>
                    <img data-tvpik-photo class="landing-tvpik-tv__photo" src="{{ $first['image'] ?? '' }}" alt=""/>
                    <div class="landing-tvpik-tv__overlay">
                        <span class="landing-tvpik-tv__tag" data-tvpik-tag>{{ $first['tag'] ?? '' }}</span>
                        <p class="landing-tvpik-tv__title" data-tvpik-title>{{ $first['title'] ?? '' }}</p>
                        <ul class="landing-tvpik-tv__items hidden" data-tvpik-items></ul>
                        <p class="landing-tvpik-tv__price" data-tvpik-price>{{ $first['price'] ?? '' }}</p>
                    </div>
                    <span class="landing-tvpik-tv__brand">TVPik</span>
                    <span class="landing-tvpik-tv__live"><span></span> {{ __('landing.tvpik.live') }}</span>
                    <span class="landing-tvpik-tv__updated" data-tvpik-updated>{{ __('landing.tvpik.updated') }}</span>
                </div>
            </div>
        </div>
        <div class="landing-tvpik-tv__glow"></div>
    </div>

    <div class="landing-tvpik-dots" data-tvpik-dots></div>
</div>
