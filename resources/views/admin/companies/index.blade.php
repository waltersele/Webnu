@extends('admin.layout')

@section('page_title', 'Negocios')
@section('page_subtitle', 'Gestiona tus establecimientos y la apariencia de cada carta.')

@section('page_actions')
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-add-company">
        <i class="ri-add-line me-1"></i> Añadir negocio
    </button>
@endsection

@section('content')
@php
    $templateLabels = collect(config('company_templates.templates', []))->mapWithKeys(fn ($t, $k) => [$k => $t['label'] ?? $k]);
@endphp

@if($companies->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="avatar avatar-xl mx-auto mb-3">
                <span class="avatar-initial rounded bg-label-primary">
                    <i class="ri-store-2-line ri-32px"></i>
                </span>
            </div>
            <h5 class="mb-2">Aún no tienes negocios</h5>
            <p class="text-muted mb-4">Crea tu primer establecimiento para empezar con la carta digital.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-company">
                <i class="ri-add-line me-1"></i> Crear negocio
            </button>
        </div>
    </div>
@else
    <div class="row g-4">
        @foreach ($companies as $company)
            @php
                $tplKey = $company->template ?: 'basic';
                $tplLabel = $templateLabels[$tplKey] ?? ucfirst($tplKey);
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm wn-company-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="wn-company-card__avatar flex-shrink-0">
                                @if($company->logo)
                                    <img src="{{ asset('img/' . $company->logo) }}" alt="">
                                @else
                                    <span>{{ mb_strtoupper(mb_substr($company->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-grow-1">
                                <h5 class="card-title mb-1 text-truncate">{{ $company->name }}</h5>
                                <p class="text-muted small mb-1"><code>/carta/{{ $company->slug }}</code></p>
                                <p class="text-muted small mb-2">
                                    <i class="ri-map-pin-line"></i>
                                    {{ $company->city ?: 'Sin localidad' }}
                                </p>
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="badge bg-label-primary">{{ $tplLabel }}</span>
                                    @if($company->enabled)
                                        <span class="badge bg-label-success">Publicada</span>
                                    @else
                                        <span class="badge bg-label-warning">Borrador</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-primary btn-sm">
                                <i class="ri-settings-3-line me-1"></i> Configurar
                            </a>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('see_menu', $company->slug) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                                    <i class="ri-eye-line me-1"></i> Ver carta
                                </a>
                                <a href="{{ route('admin.sections.index') }}" class="btn btn-outline-secondary">
                                    <i class="ri-restaurant-line me-1"></i> Platos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<div class="modal fade" id="modal-add-company">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo negocio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="{{ route('admin.companies.store') }}">
                @csrf
                <div class="modal-body">
                    <label class="form-label" for="new-company-name">Nombre comercial</label>
                    <input type="text" id="new-company-name" name="name" autofocus value="{{ old('name') }}" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="Ej. Mi restaurante" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear y configurar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('styles')
<style>
.wn-company-card__avatar {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  overflow: hidden;
  background: linear-gradient(135deg, #002055, #0074d9);
  display: flex;
  align-items: center;
  justify-content: center;
}
.wn-company-card__avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.wn-company-card__avatar span {
  color: #fff;
  font-weight: 700;
  font-size: 1.25rem;
}
</style>
@endpush

