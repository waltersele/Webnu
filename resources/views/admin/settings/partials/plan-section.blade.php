<div id="plan" class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Plan y suscripción</h5>
    </div>
    <div class="card-body">
        @if ($hasAccess)
            <div class="alert alert-success mb-4">Tu suscripción está activa. Puedes usar el panel con normalidad.</div>
        @else
            <div class="alert alert-warning mb-4">
                <strong>Acceso restringido.</strong> Activa o renueva tu suscripción para gestionar tu carta.
            </div>
        @endif

        <dl class="row mb-4">
            <dt class="col-sm-4">Estado</dt>
            <dd class="col-sm-8">
                <span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
            </dd>
            <dt class="col-sm-4">Plan</dt>
            <dd class="col-sm-8">{{ $planLabel }}</dd>
            @if ($subscription && $subscription->ends_at)
                <dt class="col-sm-4">Fin / cancelación</dt>
                <dd class="col-sm-8">{{ $subscription->ends_at->format('d/m/Y H:i') }}</dd>
            @endif
            @if ($user->trial_ends_at)
                <dt class="col-sm-4">Prueba hasta</dt>
                <dd class="col-sm-8">{{ $user->trial_ends_at->format('d/m/Y') }}</dd>
            @endif
        </dl>

        <div class="d-flex flex-wrap gap-2">
            @if ($user->stripe_id)
                <form method="POST" action="{{ route('admin.billing.portal') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Gestionar pago en Stripe</button>
                </form>
            @else
                <a href="{{ route('welcome') }}" class="btn btn-primary">Contratar plan</a>
            @endif
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Ir al inicio</a>
        </div>
    </div>
</div>
