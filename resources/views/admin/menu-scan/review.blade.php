@extends('admin.layout')

@section('page_title', 'Revisar importación')
@section('page_subtitle', 'Comprueba secciones y platos antes de guardar en tu carta')

@push('styles')
<link rel="stylesheet" href="{{ asset('materio/css/webnu-menu-scan.css') }}">
@endpush

@section('page_actions')
    <form method="POST" action="{{ route('admin.menu-scan.destroy', $job) }}" class="d-inline" onsubmit="return confirm('¿Cancelar esta importación?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger btn-sm">Cancelar</button>
    </form>
@endsection

@section('content')
@php
    $sections = $job->parsed_menu['sections'] ?? [];
    $isFailed = $job->status === \App\MenuScanJob::STATUS_FAILED;
    $isReview = $job->status === \App\MenuScanJob::STATUS_REVIEW;
@endphp

@if ($isFailed)
    <div class="alert alert-danger">
        <strong>No se pudo analizar la carta.</strong>
        {{ $job->error_message }}
    </div>
    <a href="{{ route('admin.menu-scan.create') }}" class="btn btn-primary">Intentar de nuevo</a>
@elseif ($isReview)
    @if ($job->fallback_used)
        <div class="alert alert-warning d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark">OCR</span>
            <span>Se usó reconocimiento local (Tesseract) porque Gemini no estuvo disponible. Revisa nombres y precios con cuidado.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.menu-scan.update', $job) }}" id="menu-scan-review-form">
        @csrf
        @method('PUT')
        <div id="menu-scan-sections">
            @foreach ($sections as $si => $section)
                <div class="card mb-3 wn-menu-scan-section" data-section-index="{{ $si }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <input type="text" name="sections[{{ $si }}][name]" value="{{ $section['name'] }}" class="form-control form-control-sm wn-section-name" required>
                        <button type="button" class="btn btn-sm btn-outline-danger wn-remove-section" title="Quitar sección">&times;</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 wn-menu-scan-table">
                                <thead>
                                    <tr>
                                        <th>Plato</th>
                                        <th>Descripción</th>
                                        <th>Precio</th>
                                        <th>Medio/ración</th>
                                        <th>Alérgenos</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($section['products'] as $pi => $product)
                                        <tr class="wn-product-row">
                                            <td><input type="text" name="sections[{{ $si }}][products][{{ $pi }}][name]" value="{{ $product['name'] }}" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="sections[{{ $si }}][products][{{ $pi }}][description]" value="{{ $product['description'] ?? '' }}" class="form-control form-control-sm"></td>
                                            <td><input type="text" name="sections[{{ $si }}][products][{{ $pi }}][price_unit]" value="{{ $product['price_unit'] ?? '' }}" class="form-control form-control-sm" placeholder="12,50"></td>
                                            <td><input type="text" name="sections[{{ $si }}][products][{{ $pi }}][price_portion]" value="{{ $product['price_portion'] ?? '' }}" class="form-control form-control-sm"></td>
                                            <td>
                                                <input type="text"
                                                       name="sections[{{ $si }}][products][{{ $pi }}][allergens]"
                                                       value="{{ is_array($product['allergens'] ?? null) ? implode(', ', $product['allergens']) : ($product['allergens'] ?? '') }}"
                                                       class="form-control form-control-sm"
                                                       placeholder="Gluten, Lácteos">
                                            </td>
                                            <td><button type="button" class="btn btn-sm btn-link text-danger wn-remove-product">&times;</button></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-link wn-add-product">+ Añadir plato</button>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm mb-4" id="wn-add-section">+ Añadir sección</button>

        <div class="d-flex justify-content-end mb-4">
            <button type="submit" class="btn btn-label-primary">Guardar borrador</button>
        </div>
    </form>

    <p class="text-muted small mb-3">Si has editado la tabla, pulsa <strong>Guardar borrador</strong> antes de importar.</p>

    <form method="POST" action="{{ route('admin.menu-scan.import', $job) }}" id="menu-scan-import-form" class="card">
        @csrf
        <div class="card-body">
            <h6 class="fw-semibold mb-3">Confirmar importación</h6>
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="import_mode" id="import-append" value="append" checked>
                    <label class="form-check-label" for="import-append">Añadir al final de la carta actual</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="import_mode" id="import-replace" value="replace">
                    <label class="form-check-label" for="import-replace">Reemplazar toda la carta actual</label>
                </div>
            </div>
            <div class="form-check mb-3 d-none" id="replace-confirm-wrap">
                <input class="form-check-input" type="checkbox" name="replace_confirm" value="1" id="replace-confirm">
                <label class="form-check-label text-danger" for="replace-confirm">Entiendo que se borrarán todas las secciones y platos actuales</label>
            </div>
            @error('replace_confirm')<div class="text-danger small">{{ $message }}</div>@enderror
            @error('import')<div class="text-danger small">{{ $message }}</div>@enderror
            <button type="submit" class="btn btn-success">Importar a Mi carta</button>
        </div>
    </form>

    <template id="wn-product-row-template">
        <tr class="wn-product-row">
            <td><input type="text" data-name="name" class="form-control form-control-sm" required></td>
            <td><input type="text" data-name="description" class="form-control form-control-sm"></td>
            <td><input type="text" data-name="price_unit" class="form-control form-control-sm" placeholder="12,50"></td>
            <td><input type="text" data-name="price_portion" class="form-control form-control-sm"></td>
            <td><input type="text" data-name="allergens" class="form-control form-control-sm" placeholder="Gluten, Lácteos"></td>
            <td><button type="button" class="btn btn-sm btn-link text-danger wn-remove-product">&times;</button></td>
        </tr>
    </template>

    <template id="wn-section-template">
        <div class="card mb-3 wn-menu-scan-section">
            <div class="card-header d-flex justify-content-between align-items-center">
                <input type="text" data-name="section_name" value="Nueva sección" class="form-control form-control-sm wn-section-name" required>
                <button type="button" class="btn btn-sm btn-outline-danger wn-remove-section">&times;</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 wn-menu-scan-table">
                        <thead>
                            <tr>
                                <th>Plato</th>
                                <th>Descripción</th>
                                <th>Precio</th>
                                <th>Medio/ración</th>
                                <th>Alérgenos</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-link wn-add-product">+ Añadir plato</button>
            </div>
        </div>
    </template>
@elseif ($job->status === \App\MenuScanJob::STATUS_IMPORTED)
    <div class="alert alert-success">Esta importación ya se guardó en tu carta.</div>
    <a href="{{ route('admin.sections.index') }}" class="btn btn-primary">Ir a Mi carta</a>
@else
    <div class="alert alert-info">Procesando…</div>
@endif
@endsection

@push('scripts')
<script src="{{ asset('materio/js/webnu-menu-scan.js') }}"></script>
@if ($isReview)
<script>window.webnuMenuScanReview = true;</script>
@endif
@endpush

