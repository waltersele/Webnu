@extends('admin.layout')

@section('page_title', $user->email)
@section('page_subtitle', 'Detalle del cliente')

@push('styles')
<link rel="stylesheet" href="{{ asset('materio/css/webnu-platform.css') }}">
@endpush

@section('page_actions')
    <a href="{{ route('admin.platform.users.index') }}" class="btn btn-outline-secondary btn-sm">Volver al listado</a>
@endsection

@section('content')
@php
    $subscription = $user->primarySubscription();
    $stripeUrl = $presenter->stripeCustomerUrl($user);
@endphp

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Cuenta</h6></div>
            <div class="card-body">
                <dl class="mb-0">
                    <dt>Email</dt>
                    <dd>{{ $user->email }}</dd>
                    <dt>Nombre</dt>
                    <dd>{{ $user->name ?: '—' }}</dd>
                    <dt>Registro</dt>
                    <dd>{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                    <dt>Superadmin</dt>
                    <dd>{{ $user->isSuperAdmin() ? 'Sí' : 'No' }}</dd>
                </dl>
                @if (! $user->hasRole('super-admin'))
                    <form method="POST" action="{{ route('admin.platform.users.grant-super-admin', $user) }}" class="mt-3" onsubmit="return confirm('¿Asignar rol super-admin a este usuario?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">Asignar super-admin</button>
                    </form>
                @endif
                <hr class="my-3">
                <dt>Comercial</dt>
                <dd>{{ $user->isSalesRep() ? 'Sí' : 'No' }}</dd>
                @if (! $user->isSalesRep())
                    <form method="POST" action="{{ route('admin.platform.users.grant-sales-rep', $user) }}" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success">Asignar rol comercial</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.platform.users.revoke-sales-rep', $user) }}" class="mt-2" onsubmit="return confirm('¿Quitar acceso comercial?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">Quitar rol comercial</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Suscripción</h6></div>
            <div class="card-body">
                <p>
                    <span class="badge {{ $presenter->statusBadgeClass($user) }}">{{ $presenter->statusLabel($user) }}</span>
                    <span class="ms-2">{{ $presenter->planLabel($user) }}</span>
                </p>
                <p class="text-muted small mb-3">Tarjeta: {{ $presenter->cardSummary($user) }}</p>
                @if ($subscription && $subscription->ends_at)
                    <p class="small">Cancelación / fin: {{ $subscription->ends_at->format('d/m/Y H:i') }}</p>
                @endif
                <div class="d-flex flex-wrap gap-2">
                    @if ($stripeUrl)
                        <a href="{{ $stripeUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">Ver en Stripe</a>
                    @endif
                    @if ($subscription && $subscription->stripe_status !== 'canceled' && ! $subscription->ends_at)
                        <form method="POST" action="{{ route('admin.platform.users.cancel-subscription', $user) }}" onsubmit="return confirm('¿Cancelar al final del periodo actual?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">Cancelar suscripción</button>
                        </form>
                    @endif
                    @if ($subscription && $subscription->ends_at && $subscription->ends_at->isFuture())
                        <form method="POST" action="{{ route('admin.platform.users.resume-subscription', $user) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success">Reanudar</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header"><h6 class="mb-0">Negocios ({{ $user->companies->count() }})</h6></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Visible</th>
                    @if (\Schema::hasColumn('companies', 'menu_views'))
                        <th>Visitas</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($user->companies as $company)
                    <tr>
                        <td>{{ $company->name }}</td>
                        <td><code>{{ $company->slug }}</code></td>
                        <td>{{ $company->enabled ? 'Sí' : 'No' }}</td>
                        @if (\Schema::hasColumn('companies', 'menu_views'))
                            <td>{{ $company->menu_views ?? 0 }}</td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center py-3">Sin negocios</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header"><h6 class="mb-0">Facturas (Stripe)</h6></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Importe</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->date()->format('d/m/Y') }}</td>
                        <td>{{ $invoice->total() }}</td>
                        <td>{{ $invoice->status ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ $invoice->hosted_invoice_url ?? '#' }}" target="_blank" rel="noopener" class="btn btn-sm btn-link">PDF / ver</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center py-3">Sin facturas o cliente sin Stripe</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

