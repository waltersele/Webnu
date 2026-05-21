@extends('sales.layout')

@section('title', 'Revisar carta')

@push('styles')
<link rel="stylesheet" href="{{ asset('materio/css/webnu-menu-scan.css') }}">
@endpush

@section('content')
@php
    $sections = $job->parsed_menu['sections'] ?? [];
    $isFailed = $job->status === \App\MenuScanJob::STATUS_FAILED;
    $isReview = $job->status === \App\MenuScanJob::STATUS_REVIEW;
@endphp

<p style="margin: 0 0 0.25rem;"><a href="{{ route('sales.visit.show', $visit->id) }}" style="color: #64748b; font-size: 0.9rem;">← {{ $visit->name }}</a></p>
<h1 style="font-size: 1.25rem; margin: 0 0 1rem;">Revisar importación</h1>

@if ($isFailed)
    <div class="wn-sales-error">{{ $job->error_message }}</div>
    <a href="{{ route('sales.menu-scan.create', $visit->id) }}" class="wn-sales-btn wn-sales-btn-primary">Intentar de nuevo</a>
@elseif ($isReview)
    <form method="POST" action="{{ route('sales.menu-scan.update', [$visit->id, $job->id]) }}" id="menu-scan-review-form">
        @csrf
        @method('PUT')
        <div id="menu-scan-sections">
            @foreach ($sections as $si => $section)
                <div class="wn-sales-card wn-menu-scan-section" data-section-index="{{ $si }}" style="padding: 0.75rem;">
                    <input type="text" name="sections[{{ $si }}][name]" value="{{ $section['name'] }}" class="wn-sales-form" style="margin-bottom: 0.5rem;" required>
                    @foreach ($section['products'] as $pi => $product)
                        <div style="border-top: 1px solid #f1f5f9; padding-top: 0.5rem; margin-top: 0.5rem;">
                            <input type="text" name="sections[{{ $si }}][products][{{ $pi }}][name]" value="{{ $product['name'] }}" placeholder="Plato" required style="margin-bottom: 0.35rem;">
                            <input type="text" name="sections[{{ $si }}][products][{{ $pi }}][price_unit]" value="{{ $product['price_unit'] ?? '' }}" placeholder="Precio">
                            <input type="hidden" name="sections[{{ $si }}][products][{{ $pi }}][description]" value="{{ $product['description'] ?? '' }}">
                            <input type="hidden" name="sections[{{ $si }}][products][{{ $pi }}][price_portion]" value="{{ $product['price_portion'] ?? '' }}">
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
        <button type="submit" class="wn-sales-btn wn-sales-btn-outline">Guardar borrador</button>
    </form>

    <form method="POST" action="{{ route('sales.menu-scan.import', [$visit->id, $job->id]) }}" class="wn-sales-card" style="margin-top: 1rem;">
        @csrf
        <p style="font-weight: 600; margin: 0 0 0.75rem;">Importar a la carta de la visita</p>
        <label style="display: flex; gap: 0.5rem; font-weight: normal; margin-bottom: 0.5rem;">
            <input type="radio" name="import_mode" value="replace" checked style="width: auto;">
            Reemplazar carta (recomendado en visita nueva)
        </label>
        <input type="hidden" name="replace_confirm" value="1">
        @error('import')<div class="wn-sales-error">{{ $message }}</div>@enderror
        <button type="submit" class="wn-sales-btn wn-sales-btn-primary">Importar carta</button>
    </form>
@elseif ($job->status === \App\MenuScanJob::STATUS_IMPORTED)
    <div class="wn-sales-flash">Carta importada correctamente.</div>
    <a href="{{ route('sales.visit.show', $visit->id) }}" class="wn-sales-btn wn-sales-btn-primary">Volver a la visita</a>
@endif
@endsection
