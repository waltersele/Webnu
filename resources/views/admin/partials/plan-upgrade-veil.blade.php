@php
    $feature = $feature ?? 'videos';
    $planLabel = $planLabel ?? app(\App\Services\UserPlanService::class)->requiredPlanLabel($feature) ?? 'Plus';
    $billingUrl = $planFeatures['billing_url'] ?? route('admin.billing');
    $message = $message ?? "Disponible con el plan {$planLabel}. Mejora tu suscripción para desbloquear esta función.";
@endphp
<div class="wn-plan-feature-lock__veil">
    <div class="wn-plan-feature-lock__card">
        @include('admin.partials.plan-pro-badge', ['label' => $planLabel])
        <p class="wn-plan-feature-lock__text">{{ $message }}</p>
        <a href="{{ $billingUrl }}" class="btn btn-sm btn-primary">Ver planes</a>
    </div>
</div>
