@php
    $feature = $feature ?? 'videos';
    $planLabel = $planLabel ?? app(\App\Services\UserPlanService::class)->requiredPlanLabel($feature) ?? 'Plus';
    $billingUrl = $planFeatures['billing_url'] ?? route('admin.settings');
    $message = $message ?? "Disponible con el plan {$planLabel}. Mejora tu suscripción para desbloquear esta función.";
    $lockSlot = $slot ?? '';
@endphp
<div class="wn-plan-feature-lock">
    <div class="wn-plan-feature-lock__content{{ isset($class) && $class ? ' ' . $class : '' }}">
        {!! $lockSlot !!}
    </div>
    @include('admin.partials.plan-upgrade-veil')
</div>
