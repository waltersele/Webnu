@extends('admin.layout')

@section('content')
@php
    $d = $dashboard ?? [];
    $company = $dashboardCompany;
    $next = $d['nextStep'] ?? [];
    $progress = $d['progress'] ?? [];
    $completed = $d['progressCompleted'] ?? 0;
    $currentStep = $d['progressCurrent'] ?? 1;
    $primaryKey = $d['primaryActionKey'] ?? 'add_dishes';
    $pf = $planFeatures ?? [];
    $canScan = $d['canMenuScan'] ?? ($pf['menu_scan'] ?? true);
    $canTvpik = $d['canTvpik'] ?? ($pf['tvpik'] ?? false);
    $billingUrl = $pf['billing_url'] ?? route('admin.settings');

    $scanUrl = $canScan ? route('admin.menu-scan.create') : route('admin.sections.index');
    $qrUrl = $company ? route('admin.qrgenerator', $company) : route('admin.companies.index');
    $printUrl = $company ? route('admin.menu-print', $company) : route('admin.companies.index');
    $menuUrl = $company ? route('see_menu', $company->slug) : '#';
    $sectionsUrl = $company ? route('admin.sections.index') : route('admin.companies.index');
@endphp

<div class="wn-dash">
    @if(!empty($d['hasCompany']) && $company)
        <div class="wn-dash-greeting">
            <h1>Hola, {{ $company->name }}</h1>
            <p>Tu carta ha recibido {{ number_format($d['menuViews'] ?? 0, 0, ',', '.') }} {{ ($d['menuViews'] ?? 0) === 1 ? 'visita' : 'visitas' }} (total acumulado)</p>
        </div>

        <div class="wn-dash-next">
            <div class="wn-dash-next__icon">
                <i class="ti {{ $next['icon'] ?? 'ti-camera' }}"></i>
            </div>
            <div class="wn-dash-next__text">
                <strong>{{ $next['title'] ?? 'Siguiente paso' }}</strong>
                <span>{{ $next['subtitle'] ?? '' }}</span>
            </div>
            <a href="{{ $next['ctaUrl'] ?? $sectionsUrl }}" class="wn-dash-next__btn">
                @if(($next['key'] ?? '') === 'import_dishes')
                    <i class="ti ti-sparkles"></i>
                @endif
                {{ $next['cta'] ?? 'Continuar' }}
            </a>
        </div>

        <div class="wn-dash-progress" role="progressbar" aria-valuenow="{{ $completed }}" aria-valuemin="0" aria-valuemax="4" aria-label="Progreso de configuración">
            @foreach(['account', 'business', 'dishes', 'qr'] as $i => $stepKey)
                @php
                    $isDone = !empty($progress[$stepKey]);
                    $isActive = !$isDone && ($i === $completed);
                @endphp
                <div class="wn-dash-progress__seg {{ $isDone ? 'is-done' : '' }} {{ $isActive ? 'is-active' : '' }}" title="Paso {{ $i + 1 }}"></div>
            @endforeach
            <span class="wn-dash-progress__label">Paso {{ min(4, max(1, $currentStep)) }} de 4</span>
        </div>

        <p class="wn-dash-section-title">¿Qué quieres hacer?</p>
        <div class="wn-dash-actions">
            <a href="{{ $scanUrl }}" class="wn-dash-action {{ $primaryKey === 'add_dishes' ? 'is-primary' : '' }}">
                <div class="wn-dash-action__icon" style="background: #e6f1fb;">
                    <i class="ti ti-camera" style="color: #378add;"></i>
                </div>
                <div>
                    <p class="wn-dash-action__title">Añadir platos</p>
                    <p class="wn-dash-action__desc">Fotografía la carta o escríbelos tú mismo</p>
                </div>
            </a>
            <a href="{{ $qrUrl }}" class="wn-dash-action {{ $primaryKey === 'qr' ? 'is-primary' : '' }}">
                <div class="wn-dash-action__icon" style="background: #f1f5f9;">
                    <i class="ti ti-qrcode" style="color: #64748b;"></i>
                </div>
                <div>
                    <p class="wn-dash-action__title">Mi código QR</p>
                    <p class="wn-dash-action__desc">Descárgalo e imprímelo para las mesas</p>
                </div>
            </a>
            <a href="{{ $menuUrl }}" target="_blank" rel="noopener" class="wn-dash-action {{ $primaryKey === 'view_menu' ? 'is-primary' : '' }}">
                <div class="wn-dash-action__icon" style="background: #f1f5f9;">
                    <i class="ti ti-eye" style="color: #64748b;"></i>
                </div>
                <div>
                    <p class="wn-dash-action__title">Ver mi carta</p>
                    <p class="wn-dash-action__desc">Tal como la ven tus clientes en el móvil</p>
                </div>
            </a>
        </div>

        <p class="wn-dash-section-title">Este mes</p>
        <div class="wn-dash-stats">
            <div class="wn-dash-stat">
                <p class="wn-dash-stat__label">Escaneos QR</p>
                <p class="wn-dash-stat__value is-blue">{{ number_format($d['menuViews'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="wn-dash-stat">
                <p class="wn-dash-stat__label">Platos publicados</p>
                <p class="wn-dash-stat__value">{{ number_format($d['productCount'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <a href="{{ $billingUrl }}" class="wn-dash-stat wn-dash-stat--link" title="Gestionar suscripción">
                <p class="wn-dash-stat__label">Días gratis</p>
                <p class="wn-dash-stat__value is-green">
                    @if(!empty($d['trialActive']) && $d['trialDaysRemaining'] !== null)
                        {{ $d['trialDaysRemaining'] }}
                    @else
                        —
                    @endif
                </p>
            </a>
        </div>

        <div class="wn-dash-qr">
            <div class="wn-dash-qr__icon">
                <i class="ti ti-qrcode"></i>
            </div>
            <div class="wn-dash-qr__info">
                <p class="wn-dash-qr__url">{{ $d['publicPath'] ?? ('webnu.es/carta/' . $company->slug) }}</p>
                <div class="wn-dash-qr__meta">
                    @if(!empty($d['isPublished']))
                        <span class="wn-dash-qr__status">Publicada</span>
                    @endif
                    <span>Plantilla {{ $d['templateLabel'] ?? '—' }}</span>
                </div>
            </div>
            <div class="wn-dash-qr__actions">
                <button type="button" class="wn-dash-btn-secondary" data-bs-toggle="modal" data-bs-target="#modal-share-menu">
                    <i class="ti ti-share"></i> Compartir carta
                </button>
                <a href="{{ $qrUrl }}" target="_blank" rel="noopener" class="wn-dash-btn-secondary">
                    <i class="ti ti-download"></i> Descargar QR
                </a>
                @if($company)
                <a href="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data={{ urlencode(route('see_menu', $company->slug)) }}"
                   target="_blank" rel="noopener" class="wn-dash-btn-secondary" title="Alternativa si el PDF no abre">
                    <i class="ti ti-photo"></i> QR imagen
                </a>
                @endif
                <a href="{{ $d['publicUrl'] ?? $menuUrl }}" target="_blank" rel="noopener" class="wn-dash-btn-secondary">
                    <i class="ti ti-external-link"></i> Ver carta
                </a>
            </div>
        </div>

        <div class="wn-dash-tvpik">
            <div class="wn-dash-tvpik__left">
                <div class="wn-dash-tvpik__icon">
                    <i class="ti ti-device-tv"></i>
                </div>
                <div class="wn-dash-tvpik__text">
                    <strong>Muestra tu carta en la TV con TVPik</strong>
                    <span>Configuras aquí; en TVPik eliges carta y plantilla para cada pantalla</span>
                </div>
            </div>
            <span class="wn-dash-tvpik__badge">TVPik</span>
            @if($canTvpik)
                <a href="{{ route('admin.tvpik.index') }}" class="wn-dash-tvpik__btn">
                    <i class="ti ti-plug"></i> Conectar TV
                </a>
            @else
                <a href="{{ $billingUrl }}" class="wn-dash-tvpik__btn">
                    <i class="ti ti-arrow-up-circle"></i> Plan Ilimitado
                </a>
            @endif
        </div>
    @else
        <div class="wn-dash-greeting">
            <h1>Hola{{ auth()->user() ? ', ' . explode(' ', auth()->user()->name)[0] : '' }}</h1>
            <p>Crea tu primer negocio para tener carta digital, QR y estadísticas.</p>
        </div>

        <div class="wn-dash-next">
            <div class="wn-dash-next__icon">
                <i class="ti {{ $next['icon'] ?? 'ti-store' }}"></i>
            </div>
            <div class="wn-dash-next__text">
                <strong>{{ $next['title'] ?? 'Siguiente paso: crea tu negocio' }}</strong>
                <span>{{ $next['subtitle'] ?? '' }}</span>
            </div>
            <a href="{{ $next['ctaUrl'] ?? route('admin.companies.index') }}" class="wn-dash-next__btn">
                {{ $next['cta'] ?? 'Crear mi negocio' }}
            </a>
        </div>

        <div class="wn-dash-empty">
            <h2>Aún no tienes un restaurante configurado</h2>
            <p>Da de alta tu local, elige plantilla y empieza a añadir platos en minutos.</p>
            <a href="{{ route('admin.companies.index') }}" class="wn-dash-next__btn">Ir a mis negocios</a>
        </div>
    @endif
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('materio/css/webnu-dashboard.css') }}">
@endpush
