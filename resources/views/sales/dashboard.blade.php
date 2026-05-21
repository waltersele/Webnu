@extends('sales.layout')

@section('title', 'Visitas')

@section('content')
<h1 style="font-size: 1.35rem; margin: 0 0 0.5rem;">Visitas</h1>
<p style="color: #64748b; font-size: 0.9rem; margin: 0 0 1rem;">
    Crea una visita e importa la carta con la cámara o un PDF. La IA monta el menú automáticamente.
</p>

@forelse ($visits as $visit)
    @php
        $needsImport = ($visit->products_count ?? 0) < 1;
        $visitUrl = $needsImport
            ? route('sales.menu-scan.create', $visit->id)
            : route('sales.visit.show', $visit->id);
    @endphp
    <a href="{{ $visitUrl }}" class="wn-sales-list-item">
        <strong>{{ $visit->name }}</strong>
        @if ($visit->city)
            <span style="color: #64748b; display: block; font-size: 0.9rem;">{{ $visit->city }}</span>
        @endif
        @if ($needsImport)
            <span style="color: #2563eb; display: block; font-size: 0.85rem; margin-top: 0.25rem;">→ Importar carta</span>
        @endif
    </a>
@empty
@endforelse

<div class="wn-sales-card" style="margin-top: {{ $visits->isEmpty() ? '0' : '1.5rem' }};">
    <h2 style="font-size: 1.1rem; margin: 0 0 0.5rem;">{{ $visits->isEmpty() ? 'Empezar visita' : 'Nueva visita' }}</h2>
    <p style="color: #64748b; font-size: 0.9rem; margin: 0 0 1rem;">Tras crear la visita irás directo a escanear o subir la carta.</p>
    <form method="POST" action="{{ route('sales.visit.store') }}" class="wn-sales-form">
        @csrf
        <label for="name">Nombre del restaurante</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Ej. Taberna del Puerto" autofocus>

        <label for="city">Ciudad (opcional)</label>
        <input type="text" id="city" name="city" value="{{ old('city') }}" placeholder="Ej. Valencia">

        <button type="submit" class="wn-sales-btn wn-sales-btn-primary">Importar carta</button>
    </form>
</div>
@endsection
