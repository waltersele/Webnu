@extends('emails.subscriptions._layout')

@section('title', 'Prueba terminada')

@section('content')
    <h1>Tu prueba de Webnu ha terminado</h1>
    <p>Tu cuenta ha pasado al plan gratuito. Reactiva Pro para recuperar funciones avanzadas y seguir actualizando la carta sin límites.</p>
    <div class="actions">
        <a class="btn" href="{{ $ctaUrl }}">{{ $ctaLabel }}</a>
    </div>
@endsection
