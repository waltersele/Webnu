@extends('admin.layout')

@section('page_title', 'Plataforma')
@section('page_subtitle', 'Resumen de clientes y facturación')

@push('styles')
<link rel="stylesheet" href="{{ asset('materio/css/webnu-platform.css') }}">
@endpush

@section('page_actions')
    <a href="{{ route('admin.platform.users.index') }}" class="btn btn-primary btn-sm">
        <i class="ri-team-line me-1"></i> Ver clientes
    </a>
@endsection

@section('content')
<div class="row g-4 wn-platform-metrics">
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <span class="text-muted small">Clientes totales</span>
                <h3 class="mb-0 mt-1">{{ $metrics['total_users'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-success">
            <div class="card-body">
                <span class="text-muted small">Suscripciones activas</span>
                <h3 class="mb-0 mt-1 text-success">{{ $metrics['active_subscriptions'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-warning">
            <div class="card-body">
                <span class="text-muted small">Impagados / past_due</span>
                <h3 class="mb-0 mt-1 text-warning">{{ $metrics['past_due'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-primary">
            <div class="card-body">
                <span class="text-muted small">MRR estimado</span>
                <h3 class="mb-0 mt-1">{{ number_format($metrics['estimated_mrr_eur'], 2, ',', '.') }} €</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-semibold">En prueba</h6>
                <p class="display-6 mb-0">{{ $metrics['trialing'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-semibold">Sin suscripción</h6>
                <p class="display-6 mb-0">{{ $metrics['without_subscription'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-semibold">Negocios en plataforma</h6>
                <p class="display-6 mb-0">{{ $metrics['total_companies'] }}</p>
            </div>
        </div>
    </div>
</div>
@endsection


