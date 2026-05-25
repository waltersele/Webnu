@extends('emails.subscriptions._layout')

@section('title', 'Tu plan de cortesía termina pronto')

@section('content')
@php
    $name = $recipient->name ?: explode('@', $recipient->email)[0];
    $endsAt = $recipient->manual_plan_until;
    $daysLeft = $endsAt ? max(0, (int) now()->diffInDays($endsAt, false)) : null;
@endphp

<h1>Hola {{ $name }}, tu plan de cortesía está a punto de terminar</h1>

<p>
    El plan especial que te asignamos en Webnu
    @if($daysLeft !== null && $daysLeft > 0)
        termina en <strong>{{ $daysLeft }} {{ $daysLeft === 1 ? 'día' : 'días' }}</strong>
    @elseif($endsAt)
        termina el <strong>{{ $endsAt->format('d/m/Y') }}</strong>
    @else
        está a punto de terminar
    @endif.
</p>

<p>
    Cuando finalice volverás al plan base de tu cuenta. Si quieres mantener las funciones premium activas, puedes contratar una suscripción ahora.
</p>

<p class="actions">
    <a href="{{ $billingUrl }}" class="btn">Contratar suscripción</a>
</p>

<p>
    Tu carta, productos y configuración no se pierden. Solo cambian los límites de tu plan.
</p>
@endsection
