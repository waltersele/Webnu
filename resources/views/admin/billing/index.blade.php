@extends('admin.layout')

@section('page_title', 'Suscripción')
@section('page_subtitle', 'Estado de tu plan y método de pago')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                @if ($hasAccess)
                    <div class="alert alert-success">Tu suscripción está activa. Puedes usar el panel con normalidad.</div>
                @else
                    <div class="alert alert-warning">
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
                    @if ($hasAccess)
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Ir al panel</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

