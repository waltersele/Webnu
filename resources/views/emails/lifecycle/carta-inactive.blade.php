@extends('emails.subscriptions._layout')

@section('title', 'Carta no visible')

@section('content')
    <h1>Tu carta aún no se ve al escanear</h1>
    <p>Si alguien escanea el QR ahora, la carta no aparece porque todavía no está publicada. Actívala desde el panel o termina el onboarding.</p>
    <div class="actions">
        <a class="btn" href="{{ $ctaUrl }}">{{ $ctaLabel }}</a>
    </div>
@endsection
