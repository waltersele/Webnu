@php
    $feature = $feature ?? 'videos';
    $copy = config('upgrade_triggers.copy.' . $feature, []);
    if (! is_array($copy)) {
        $copy = [];
    }
    $planLabel = $planLabel ?? app(\App\Services\UserPlanService::class)->requiredPlanLabel($feature) ?? ucfirst($copy['price_tier'] ?? 'Plus');
    $badgeLabel = ! empty($copy['price_tier'])
        ? ucfirst($copy['price_tier'])
        : $planLabel;
    $message = $message ?? ($copy['body'] ?? "Disponible con el plan {$planLabel}. Mejora tu suscripción para desbloquear esta función.");
    $cta = $copy['cta'] ?? 'Saber más';
@endphp
<div class="wn-plan-feature-lock__veil">
    <div class="wn-plan-feature-lock__card">
        @include('admin.partials.plan-pro-badge', ['label' => $badgeLabel, 'size' => 'xs'])
        <p class="wn-plan-feature-lock__text">{{ $message }}</p>
        <button type="button"
                class="wn-upgrade-teaser__cta"
                data-upgrade-trigger="{{ $feature }}">
            {{ $cta }}
        </button>
    </div>
</div>
