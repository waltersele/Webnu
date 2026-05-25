@extends('admin.layout')

@section('page_title', 'Mis cartas')
@section('page_subtitle', 'Gestiona todas tus cartas y publícalas cuando estén listas.')

@section('content')
@php
    $templateLabels = collect(config('company_templates.templates', []))->mapWithKeys(fn ($t, $k) => [$k => $t['label'] ?? $k]);
    $companyCount = $companies->count();
    $limitLabel = isset($maxCompanies) && $maxCompanies !== null
        ? $companyCount . ' / ' . $maxCompanies
        : $companyCount . ' / sin límite';
@endphp

<div class="wn-cards-meta">
    <div class="wn-cards-meta__count">
        <i class="ri ri-restaurant-line"></i>
        <span><strong>{{ $companyCount }}</strong> {{ $companyCount === 1 ? 'carta' : 'cartas' }} · {{ $limitLabel }}</span>
    </div>
</div>

@if($companies->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="wn-cards-empty-icon mx-auto mb-3">
                <i class="ri ri-restaurant-2-line"></i>
            </div>
            <h5 class="mb-2">Aún no tienes cartas</h5>
            <p class="text-muted mb-4">Crea tu primera carta para empezar con tu menú digital.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-company">
                <i class="ri ri-add-line me-1"></i> Crear primera carta
            </button>
        </div>
    </div>
@else
    <div class="row g-4">
        @foreach ($companies as $company)
            @php
                $tplKey = $company->template ?: 'basic';
                $tplLabel = $templateLabels[$tplKey] ?? ucfirst($tplKey);
                $sectionsCount = (int) ($company->sections_count ?? $company->sections->count());
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm wn-company-card {{ $company->enabled ? 'is-published' : 'is-draft' }}">
                    <div class="card-body d-flex flex-column">
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
                                <p class="text-muted small mb-1"><code>webnu.es/{{ $company->publicPath() }}</code></p>
                                <p class="text-muted small mb-2">
                                    <i class="ri ri-map-pin-line"></i>
                                    {{ $company->city ?: 'Sin localidad' }}
                                </p>
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="badge bg-label-primary">{{ $tplLabel }}</span>
                                    <span class="badge bg-label-secondary">{{ $sectionsCount }} {{ $sectionsCount === 1 ? 'sección' : 'secciones' }}</span>
                                </div>
                            </div>
                            <div class="dropdown wn-company-card__menu">
                                <button type="button" class="btn btn-icon btn-sm" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones de la carta">
                                    <i class="ri ri-more-2-fill"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.companies.edit', $company) }}">
                                            <i class="ri ri-edit-line me-2"></i>Editar
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.companies.languages', $company) }}">
                                            <i class="ri ri-translate-2 me-2"></i>Idiomas
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button type="button" class="dropdown-item text-danger"
                                                data-bs-toggle="modal" data-bs-target="#modal-delete-company-{{ $company->id }}">
                                            <i class="ri ri-delete-bin-line me-2"></i>Eliminar
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="wn-company-card__toggle">
                            <label class="wn-company-toggle">
                                <input type="checkbox"
                                       class="wn-company-toggle__input"
                                       data-company-toggle
                                       data-url="{{ route('admin.companies.toggle-enabled', $company) }}"
                                       data-token="{{ csrf_token() }}"
                                       data-company-id="{{ $company->id }}"
                                       {{ $company->enabled ? 'checked' : '' }}>
                                <span class="wn-company-toggle__switch" aria-hidden="true"></span>
                                <span class="wn-company-toggle__text">
                                    <strong class="wn-company-toggle__status">
                                        {{ $company->enabled ? 'Publicada' : 'Borrador' }}
                                    </strong>
                                    <small class="text-muted">
                                        {{ $company->enabled ? 'Visible para tus clientes' : 'No accesible públicamente' }}
                                    </small>
                                </span>
                            </label>
                        </div>

                        <div class="d-grid gap-2 mt-auto pt-3">
                            <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-primary btn-sm">
                                <i class="ri ri-settings-3-line me-1"></i> Configurar
                            </a>
                            <div class="d-grid grid-cols-3 gap-1 wn-company-card__quick">
                                <form method="POST" action="{{ route('admin.companies.changecompany') }}" class="d-contents">
                                    @csrf
                                    <input type="hidden" name="company_selection" value="{{ $company->id }}">
                                    <input type="hidden" name="redirect_after" value="{{ route('admin.sections.index') }}">
                                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                                        <i class="ri ri-restaurant-line"></i>
                                        <span class="ms-1 d-none d-sm-inline">Platos</span>
                                    </button>
                                </form>
                                @php
                                    $previewUrl = $company->enabled
                                        ? $company->publicUrl()
                                        : $company->publicUrl(['preview_token' => $company->previewToken()]);
                                @endphp
                                <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm" title="{{ $company->enabled ? 'Ver carta pública' : 'Previsualizar borrador' }}">
                                    <i class="ri ri-eye-line"></i>
                                    <span class="ms-1 d-none d-sm-inline">Ver</span>
                                </a>
                                @if($company->enabled)
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#wn-qr-modal"
                                            title="Código QR">
                                        <i class="ri ri-qr-code-line"></i>
                                        <span class="ms-1 d-none d-sm-inline">QR</span>
                                    </button>
                                @else
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm"
                                            disabled
                                            title="Publica la carta para generar el QR">
                                        <i class="ri ri-qr-code-line"></i>
                                        <span class="ms-1 d-none d-sm-inline">QR</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- CTA "Añadir nueva carta" como link inline --}}
    <div class="wn-cards-add-row">
        @if($canCreateCompany)
            <button type="button" class="wn-add-link" data-bs-toggle="modal" data-bs-target="#modal-add-company">
                <i class="ri ri-add-line"></i> Añadir nueva carta
            </button>
        @else
            <span class="wn-add-link wn-add-link--locked">
                <i class="ri ri-vip-crown-line"></i>
                Has alcanzado el límite de tu plan
                @if(isset($maxCompanies) && $maxCompanies !== null)
                    ({{ $maxCompanies }} {{ $maxCompanies === 1 ? 'carta' : 'cartas' }})
                @endif.
                <a href="{{ route('admin.settings') }}#plan">Mejorar a PRO</a>
            </span>
        @endif
    </div>
