@extends('sales.layout')

@section('title', $visit->name)

@section('content')
<p style="margin: 0 0 0.25rem;"><a href="{{ route('sales.dashboard') }}" style="color: #64748b; font-size: 0.9rem;">← Visitas</a></p>
<h1 style="font-size: 1.35rem; margin: 0 0 1rem;">{{ $visit->name }}</h1>

<div class="wn-sales-card">
    <div class="wn-sales-step">
        <span class="wn-sales-step-num">1</span>
        <div style="flex: 1;">
            <strong>Importar carta</strong>
            <p style="margin: 0.25rem 0 0.5rem; color: #64748b; font-size: 0.9rem;">Escanear con cámara o subir PDF/fotos.</p>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                <a href="{{ route('sales.menu-scan.create', $visit->id) }}" class="wn-sales-btn wn-sales-btn-primary" style="width: auto; display: inline-block; padding: 0.5rem 1rem;">Escanear / fotografiar</a>
                <a href="{{ route('sales.menu-scan.create', $visit->id) }}#upload" class="wn-sales-btn wn-sales-btn-outline" style="width: auto; display: inline-block; padding: 0.5rem 1rem;">Subir archivo</a>
            </div>
        </div>
    </div>

    <div class="wn-sales-step">
        <span class="wn-sales-step-num">2</span>
        <div style="flex: 1;">
            <strong>Presentar carta</strong>
            <p style="margin: 0.25rem 0 0.5rem; color: #64748b; font-size: 0.9rem;">
                {{ $productCount > 0 ? $productCount . ' platos — cambia plantilla y colores en vivo.' : 'Importa la carta antes de presentarla.' }}
            </p>
            @if ($productCount > 0)
                <a href="{{ route('sales.visit.present', $visit->id) }}" class="wn-sales-btn wn-sales-btn-primary" style="width: auto; display: inline-block; padding: 0.5rem 1rem;">Presentar</a>
            @endif
        </div>
    </div>

    <div class="wn-sales-step">
        <span class="wn-sales-step-num">3</span>
        <div style="flex: 1;">
            <strong>Platos con foto (demo)</strong>
            <p style="margin: 0.25rem 0 0.5rem; color: #64748b; font-size: 0.9rem;">Hasta {{ $photoSlotsRemaining }} foto(s) más para impresionar.</p>
            @if ($productCount > 0)
                <a href="{{ route('sales.demo-products.index', $visit->id) }}" class="wn-sales-btn wn-sales-btn-outline" style="width: auto; display: inline-block; padding: 0.5rem 1rem;">Añadir fotos</a>
            @endif
        </div>
    </div>

    <div class="wn-sales-step">
        <span class="wn-sales-step-num">4</span>
        <div style="flex: 1;">
            <strong>Enviar acceso</strong>
            <p style="margin: 0.25rem 0 0.5rem; color: #64748b; font-size: 0.9rem;">Email al restaurante con trial y administración.</p>
            @if ($productCount > 0)
                <a href="{{ route('sales.handoff.show', $visit->id) }}" class="wn-sales-btn wn-sales-btn-primary" style="width: auto; display: inline-block; padding: 0.5rem 1rem;">Cerrar venta</a>
            @endif
        </div>
    </div>
</div>
@endsection
