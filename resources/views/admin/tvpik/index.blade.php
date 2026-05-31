@extends('admin.layout')

@section('page_title', 'Pantallas')
@section('page_subtitle', 'Elige carta y plantilla para cada TV del local')

@section('content')
@php
    $defaultCompanyId = $company ? $company->id : ($companies->first()->id ?? null);
    $canTvpikPremium = ! empty($planFeatures['tvpik_premium_templates'] ?? false);
@endphp

<div class="row g-4 wn-tvpik-page">
    @if(!$canTvpik)
        <div class="col-12">
            <div class="card border-0 shadow-sm wn-tvpik-upgrade-cta">
                <div class="card-body d-flex flex-wrap align-items-center gap-3">
                    <div class="wn-tvpik-upgrade-cta__icon">
                        <i class="ti ti-device-tv"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-1">Pantallas TV</h5>
                        <p class="text-muted mb-0 small">
                            Muestra tu carta en cualquier TV del local en tiempo real. Disponible en el plan <strong>Plus</strong> (1 pantalla incluida y plantillas premium) o como add-on en Pro (plantillas estándar).
                        </p>
                    </div>
                    <a href="{{ route('admin.settings') }}#plan" class="btn btn-primary flex-shrink-0">
                        <i class="ti ti-crown me-1"></i> Ver planes
                    </a>
                </div>
            </div>
        </div>
    @else
        @error('publish')
            <div class="col-12"><div class="alert alert-danger">{{ $message }}</div></div>
        @enderror

        <div class="col-12">
            @include('admin.tvpik.partials.connection-banner')
            @include('admin.tvpik.partials.advanced-panel')
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="card-title mb-0">Mis pantallas</h5>
                    <div class="d-flex flex-wrap gap-2">
                        @if($tvpikConnected)
                            <button type="button"
                                    class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#wn-tvpik-new-screen-modal">
                                <i class="ti ti-plus me-1"></i> Nueva pantalla
                            </button>
                        @endif
                        @if($company && (int) $company->menu_type === 1)
                            <form method="POST" action="{{ route('admin.tvpik.publish-all') }}" class="mb-0">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                    <i class="ti ti-refresh me-1"></i> Republicar todas
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @error('screen')
                        <div class="alert alert-danger mb-3">{{ $message }}</div>
                    @enderror
                    @error('pair')
                        <div class="alert alert-danger mb-3">{{ $message }}</div>
                    @enderror
                    @if($screensError)
                        <div class="alert alert-warning mb-3">{{ $screensError }}</div>
                    @endif
                    @if(empty($screens))
                        <div class="wn-tvpik-empty">
                            <div class="wn-tvpik-empty__icon"><i class="ti ti-device-tv"></i></div>
                            <p class="mb-2">Aún no hay pantallas</p>
                            <p class="text-muted small mb-3">
                                Crea una pantalla, instala la app TVPik en la TV e introduce el código de emparejamiento.
                                También puedes usar el reproductor rápido para emitir por HDMI.
                            </p>
                            @if($tvpikConnected)
                                <button type="button"
                                        class="btn btn-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#wn-tvpik-new-screen-modal">
                                    <i class="ti ti-plus me-1"></i> Nueva pantalla
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="row g-3" id="wn-tvpik-screens-grid" data-poll-url="{{ route('admin.tvpik.screens-json') }}">
                            @foreach($screens as $screen)
                                @include('admin.tvpik.partials.screen-card', [
                                    'screen' => $screen,
                                    'links' => $links,
                                    'companies' => $companies,
                                    'templates' => $templates,
                                    'menusByCompany' => $menusByCompany,
                                    'defaultCompanyId' => $defaultCompanyId,
                                    'canTvpikPremium' => $canTvpikPremium,
                                ])
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @include('admin.tvpik.partials.modals')

        @include('admin.tvpik.partials.player-mode-card', [
            'defaultCompanyId' => $defaultCompanyId,
            'companies' => $companies,
            'templates' => $templates,
        ])
    @endif

    <div class="col-12">
        <div class="card border-0 shadow-sm wn-tvpik-gallery-wrap {{ !$canTvpik ? 'wn-tvpik-gallery-wrap--locked' : '' }}">
            <div class="card-header bg-transparent d-flex align-items-center gap-2">
                <h5 class="card-title mb-0">Explorar plantillas</h5>
                @if(!$canTvpik)
                    <span class="badge bg-label-warning ms-auto">
                        <i class="ti ti-lock me-1"></i> Requiere plan Plus
                    </span>
                @endif
            </div>
            <div class="card-body">
                @if(!$canTvpik)
                    <p class="text-muted small mb-4">
                        Así se vería tu carta en las pantallas del local. Activa el plan Plus para empezar.
                    </p>
                @else
                    <p class="text-muted small mb-3">
                        Cada plantilla genera una URL distinta. Las premium requieren plan Plus.
                    </p>
                @endif
                @include('admin.tvpik.partials.template-gallery', [
                    'templates' => $templates,
                    'canTvpik' => $canTvpik,
                    'canTvpikPremium' => $canTvpikPremium,
                    'defaultCompanyId' => $defaultCompanyId,
                    'company' => $company,
                    'companies' => $companies,
                    'showFilter' => true,
                ])
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('adminlte/css/webnu-tvpik.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('materio/js/webnu-tvpik-admin.js') }}" defer></script>
@endpush
