@extends('emails.subscriptions._layout')

@section('title', 'Carta publicada')

@section('content')
    <h1>¡Tu carta está publicada!</h1>
    <p>{{ !empty($context['company_name']) ? '«' . $context['company_name'] . '» ya es visible online.' : 'Tu carta ya es visible online.' }} Descarga el QR, imprímelo y colócalo en mesas o barra.</p>
    <div class="actions">
        <a class="btn" href="{{ $ctaUrl }}">{{ $ctaLabel }}</a>
    </div>
@endsection
