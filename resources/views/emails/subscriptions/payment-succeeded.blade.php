@extends('emails.subscriptions._layout')

@section('title', 'Pago recibido')

@section('content')
@php
    $name = $recipient->name ?: explode('@', $recipient->email)[0];
    $invoiceUrl = $context['invoice_url'] ?? null;
    $amount = $context['amount_formatted'] ?? null;
    $periodLabel = $context['period_label'] ?? null;
@endphp

<h1>Gracias {{ $name }}, hemos recibido tu pago</h1>

<p>
    Tu suscripción a Webnu se ha renovado correctamente
    @if($amount) por <strong>{{ $amount }}</strong>@endif
    @if($periodLabel) ({{ $periodLabel }})@endif.
</p>

<p>
    No tienes que hacer nada. Tu carta sigue activa y todas las funciones de tu plan continúan disponibles.
</p>

<p class="actions">
    @if($invoiceUrl)
        <a href="{{ $invoiceUrl }}" class="btn">Descargar factura</a>
    @else
        <a href="{{ $billingUrl }}" class="btn">Ver facturas</a>
    @endif
</p>
@endsection
