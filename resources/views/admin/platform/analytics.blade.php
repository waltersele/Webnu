@extends('admin.layout')

@section('page_title', 'Analítica')
@section('page_subtitle', 'Datos first-party para decidir: landing, altas, cartas y embudo')

@push('styles')
<link rel="stylesheet" href="{{ asset('materio/css/webnu-platform.css') }}">
<style>
.wn-an-kpi h3 { font-size: 1.75rem; letter-spacing: -0.02em; }
.wn-an-delta-up { color: #198754; }
.wn-an-delta-down { color: #dc3545; }
.wn-an-bars { display: flex; align-items: flex-end; gap: 4px; height: 140px; padding: 8px 0; }
.wn-an-bars span { flex: 1; background: #696cff; border-radius: 4px 4px 0 0; min-height: 2px; opacity: .85; }
.wn-an-bars span.wn-an-alt { background: #03c3ec; }
.wn-an-funnel li { display: flex; justify-content: space-between; padding: .55rem 0; border-bottom: 1px solid rgba(0,0,0,.06); }
.wn-an-table td, .wn-an-table th { white-space: nowrap; }
.wn-an-note { font-size: .85rem; color: #697a8d; }
</style>
@endpush

@section('page_actions')
    <div class="btn-group btn-group-sm" role="group">
        @foreach([7, 30, 90] as $d)
            <a href="{{ route('admin.platform.analytics', ['days' => $d]) }}"
               class="btn {{ $analytics['days'] === $d ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $d }}d</a>
        @endforeach
    </div>
    <a href="{{ route('admin.platform.dashboard') }}" class="btn btn-outline-secondary btn-sm">Dashboard</a>
@endsection

@section('content')
@php
    $k = $analytics['kpis'];
    $series = $analytics['series'];
    $maxPv = max(1, max($series['pageviews'] ?: [0]));
@endphp

@unless($analytics['tables_ready'])
<div class="alert alert-warning">
    Faltan tablas de analítica. En el servidor ejecuta
    <code>php artisan migrate</code>.
</div>
@endunless

<p class="wn-an-note mb-3">
    Periodo: {{ $analytics['from']->format('d/m/Y') }} — {{ $analytics['to']->format('d/m/Y') }}.
    Datos propios de Webnu (landing + producto). No depende de Plausible ni de que el visitante acepte cookies de Google.
</p>

<div class="row g-3 wn-platform-metrics">
    <div class="col-6 col-xl-3">
        <div class="card h-100"><div class="card-body wn-an-kpi">
            <span class="text-muted small">Visitas landing</span>
            <h3 class="mb-0 mt-1">{{ number_format($k['landing_views']) }}</h3>
            <div class="small text-muted">{{ number_format($k['landing_uniques']) }} visitantes únicos/día</div>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100"><div class="card-body wn-an-kpi">
            <span class="text-muted small">Clics CTA landing</span>
            <h3 class="mb-0 mt-1">{{ number_format($k['cta_clicks']) }}</h3>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-primary"><div class="card-body wn-an-kpi">
            <span class="text-muted small">Altas nuevas</span>
            <h3 class="mb-0 mt-1">{{ number_format($k['signups']) }}</h3>
            @php $delta = $k['signups_delta_pct']; @endphp
            <div class="small {{ $delta >= 0 ? 'wn-an-delta-up' : 'wn-an-delta-down' }}">
                {{ $delta >= 0 ? '+' : '' }}{{ $delta }}% vs periodo anterior
            </div>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100"><div class="card-body wn-an-kpi">
            <span class="text-muted small">Onboarding completado</span>
            <h3 class="mb-0 mt-1">{{ number_format($k['onboarding_completed']) }}</h3>
            <div class="small text-muted">{{ $k['onboarding_rate_pct'] }}% de las altas</div>
        </div></div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-6 col-xl-3">
        <div class="card h-100"><div class="card-body wn-an-kpi">
            <span class="text-muted small">Vistas de carta (periodo)</span>
            <h3 class="mb-0 mt-1">{{ number_format($k['menu_views']) }}</h3>
            <div class="small text-muted">QR: {{ number_format($k['menu_views_qr']) }} · lifetime {{ number_format($k['menu_views_lifetime']) }}</div>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-success"><div class="card-body wn-an-kpi">
            <span class="text-muted small">Suscripciones activas</span>
            <h3 class="mb-0 mt-1 text-success">{{ $analytics['billing']['active_subscriptions'] }}</h3>
            <div class="small text-muted">MRR ~ {{ number_format($analytics['billing']['estimated_mrr_eur'], 2, ',', '.') }} €</div>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-warning"><div class="card-body wn-an-kpi">
            <span class="text-muted small">En trial</span>
            <h3 class="mb-0 mt-1">{{ $analytics['billing']['trialing'] }}</h3>
            <div class="small text-muted">Past due: {{ $analytics['billing']['past_due'] }}</div>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100"><div class="card-body wn-an-kpi">
            <span class="text-muted small">Handoffs comercial (mes)</span>
            <h3 class="mb-0 mt-1">{{ $k['handoffs_month'] }}</h3>
            <div class="small text-muted">Convertidos: {{ $k['handoffs_converted'] }} ({{ $k['handoff_rate_pct'] }}%)</div>
        </div></div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Visitas landing (pageviews / día)</h6>
                <span class="small text-muted">azul = pageviews</span>
            </div>
            <div class="card-body">
                <div class="wn-an-bars" title="Pageviews diarios">
                    @foreach($series['pageviews'] as $v)
                        <span style="height: {{ max(2, (int) round(($v / $maxPv) * 100)) }}%" title="{{ $v }}"></span>
                    @endforeach
                </div>
                <div class="d-flex justify-content-between small text-muted">
                    <span>{{ $series['labels'][0] ?? '' }}</span>
                    <span>{{ $series['labels'][count($series['labels']) - 1] ?? '' }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Embudo</h6></div>
            <div class="card-body">
                <ul class="list-unstyled wn-an-funnel mb-0">
                    @foreach($analytics['funnel'] as $step)
                        <li>
                            <span>{{ $step['label'] }}</span>
                            <strong>{{ number_format($step['value']) }}</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Top páginas landing</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 wn-an-table">
                        <thead><tr><th>Ruta</th><th class="text-end">Vistas</th></tr></thead>
                        <tbody>
                        @forelse($analytics['top_paths'] as $row)
                            <tr><td><code>{{ $row->dimension }}</code></td><td class="text-end">{{ number_format($row->total) }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-muted px-3 py-3">Aún no hay datos. Visita la landing.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Top referencias</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 wn-an-table">
                        <thead><tr><th>Origen</th><th class="text-end">Vistas</th></tr></thead>
                        <tbody>
                        @forelse($analytics['top_referrers'] as $row)
                            <tr><td>{{ $row->dimension }}</td><td class="text-end">{{ number_format($row->total) }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-muted px-3 py-3">Sin referencias externas aún.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">CTAs más clicados</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 wn-an-table">
                        <thead><tr><th>CTA</th><th class="text-end">Clics</th></tr></thead>
                        <tbody>
                        @forelse($analytics['top_ctas'] as $row)
                            <tr><td><code>{{ $row->dimension }}</code></td><td class="text-end">{{ number_format($row->total) }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-muted px-3 py-3">Añade <code>data-wn-analytics-cta</code> en botones.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Cartas más vistas (lifetime)</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 wn-an-table">
                        <thead><tr><th>Negocio</th><th>Slug</th><th class="text-end">Vistas</th></tr></thead>
                        <tbody>
                        @forelse($analytics['top_companies'] as $company)
                            <tr>
                                <td>{{ $company->name }}</td>
                                <td><code>{{ $company->slug }}</code></td>
                                <td class="text-end">{{ number_format($company->menu_views ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted px-3 py-3">Sin datos de cartas.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Altas por origen</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 wn-an-table">
                        <thead><tr><th>Origen</th><th class="text-end">Altas</th></tr></thead>
                        <tbody>
                        @forelse($analytics['signup_sources'] as $row)
                            <tr><td>{{ $row->dimension }}</td><td class="text-end">{{ number_format($row->total) }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-muted px-3 py-3">Las próximas altas se contabilizarán aquí.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="fw-semibold">Cómo leer esto</h6>
                <ul class="small text-muted mb-0 ps-3">
                    <li><strong>Landing</strong>: cada visita a páginas con el beacon (home, legales, blog…).</li>
                    <li><strong>Únicos</strong>: huella diaria anónima (IP+UA hasheada); no identifica personas.</li>
                    <li><strong>GA4</strong> sigue aparte para marketing de quien acepta cookies.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
