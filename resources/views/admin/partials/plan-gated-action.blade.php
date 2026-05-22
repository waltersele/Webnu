@php
    $enabled = $enabled ?? true;
    $feature = $feature ?? '';
    $planLabel = $planLabel ?? app(\App\Services\UserPlanService::class)->requiredPlanLabel($feature) ?? 'Pro';
    $element = $element ?? 'a';
    $type = $type ?? 'button';
    $href = $href ?? '#';
    $class = $class ?? 'btn btn-outline-primary';
    $fallbackHref = $fallbackHref ?? null;
    $attrs = $attrs ?? '';
    $badgeSize = $badgeSize ?? 'xs';
    $showBadge = $showBadge ?? true;
@endphp
@if ($enabled)
    @if ($element === 'button')
        <button type="{{ $type }}" class="{{ $class }}" {!! $attrs !!}>{{ $slot }}</button>
    @else
        <a href="{{ $href }}" class="{{ $class }}" {!! $attrs !!}>{{ $slot }}</a>
    @endif
@else
    @if ($element === 'button')
        <button type="button"
                class="{{ $class }} wn-plan-gated--locked"
                aria-disabled="true"
                data-upgrade-trigger="{{ $feature }}"
                @if($fallbackHref) data-upgrade-fallback-href="{{ $fallbackHref }}" @endif
                {!! $attrs !!}>
            {{ $slot }}
            @if ($showBadge)
                @include('admin.partials.plan-pro-badge', ['label' => $planLabel, 'size' => $badgeSize])
            @endif
        </button>
    @else
        <a href="#"
           class="{{ $class }} wn-plan-gated--locked"
           role="button"
           data-upgrade-trigger="{{ $feature }}"
           @if($fallbackHref) data-upgrade-fallback-href="{{ $fallbackHref }}" @endif
           {!! $attrs !!}>
            {{ $slot }}
            @if ($showBadge)
                @include('admin.partials.plan-pro-badge', ['label' => $planLabel, 'size' => $badgeSize])
            @endif
        </a>
    @endif
@endif
