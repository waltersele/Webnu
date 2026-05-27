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

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="wn-settings-tabs-wrap mb-4">
            <ul class="nav nav-pills wn-settings-tabs flex-nowrap" id="wn-settings-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $initialTab === 'perfil' ? 'active' : '' }}"
                            id="wn-settings-tab-perfil"
                            data-bs-toggle="tab"
                            data-bs-target="#wn-settings-pane-perfil"
                            data-tab-key="perfil"
                            type="button"
                            role="tab"
                            aria-controls="wn-settings-pane-perfil"
                            aria-selected="{{ $initialTab === 'perfil' ? 'true' : 'false' }}">
                        <i class="ri ri-user-3-line me-1"></i> Perfil
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $initialTab === 'facturacion' ? 'active' : '' }}"
                            id="wn-settings-tab-facturacion"
                            data-bs-toggle="tab"
                            data-bs-target="#wn-settings-pane-facturacion"
                            data-tab-key="facturacion"
                            type="button"
                            role="tab"
                            aria-controls="wn-settings-pane-facturacion"
                            aria-selected="{{ $initialTab === 'facturacion' ? 'true' : 'false' }}">
                        <i class="ri ri-bill-line me-1"></i> Facturación
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $initialTab === 'plan' ? 'active' : '' }}"
                            id="wn-settings-tab-plan"
                            data-bs-toggle="tab"
                            data-bs-target="#wn-settings-pane-plan"
                            data-tab-key="plan"
                            type="button"
                            role="tab"
                            aria-controls="wn-settings-pane-plan"
                            aria-selected="{{ $initialTab === 'plan' ? 'true' : 'false' }}">
                        <i class="ri ri-vip-crown-line me-1"></i> Plan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $initialTab === 'app' ? 'active' : '' }}"
                            id="wn-settings-tab-app"
                            data-bs-toggle="tab"
                            data-bs-target="#wn-settings-pane-app"
                            data-tab-key="app"
                            type="button"
                            role="tab"
                            aria-controls="wn-settings-pane-app"
                            aria-selected="{{ $initialTab === 'app' ? 'true' : 'false' }}">
                        <i class="ri ri-smartphone-line me-1"></i> App
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="wn-settings-tab-content">
            <div class="tab-pane fade {{ $initialTab === 'perfil' ? 'show active' : '' }}"
                 id="wn-settings-pane-perfil"
                 role="tabpanel"
                 aria-labelledby="wn-settings-tab-perfil">
                <div class="card mb-4" id="perfil">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Datos personales</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.settings.profile') }}">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="settings-name">Nombre</label>
                                    <input type="text" class="form-control" id="settings-name" name="name" value="{{ old('name', $user->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="settings-email">Email</label>
                                    <input type="email" class="form-control" id="settings-email" value="{{ $user->email }}" readonly disabled>
                                    <div class="form-text">El email no se puede cambiar desde aquí.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="settings-phone">Teléfono</label>
                                    <input type="text" class="form-control" id="settings-phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+34 600 000 000">
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Guardar datos personales</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $initialTab === 'facturacion' ? 'show active' : '' }}"
                 id="wn-settings-pane-facturacion"
                 role="tabpanel"
                 aria-labelledby="wn-settings-tab-facturacion">
                <div class="card mb-4" id="facturacion">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Datos de facturación</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.settings.billing-info') }}">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label" for="settings-legal-name">Razón social / nombre fiscal</label>
                                    <input type="text" class="form-control" id="settings-legal-name" name="legal_name" value="{{ old('legal_name', $user->legal_name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="settings-tax-id">NIF / CIF / nº IVA</label>
                                    <input type="text" class="form-control" id="settings-tax-id" name="tax_id" value="{{ old('tax_id', $user->tax_id) }}" placeholder="B12345678">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="settings-country">País</label>
                                    <input type="text" class="form-control" id="settings-country" name="billing_country" value="{{ old('billing_country', $user->billing_country ?: 'ES') }}" maxlength="2" placeholder="ES">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="settings-address">Dirección fiscal</label>
                                    <input type="text" class="form-control" id="settings-address" name="billing_address" value="{{ old('billing_address', $user->billing_address) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="settings-postal">Código postal</label>
                                    <input type="text" class="form-control" id="settings-postal" name="billing_postal_code" value="{{ old('billing_postal_code', $user->billing_postal_code) }}">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label" for="settings-city">Ciudad</label>
                                    <input type="text" class="form-control" id="settings-city" name="billing_city" value="{{ old('billing_city', $user->billing_city) }}">
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Guardar datos de facturación</button>
                            </div>
                        </form>
                    </div>
                </div>
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
</div>
@endsection

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
