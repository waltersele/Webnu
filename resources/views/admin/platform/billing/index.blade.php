@extends('admin.layout')

@section('page_title', 'Facturación Stripe')
@section('page_subtitle', 'Precios y suscripciones de la plataforma')

@push('styles')
<link rel="stylesheet" href="{{ asset('materio/css/webnu-platform.css') }}">
@endpush

@section('page_actions')
    <a href="{{ route('admin.platform.settings') }}" class="btn btn-outline-primary btn-sm">Claves Stripe</a>
    <a href="{{ route('admin.platform.dashboard') }}" class="btn btn-outline-secondary btn-sm">Dashboard</a>
@endsection

@section('content')
@if (! $stripeConfigured)
    <div class="alert alert-warning">
        <strong>Stripe no configurado.</strong>
        Añade la clave secreta en <a href="{{ $settingsUrl }}" class="alert-link">Plataforma → Configuración → Integraciones</a>
        (o <code>STRIPE_SECRET</code> en <code>.env</code>) y luego crea los precios aquí.
    </div>
@else
    <div class="alert alert-info">
        <strong>Cuenta Stripe nueva:</strong> si los <code>price_…</code> guardados son de otra cuenta, pulsa
        «Borrar catálogo local» y después «Crear todos los que falten».
    </div>
@endif

<div class="card mb-4">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h6 class="mb-0">Catálogo de precios (sin IVA)</h6>
        <div class="d-flex flex-wrap gap-2">
            @if ($stripeConfigured)
                <form method="POST" action="{{ route('admin.platform.billing.create-all') }}" class="d-inline"
                      onsubmit="return confirm('¿Crear en Stripe todos los precios que falten?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary">Crear todos los que falten</button>
                </form>
            @endif
            <form method="POST" action="{{ route('admin.platform.billing.clear-catalog') }}" class="d-inline"
                  onsubmit="return confirm('¿Borrar todos los Price ID y Product ID guardados en Webnu? No borra nada en Stripe.');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger">Borrar catálogo local</button>
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Importe</th>
                    <th>Suscripción Cashier</th>
                    <th>Price ID</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($catalog as $row)
                    <tr>
                        <td>{{ $row['label'] ?? $row['key'] }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.platform.billing.save-amount') }}" class="d-flex gap-1 align-items-center">
                                @csrf
                                <input type="hidden" name="catalog_key" value="{{ $row['key'] }}">
                                <input type="number" name="amount_eur" class="form-control form-control-sm" style="max-width:6rem"
                                       value="{{ $row['amount_eur'] ?? '0' }}" step="0.01" min="0.01" required>
                                <span class="small text-muted">€ / {{ $row['interval'] ?? 'month' }}</span>
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Guardar</button>
                            </form>
                        </td>
                        <td><code class="small">{{ $row['subscription_name'] ?? '—' }}</code></td>
                        <td>
                            @if (! empty($row['configured']))
                                <span class="badge bg-label-success">OK</span>
                                <code class="small d-block mt-1">{{ $row['price_id'] }}</code>
                            @else
                                <span class="badge bg-label-warning">Pendiente</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            @if ($stripeConfigured)
                                @if (empty($row['configured']))
                                    <form method="POST" action="{{ route('admin.platform.billing.create-price') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="catalog_key" value="{{ $row['key'] }}">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Crear en Stripe</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.platform.billing.recreate-price') }}" class="d-inline"
                                          onsubmit="return confirm('¿Crear un nuevo price_… en Stripe con el importe guardado? Las suscripciones actuales no cambian solas.');">
                                        @csrf
                                        <input type="hidden" name="catalog_key" value="{{ $row['key'] }}">
                                        <button type="submit" class="btn btn-sm btn-outline-warning">Recrear</button>
                                    </form>
                                @endif
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#price-form-{{ $row['key'] }}">Pegar ID</button>
                        </td>
                    </tr>
                    <tr class="collapse" id="price-form-{{ $row['key'] }}">
                        <td colspan="5" class="bg-light">
                            <form method="POST" action="{{ route('admin.platform.billing.save-price-id') }}" class="row g-2 align-items-end">
                                @csrf
                                <input type="hidden" name="catalog_key" value="{{ $row['key'] }}">
                                <div class="col-md-8">
                                    <label class="form-label small mb-0">Price ID (price_…)</label>
                                    <input type="text" name="price_id" class="form-control form-control-sm" value="{{ $row['price_id'] ?? '' }}" placeholder="price_...">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">Guardar ID</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-body border-top small text-muted">
        Los IDs se guardan en <strong>platform_settings</strong> (prioridad sobre <code>.env</code>).
        Los importes editables se usan al crear o recrear precios en Stripe.
        El checkout en <a href="{{ route('welcome') }}">/welcome</a> usa estos <code>price_…</code>.
        <a href="{{ $stripeDashboardUrl }}" target="_blank" rel="noopener">Abrir Stripe Dashboard</a>
    </div>
</div>

<div class="card">
    <div class="card-header"><h6 class="mb-0">¿Qué incluye el panel de facturación?</h6></div>
    <div class="card-body small">
        <ul class="mb-0">
            <li><strong>Aquí:</strong> importes, crear y enlazar precios Stripe (Pro, Plus, TVPik).</li>
            <li><strong><a href="{{ route('admin.platform.settings') }}">Configuración</a>:</strong> claves pk/sk/whsec de Stripe.</li>
            <li><strong><a href="{{ route('admin.platform.users.index') }}">Clientes</a>:</strong> suscripción, facturas, plan manual.</li>
        </ul>
    </div>
</div>
@endsection
