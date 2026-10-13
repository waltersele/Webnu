@extends('emails.subscriptions._layout')

@section('title', 'Bienvenido a Webnu')

@section('content')
@php
    $name = $recipient->name ?: explode('@', $recipient->email)[0];
    $days = (int) config('plans.trial_days', 30);
    $onboardingUrl = url('/admin/onboarding');
@endphp

<h1>Hola {{ $name }}, bienvenido a Webnu</h1>

<p>
    Ya tienes tu cuenta lista. Durante <strong>{{ $days }} días</strong> disfrutas de
    <strong>Pro gratis</strong>: plantillas, escaneos IA, vídeos y traducciones según el plan Pro.
</p>

<p>
    El siguiente paso es completar el onboarding y <strong>publicar tu carta con QR</strong>
    (unos minutos).
</p>

<p class="actions">
    <a href="{{ $onboardingUrl }}" class="btn">Continuar onboarding</a>
</p>

<p>
    Cuando termine la prueba pasarás al plan <strong>Free</strong> automáticamente
    (tu carta publicada sigue online). Puedes activar Pro o Plus cuando quieras desde Ajustes.
</p>
@endsection
