@php
    $trigger = $trigger ?? '';
    $icon = $icon ?? 'ri-sparkling-line';
    $title = $title ?? '';
    $text = $text ?? '';
    $tier = $tier ?? 'Pro';
    $cta = $cta ?? 'Saber más';
    $tierSlug = strtolower($tier);
    $tierVariant = in_array($tierSlug, ['plus', 'pro'], true) ? $tierSlug : 'pro';
@endphp

<div class="wn-upgrade-teaser d-flex flex-wrap align-items-center gap-2 gap-md-3 mb-0" role="note">
    <span class="wn-upgrade-teaser__viewport" aria-hidden="true">
        <i class="{{ $icon }}"></i>
    </span>
    <div class="flex-grow-1 min-w-0">
        <div class="wn-upgrade-teaser__head">
            <strong class="wn-upgrade-teaser__title">{{ $title }}</strong>
            <span class="wn-plan-pro-badge wn-plan-pro-badge--xs wn-plan-pro-badge--{{ $tierVariant }}">{{ $tier }}</span>
        </div>
        @if ($text !== '')
            <p class="wn-upgrade-teaser__text mb-0">{{ $text }}</p>
        @endif
    </div>
    <button type="button"
            class="wn-upgrade-teaser__cta"
            data-upgrade-trigger="{{ $trigger }}">
        {{ $cta }}
    </button>
</div>
