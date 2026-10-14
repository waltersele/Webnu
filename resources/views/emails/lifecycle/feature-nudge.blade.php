@extends('emails.subscriptions._layout')

@section('title', 'Función recomendada')

@section('content')
    <h1>{{ $context['subject'] ?? 'Una función que te puede ayudar' }}</h1>
    <p>{{ $context['body'] ?? 'Hay una función de Webnu que aún no has usado y puede mejorar tu carta.' }}</p>
    <div class="actions">
        <a class="btn" href="{{ $ctaUrl }}">{{ $ctaLabel }}</a>
    </div>
@endsection
