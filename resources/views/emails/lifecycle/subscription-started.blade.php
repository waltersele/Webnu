@extends('emails.subscriptions._layout')

@section('title', 'Suscripción activada')

@section('content')
    <h1>¡Suscripción activada!</h1>
    <p>Gracias por confiar en Webnu{{ !empty($context['plan']) ? ' con el plan ' . $context['plan'] : '' }}. Ya tienes acceso completo a las funciones de tu plan.</p>
    <div class="actions">
        <a class="btn" href="{{ $ctaUrl }}">{{ $ctaLabel }}</a>
    </div>
@endsection
