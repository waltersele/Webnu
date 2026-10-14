@extends('emails.subscriptions._layout')

@section('title', 'Bienvenido a Webnu')

@section('content')
    <h1>Hola{{ $recipient->name ? ', ' . $recipient->name : '' }}</h1>
    <p>Tu cuenta en Webnu ya está lista. En unos minutos puedes tener la carta digital publicada y el QR listo para imprimir.</p>
    <p>Empieza por el asistente: nombre del negocio, plantilla, idiomas y publicar.</p>
    <div class="actions">
        <a class="btn" href="{{ $ctaUrl }}">{{ $ctaLabel }}</a>
    </div>
    <p>Tienes <strong>30 días de prueba Plus</strong> sin tarjeta. Si necesitas ayuda, responde a este correo.</p>
@endsection
