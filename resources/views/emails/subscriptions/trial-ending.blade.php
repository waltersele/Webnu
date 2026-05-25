@extends('emails.subscriptions._layout')

@section('title', 'Tu prueba termina pronto')

@section('content')
@php
    $name = $recipient->name ?: explode('@', $recipient->email)[0];
    $endsAt = $recipient->trial_ends_at;
    $daysLeft = $endsAt ? max(0, (int) now()->diffInDays($endsAt, false)) : null;
@endphp

<h1>Hola {{ $name }}, tu prueba gratis termina pronto</h1>

<p>
    Tu periodo de prueba en <strong>Webnu</strong>
    @if($daysLeft !== null && $daysLeft > 0)
        termina en <strong>{{ $daysLeft }} {{ $daysLeft === 1 ? 'día' : 'días' }}</strong>
    @elseif($endsAt)
        termina el <strong>{{ $endsAt->format('d/m/Y') }}</strong>
    @else
        está a punto de terminar
    @endif.
</p>

<p>
    Si quieres seguir usando todas las funciones (vídeos, traducciones, plantillas premium, sugerencias del chef…),
    activa tu suscripción ahora desde el panel.
</p>

<p class="actions">
    <a href="{{ $billingUrl }}" class="btn">Activar suscripción</a>
</p>

<p>
    Si no haces nada, tu cuenta seguirá funcionando en el plan <strong>Free</strong> y conservaremos todos tus datos.
</p>
@endsection
