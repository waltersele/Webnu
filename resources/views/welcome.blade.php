<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Webnu') }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .subscription-card { max-width: 480px; margin: 3rem auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow subscription-card">
            <div class="card-body p-4">
                <h1 class="h4 mb-3 text-center">Alta Webnu</h1>
                <p class="text-muted text-center small mb-4">30 días gratis · Sin permanencia</p>

                <form id="subscription-form" action="{{ route('process_subscription') }}" method="POST">
                    @csrf
                    @include('partials.subscription-form-fields')
                    <button type="submit" class="btn btn-primary btn-block mt-3">Contratar | 30 días GRATIS</button>
                </form>

                <p class="text-center mt-3 mb-0 small">
                    <a href="{{ route('home') }}">Volver al inicio</a>
                    ·
                    <a href="{{ route('login') }}">Iniciar sesión</a>
                </p>
            </div>
        </div>
    </div>
</body>
@include('partials.subscription-stripe-scripts')
</html>