@endif

<div class="modal fade" id="modal-add-company">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva carta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="{{ route('admin.companies.store') }}">
                @csrf
                <div class="modal-body">
                    <label class="form-label" for="new-company-name">Nombre de la carta</label>
                    <input type="text" id="new-company-name" name="name" autofocus value="{{ old('name') }}" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="Ej. Casa María - Menú del día" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted d-block mt-2">
                        Puedes usarla para cartas distintas (comida, cena, eventos, bebidas).
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear y configurar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modales de confirmación de eliminación (uno por carta) --}}
@foreach ($companies as $companyForDelete)
    <div class="modal fade" id="modal-delete-company-{{ $companyForDelete->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">¿Eliminar “{{ $companyForDelete->name }}”?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Vas a eliminar la carta <strong>{{ $companyForDelete->name }}</strong> y todo lo que contiene:</p>
                    <ul class="text-muted small mb-3">
                        <li>Secciones y platos</li>
                        <li>Traducciones e idiomas activos</li>
                        <li>Logo, imágenes y configuración</li>
                    </ul>
                    <p class="mb-0 text-danger small"><i class="ri ri-error-warning-line me-1"></i>Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="{{ route('admin.companies.delete') }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="companyid" value="{{ $companyForDelete->id }}">
                        <button type="submit" class="btn btn-danger">
                            <i class="ri ri-delete-bin-line me-1"></i> Eliminar carta
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@stop

