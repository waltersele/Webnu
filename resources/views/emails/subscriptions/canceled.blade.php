@extends('emails.subscriptions._layout')

@section('title', 'Suscripción cancelada')

@section('content')
@php
    $name = $recipient->name ?: explode('@', $recipient->email)[0];
    $endsAt = $context['ends_at'] ?? null;
@endphp

<h1>Hola {{ $name }}, tu suscripción ha sido cancelada</h1>

<p>
    Hemos registrado la cancelación de tu suscripción a Webnu.
    @if($endsAt)
        Tu acceso continuará hasta el <strong>{{ \Illuminate\Support\Carbon::parse($endsAt)->format('d/m/Y') }}</strong>,
        después tu cuenta pasará al plan Free.
    @else
        Tu cuenta ya está en el plan Free.
    @endif
</p>

<p>
    Conservamos todos tus datos, cartas y configuración. Puedes reactivar la suscripción cuando quieras.
</p>

<p class="actions">
    <a href="{{ $billingUrl }}" class="btn">Reactivar suscripción</a>
</p>

<p>
    Si la cancelación ha sido por error o nos puedes contar qué echaste en falta, respondiendo a este correo nos llegará.
</p>
@endsection
