@extends('emails.subscriptions._layout')

@section('title', 'Pon el QR en mesa')

@section('content')
    <h1>Aún no hay escaneos de tu carta</h1>
    <p>La carta está publicada, pero sin el QR en mesa o barra tus clientes no la verán. Descarga el PDF, imprímelo y colócalo junto a la carta física.</p>
    <div class="actions">
        <a class="btn" href="{{ $ctaUrl }}">{{ $ctaLabel }}</a>
    </div>
@endsection
