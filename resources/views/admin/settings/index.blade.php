@extends('admin.layout')

@section('page_title', 'Configuración')
@section('page_subtitle', 'Tu cuenta, facturación y preferencias')

@section('content')
@php
    $allowedTabs = ['perfil', 'facturacion', 'plan', 'app'];
    $initialTab = request()->query('tab');
    if (! in_array($initialTab, $allowedTabs, true)) {
        $initialTab = 'perfil';
    }
@endphp

<div class="wn-settings">
    <div class="wn-settings-nav" id="wn-settings-tabs" role="tablist" aria-label="Pestañas de configuración">
        <div class="wn-settings-nav__item" role="presentation">
            <button class="wn-settings-nav__btn {{ $initialTab === 'perfil' ? 'active is-active' : '' }}"
                    id="wn-settings-tab-perfil"
                    data-bs-toggle="tab"
                    data-bs-target="#wn-settings-pane-perfil"
                    data-tab-key="perfil"
                    type="button"
                    role="tab"
                    aria-controls="wn-settings-pane-perfil"
                    aria-selected="{{ $initialTab === 'perfil' ? 'true' : 'false' }}">
                <i class="ti ti-user"></i> Perfil
            </button>
        </div>
        <div class="wn-settings-nav__item" role="presentation">
            <button class="wn-settings-nav__btn {{ $initialTab === 'facturacion' ? 'active is-active' : '' }}"
                    id="wn-settings-tab-facturacion"
                    data-bs-toggle="tab"
                    data-bs-target="#wn-settings-pane-facturacion"
                    data-tab-key="facturacion"
                    type="button"
                    role="tab"
                    aria-controls="wn-settings-pane-facturacion"
                    aria-selected="{{ $initialTab === 'facturacion' ? 'true' : 'false' }}">
                <i class="ti ti-receipt"></i> Facturación
            </button>
        </div>
        <div class="wn-settings-nav__item" role="presentation">
            <button class="wn-settings-nav__btn {{ $initialTab === 'plan' ? 'active is-active' : '' }}"
                    id="wn-settings-tab-plan"
                    data-bs-toggle="tab"
                    data-bs-target="#wn-settings-pane-plan"
                    data-tab-key="plan"
                    type="button"
                    role="tab"
                    aria-controls="wn-settings-pane-plan"
                    aria-selected="{{ $initialTab === 'plan' ? 'true' : 'false' }}">
                <i class="ti ti-crown"></i> Plan
            </button>
        </div>
        <div class="wn-settings-nav__item" role="presentation">
            <button class="wn-settings-nav__btn {{ $initialTab === 'app' ? 'active is-active' : '' }}"
                    id="wn-settings-tab-app"
                    data-bs-toggle="tab"
                    data-bs-target="#wn-settings-pane-app"
                    data-tab-key="app"
                    type="button"
                    role="tab"
                    aria-controls="wn-settings-pane-app"
                    aria-selected="{{ $initialTab === 'app' ? 'true' : 'false' }}">
                <i class="ti ti-device-mobile"></i> App
            </button>
        </div>
    </div>

    <div class="tab-content" id="wn-settings-tab-content">
        <div class="tab-pane fade {{ $initialTab === 'perfil' ? 'show active' : '' }}"
             id="wn-settings-pane-perfil"
             role="tabpanel"
             aria-labelledby="wn-settings-tab-perfil">
            @include('admin.settings.partials.profile-section')
        </div>

        <div class="tab-pane fade {{ $initialTab === 'facturacion' ? 'show active' : '' }}"
             id="wn-settings-pane-facturacion"
             role="tabpanel"
             aria-labelledby="wn-settings-tab-facturacion">
            @include('admin.settings.partials.billing-section')
        </div>

        <div class="tab-pane fade {{ $initialTab === 'plan' ? 'show active' : '' }}"
             id="wn-settings-pane-plan"
             role="tabpanel"
             aria-labelledby="wn-settings-tab-plan">
            @include('admin.settings.partials.plan-section')
        </div>

        <div class="tab-pane fade {{ $initialTab === 'app' ? 'show active' : '' }}"
             id="wn-settings-pane-app"
             role="tabpanel"
             aria-labelledby="wn-settings-tab-app">
            @include('admin.settings.partials.pwa-section')
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('materio/css/webnu-settings.css') }}">
@endpush

@push('scripts')
<script>
(function () {
    var triggers = document.querySelectorAll('#wn-settings-tabs [data-tab-key]');
    if (!triggers.length) return;

    function syncQueryParam(key) {
        if (!window.history || !window.history.replaceState) return;
        var url = new URL(window.location.href);
        if (key === 'perfil') {
            url.searchParams.delete('tab');
        } else {
            url.searchParams.set('tab', key);
        }
        window.history.replaceState({}, '', url.toString());
    }

    triggers.forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function (e) {
            syncQueryParam(btn.getAttribute('data-tab-key'));
        });
    });

    var hash = (window.location.hash || '').replace('#', '');
    var hashMap = { perfil: 'perfil', facturacion: 'facturacion', plan: 'plan', app: 'app' };
    if (hash && hashMap[hash]) {
        var target = document.querySelector('[data-tab-key="' + hashMap[hash] + '"]');
        if (target && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
            new bootstrap.Tab(target).show();
        }
    }
})();
</script>
@endpush
