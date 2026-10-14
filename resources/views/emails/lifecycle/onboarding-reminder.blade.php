@extends('emails.subscriptions._layout')

@section('title', 'Continúa tu carta')

@section('content')
    <h1>Tu carta aún no está terminada</h1>
    <p>Empezaste a configurar Webnu pero te falta algún paso. Retoma donde lo dejaste{{ !empty($context['step']) ? ' (paso ' . $context['step'] . ')' : '' }} — suelen bastar unos minutos.</p>
    <div class="actions">
        <a class="btn" href="{{ $ctaUrl }}">{{ $ctaLabel }}</a>
    </div>
@endsection
