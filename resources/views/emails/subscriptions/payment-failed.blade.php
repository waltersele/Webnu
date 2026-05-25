@extends('emails.subscriptions._layout')

@section('title', 'Cobro fallido')

@section('content')
@php
    $name = $recipient->name ?: explode('@', $recipient->email)[0];
    $invoiceUrl = $context['invoice_url'] ?? null;
    $amount = $context['amount_formatted'] ?? null;
@endphp

<h1>Hola {{ $name }}, no hemos podido cobrar tu suscripción</h1>

<p>
    Acabamos de intentar renovar tu suscripción de Webnu
    @if($amount) por <strong>{{ $amount }}</strong>@endif
    y el banco ha rechazado el pago.
</p>

<p>
    Por favor, revisa o actualiza tu tarjeta para evitar interrupciones en tu carta digital.
</p>

<p class="actions">
    <a href="{{ $billingUrl }}" class="btn">Actualizar método de pago</a>
    @if($invoiceUrl)
        <a href="{{ $invoiceUrl }}" class="btn btn--secondary" style="margin-left:8px;">Ver factura</a>
    @endif
</p>

<p>
    Si en los próximos días no podemos cobrarla, tu suscripción quedará en pausa y volverás temporalmente al plan Free.
    Tus datos no se pierden.
</p>
@endsection
