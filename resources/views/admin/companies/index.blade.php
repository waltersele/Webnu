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
                            @php
                                $avatarVariant = $company->logo_chip_variant ?: 'glass';
                                $avatarAutocontrast = ($company->logo && empty($company->logo_chip_variant)) ? 'on' : 'off';
                            @endphp
                            <div class="wn-company-card__avatar wn-company-card__avatar--bg-{{ $avatarVariant }} {{ $company->logo ? 'wn-company-card__avatar--has-logo' : '' }} flex-shrink-0"
                                 data-logo-autocontrast="{{ $avatarAutocontrast }}">
                                @if($company->logo)
                                    <img src="{{ asset('img/' . $company->logo) }}" alt="" crossorigin="anonymous">
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

@php
    $newCardOwnerSlug = optional(auth()->user())->resolveSlug() ?: 'tu-negocio';
    $newCardSuggestions = ['Restaurante', 'Carta de vinos', 'Menú del día', 'Cena', 'Brunch', 'Eventos'];
@endphp
<div class="modal fade" id="modal-add-company" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear nueva carta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="{{ route('admin.companies.store') }}" id="wn-new-card-form">
                @csrf
                <div class="modal-body">
                    <label class="form-label fw-medium" for="new-company-name">¿Cómo se llamará esta carta?</label>
                    <input type="text"
                           id="new-company-name"
                           name="name"
                           autofocus
                           value="{{ old('name') }}"
                           class="form-control form-control-lg {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           placeholder="Ej. Restaurante, Carta de vinos, Menú del día"
                           data-new-card-name
                           required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror

                    <div class="wn-new-card-suggestions mt-3">
                        <span class="wn-new-card-suggestions__label">Sugerencias:</span>
                        @foreach ($newCardSuggestions as $suggestion)
                            <button type="button" class="wn-new-card-chip" data-new-card-suggestion="{{ $suggestion }}">{{ $suggestion }}</button>
                        @endforeach
                    </div>

                    <div class="wn-new-card-preview mt-3" role="status" aria-live="polite">
                        <i class="ri ri-link"></i>
                        <span class="wn-new-card-preview__url">
                            webnu.es/carta/{{ $newCardOwnerSlug }}/<strong data-slug-preview>tu-carta</strong>
                        </span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri ri-add-line me-1"></i> Crear y configurar
                    </button>
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
    transition: background 0.2s ease, border-color 0.2s ease;
}
.wn-company-card__avatar img { width: 100%; height: 100%; object-fit: cover; }
.wn-company-card__avatar span { color: #fff; font-weight: 700; font-size: 1.5rem; }

/* Cuando hay logo subido usamos object-fit: contain con padding para no recortarlo,
 * y el fondo se elige por luminancia (calculado al subir). */
.wn-company-card__avatar--has-logo {
    background: #fff;
    border: 1px solid var(--wn-border-subtle, #e5e7eb);
    padding: 6px;
}
.wn-company-card__avatar--has-logo img { object-fit: contain; }

.wn-company-card__avatar--has-logo.wn-company-card__avatar--bg-light {
    background: #ffffff;
    border-color: var(--wn-border-subtle, #e5e7eb);
}
.wn-company-card__avatar--has-logo.wn-company-card__avatar--bg-dark {
    background: #111;
    border-color: rgba(0, 0, 0, 0.35);
}
.wn-company-card__avatar--has-logo.wn-company-card__avatar--bg-glass {
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    border-color: var(--wn-border-subtle, #e5e7eb);
}

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

/* Modal Nueva carta */
.wn-new-card-suggestions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
}
.wn-new-card-suggestions__label {
    font-size: 12px;
    color: var(--wn-text-muted, #64748b);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
    margin-right: 4px;
}
.wn-new-card-chip {
    background: #f1f5fa;
    border: 1px solid #e2e8f0;
    color: #1e293b;
    border-radius: 999px;
    padding: 5px 12px;
    font-size: 12.5px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}
.wn-new-card-chip:hover {
    background: var(--wn-primary, #004ac6);
    border-color: var(--wn-primary, #004ac6);
    color: #fff;
}
.wn-new-card-preview {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 10px;
    font-size: 13px;
    color: #475569;
    word-break: break-all;
}
.wn-new-card-preview i {
    color: var(--wn-primary, #004ac6);
    flex-shrink: 0;
}
.wn-new-card-preview__url strong {
    color: var(--wn-primary, #004ac6);
    font-weight: 700;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/webnu-logo-autocontrast.js') }}" defer></script>
<script>
(function () {
    var input = document.querySelector('[data-new-card-name]');
    var preview = document.querySelector('[data-slug-preview]');
    if (!input || !preview) return;

    function slugify(value) {
        return (value || '')
            .toString()
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    }

    function render() {
        var slug = slugify(input.value);
        preview.textContent = slug || 'tu-carta';
    }

    input.addEventListener('input', render);

    document.querySelectorAll('[data-new-card-suggestion]').forEach(function (chip) {
        chip.addEventListener('click', function () {
            input.value = chip.getAttribute('data-new-card-suggestion');
            input.focus();
            render();
        });
    });

    render();
})();
</script>
@endpush

{{-- El handler [data-company-toggle] está centralizado en public/materio/js/webnu-admin.js (initCompanyEnabledToggles). --}}
