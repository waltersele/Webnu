@php
    $feature = $feature ?? 'videos';
    $planLabel = $planLabel ?? app(\App\Services\UserPlanService::class)->requiredPlanLabel($feature) ?? 'Plus';
    $billingUrl = $planFeatures['billing_url'] ?? route('admin.billing');
    $message = $message ?? "Disponible con el plan {$planLabel}. Mejora tu suscripción para desbloquear esta función.";
@endphp
<div class="wn-plan-feature-lock">
    <div class="wn-plan-feature-lock__content{{ isset($class) && $class ? ' ' . $class : '' }}">
        {{ $slot }}
    </div>
    @include('admin.partials.plan-upgrade-veil')
</div>