@push('styles')
<style>
.wn-cards-meta {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    margin-bottom: 18px;
    flex-wrap: wrap;
    gap: 12px;
}
.wn-cards-meta__count {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: var(--wn-surface-container-low, #f7f8fb);
    border: 1px solid var(--wn-border-subtle, #e5e7eb);
    border-radius: 999px;
    font-size: 13.5px;
    line-height: 1.2;
    color: var(--wn-text, #0f172a);
}
.wn-cards-meta__count i {
    color: var(--wn-primary, #004ac6);
    font-size: 16px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
}
.wn-cards-meta__count span,
.wn-cards-meta__count strong {
    display: inline-flex;
    align-items: center;
    line-height: 1.2;
}

.wn-company-card { transition: transform 0.18s ease, box-shadow 0.18s ease; }
.wn-company-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08) !important; }
.wn-company-card.is-draft { border-left: 3px solid var(--wn-warning, #f59e0b) !important; }
.wn-company-card.is-published { border-left: 3px solid var(--wn-success, #16a34a) !important; }

.wn-company-card__avatar {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    overflow: hidden;
    background: linear-gradient(135deg, var(--wn-primary-dark, #002055), var(--wn-primary, #0074d9));
    display: flex; align-items: center; justify-content: center;
}
.wn-company-card__avatar img { width: 100%; height: 100%; object-fit: cover; }
.wn-company-card__avatar span { color: #fff; font-weight: 700; font-size: 1.5rem; }

.wn-company-card__toggle {
    padding: 12px;
    border-radius: 10px;
    background: var(--wn-surface-container-low, #f7f8fb);
    border: 1px solid var(--wn-border-subtle, #e5e7eb);
    margin-top: 4px;
}
.wn-company-toggle {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    margin: 0;
}
.wn-company-toggle__input { display: none; }
.wn-company-toggle__switch {
    width: 38px; height: 22px;
    background: #cbd5e1;
    border-radius: 999px;
    position: relative;
    flex-shrink: 0;
    transition: background 0.2s ease;
}
.wn-company-toggle__switch::after {
    content: "";
    position: absolute; top: 2px; left: 2px;
    width: 18px; height: 18px;
    background: #fff;
    border-radius: 50%;
    transition: transform 0.2s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.15);
}
.wn-company-toggle__input:checked ~ .wn-company-toggle__switch {
    background: var(--wn-success, #16a34a);
}
.wn-company-toggle__input:checked ~ .wn-company-toggle__switch::after {
    transform: translateX(16px);
}
.wn-company-toggle__input:disabled ~ .wn-company-toggle__switch {
    opacity: 0.5;
    cursor: not-allowed;
}
.wn-company-toggle__text {
    display: flex; flex-direction: column; line-height: 1.2;
    min-width: 0;
}
.wn-company-toggle__status { font-size: 13.5px; }
.wn-company-toggle__text small { font-size: 11.5px; }

.wn-company-card__quick {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
}

/* CTA "Añadir carta" como link de texto azul */
.wn-cards-add-row {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    margin-top: 20px;
    padding: 0;
}
.wn-add-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--wn-primary, #004ac6);
    font-weight: 500;
    font-size: 14px;
    text-decoration: none;
    background: none;
    border: 0;
    padding: 8px 0;
    cursor: pointer;
    line-height: 1.3;
}
.wn-add-link:hover {
    color: var(--wn-primary-dark, #003899);
    text-decoration: underline;
}
.wn-add-link i { font-size: 18px; }
.wn-add-link--locked {
    color: var(--wn-text-muted, #64748b);
    font-weight: 400;
    cursor: default;
}
.wn-add-link--locked:hover { text-decoration: none; color: var(--wn-text-muted, #64748b); }
.wn-add-link--locked i { color: var(--wn-warning, #f59e0b); }
.wn-add-link--locked a {
    color: var(--wn-primary, #004ac6);
    margin-left: 4px;
    font-weight: 500;
}
.wn-add-link--locked a:hover { text-decoration: underline; }

.wn-company-card__menu .btn-icon {
    background: transparent;
    border: 0;
    color: var(--wn-text-muted, #64748b);
    padding: 4px 6px;
    border-radius: 6px;
    line-height: 1;
}
.wn-company-card__menu .btn-icon:hover {
    background: var(--wn-surface-container-low, #f7f8fb);
    color: var(--wn-text, #0f172a);
}
.wn-company-card__menu .dropdown-item.text-danger:hover {
    background: rgba(239, 68, 68, 0.08);
    color: #b91c1c;
}

.wn-cards-empty-icon {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: var(--wn-primary-container, #e6f0ff);
    color: var(--wn-primary, #004ac6);
    display: flex; align-items: center; justify-content: center;
    font-size: 32px;
}
</style>
@endpush

{{-- El handler [data-company-toggle] está centralizado en public/materio/js/webnu-admin.js (initCompanyEnabledToggles). --}}
