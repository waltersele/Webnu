@extends('sales.layout')

@section('title', $visit->name)

@section('content')
@php
    $steps = [
        1 => [
            'title' => 'Importar carta',
            'hint' => 'Cámara o PDF — la IA monta el menú',
            'done' => $hasMenu,
            'url' => $importUrl,
            'cta' => 'Importar',
            'icon' => '📷',
        ],
        2 => [
            'title' => 'Presentar',
            'hint' => $hasMenu ? $productCount . ' platos · plantilla y colores en vivo' : 'Tras importar la carta',
            'done' => false,
            'url' => $hasMenu ? route('sales.visit.present', $visit->id) : null,
            'cta' => 'Presentar',
            'icon' => '📱',
        ],
        3 => [
            'title' => 'Fotos demo',
            'hint' => $hasMenu ? 'Hasta ' . $photoSlotsRemaining . ' plato(s) con foto' : 'Opcional, tras la carta',
            'done' => false,
            'url' => $hasMenu ? route('sales.demo-products.index', $visit->id) : null,
            'cta' => 'Añadir fotos',
            'icon' => '✨',
            'optional' => true,
        ],
        4 => [
            'title' => 'Cerrar venta',
            'hint' => 'Email al restaurante con acceso y prueba',
            'done' => false,
            'url' => $hasMenu ? route('sales.handoff.show', $visit->id) : null,
            'cta' => 'Enviar acceso',
            'icon' => '✉️',
        ],
    ];
    $nextUrl = $hasMenu ? route('sales.visit.present', $visit->id) : $importUrl;
    $nextLabel = $hasMenu ? 'Presentar al cliente' : 'Importar carta ahora';
@endphp

<header class="wn-sales-visit-header">
    <a href="{{ route('sales.dashboard') }}" class="wn-sales-visit-back">← Visitas</a>
    <h1 class="wn-sales-visit-title">{{ $visit->name }}</h1>
    @if ($visit->city)
        <p class="wn-sales-visit-meta">{{ $visit->city }}</p>
    @endif
    <div class="wn-sales-progress" aria-label="Progreso de la visita">
        @for ($i = 1; $i <= 4; $i++)
            <span class="wn-sales-progress__seg {{ $i < $progressStep ? 'is-done' : '' }} {{ $i === $progressStep ? 'is-current' : '' }}"></span>
        @endfor
    </div>
    <p class="wn-sales-progress-label">
        @if (! $hasMenu)
            Paso 1 de 4 · Importa la carta para continuar
        @elseif ($progressStep === 2)
            Paso 2 de 4 · Lista para enseñar al cliente
        @else
            {{ $productCount }} platos en la carta
        @endif
    </p>
</header>

@if (! $hasMenu)
    <a href="{{ $importUrl }}" class="wn-sales-hero-cta">
        <span class="wn-sales-hero-cta__icon" aria-hidden="true">📄</span>
        <span class="wn-sales-hero-cta__text">
            <strong>Digitalizar carta</strong>
            <small>Fotografía o sube PDF — en 1–2 minutos</small>
        </span>
        <span class="wn-sales-hero-cta__arrow" aria-hidden="true">→</span>
    </a>
@endif

<ol class="wn-sales-visit-steps">
    @foreach ($steps as $num => $step)
        @php
            $locked = ! $step['url'];
            $done = $num < $progressStep;
            $active = (int) $num === (int) $progressStep;
        @endphp
        <li class="wn-sales-visit-step {{ $done ? 'is-done' : '' }} {{ $active ? 'is-active' : '' }} {{ $locked ? 'is-locked' : '' }}">
            <div class="wn-sales-visit-step__marker" aria-hidden="true">
                @if ($done)
                    <span class="wn-sales-visit-step__check">✓</span>
                @else
                    <span class="wn-sales-visit-step__num">{{ $num }}</span>
                @endif
            </div>
            <div class="wn-sales-visit-step__body">
                <div class="wn-sales-visit-step__head">
                    <span class="wn-sales-visit-step__icon" aria-hidden="true">{{ $step['icon'] }}</span>
                    <strong>{{ $step['title'] }}</strong>
                    @if (! empty($step['optional']))
                        <span class="wn-sales-visit-step__badge">Opcional</span>
                    @endif
                </div>
                <p class="wn-sales-visit-step__hint">{{ $step['hint'] }}</p>
                @if ($step['url'] && ($active || $done))
                    <a href="{{ $step['url'] }}" class="wn-sales-visit-step__action {{ $active ? 'wn-sales-visit-step__action--primary' : '' }}">
                        {{ $step['cta'] }}
                    </a>
                @elseif ($locked)
                    <span class="wn-sales-visit-step__locked">Completa el paso anterior</span>
                @endif
            </div>
        </li>
    @endforeach
</ol>

<div class="wn-sales-visit-footer-spacer" aria-hidden="true"></div>
<nav class="wn-sales-visit-footer">
    <a href="{{ $nextUrl }}" class="wn-sales-btn wn-sales-btn-primary wn-sales-visit-footer__btn">
        {{ $nextLabel }}
    </a>
    @if ($hasMenu)
        <a href="{{ $importUrl }}" class="wn-sales-visit-footer__secondary">Volver a importar / editar carta</a>
    @endif
</nav>
@endsection

@push('styles')
<style>
.wn-sales-main { padding-bottom: 0; }
</style>
@endpush
