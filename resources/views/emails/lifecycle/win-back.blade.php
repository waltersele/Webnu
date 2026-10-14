@extends('emails.subscriptions._layout')

@section('title', 'Te echamos de menos')

@section('content')
    <h1>¿Volvemos a poner tu carta en marcha?</h1>
    <p>Han pasado unos días desde que terminó tu prueba. Reactiva Pro y recuperas idiomas, IA y actualizaciones ilimitadas en un clic.</p>
    <div class="actions">
        <a class="btn" href="{{ $ctaUrl }}">{{ $ctaLabel }}</a>
    </div>
@endsection
