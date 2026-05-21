@extends('admin.layout')

@section('page_title', 'Configuración')
@section('page_subtitle', 'Tu cuenta, facturación y preferencias')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
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

        @include('admin.settings.partials.plan-section')
        @include('admin.settings.partials.pwa-section')
    </div>
</div>
@endsection
