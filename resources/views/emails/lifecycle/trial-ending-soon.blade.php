@extends('emails.subscriptions._layout')

@section('title', 'Prueba terminando')

@section('content')
    <h1>Tu prueba termina{{ ($context['days_left'] ?? 7) <= 1 ? ' mañana' : ' en ' . ($context['days_left'] ?? 7) . ' días' }}</h1>
    <p>Con Pro/Plus mantienes idiomas, escaneo IA, cambios ilimitados y el QR siempre actualizado. Activa el plan antes de que expire la prueba — sin perder tu carta.</p>
    <div class="actions">
        <a class="btn" href="{{ $ctaUrl }}">{{ $ctaLabel }}</a>
    </div>
@endsection
