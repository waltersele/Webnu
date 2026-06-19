<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Cuenta suspendida · Webnu</title>
    <link rel="icon" type="image/png" href="{{ \App\PlatformSetting::brandUrl('favicon') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: Inter, system-ui, sans-serif;
            background: #f7f7f9;
            color: #0f172a;
        }
        .card {
            max-width: 480px;
            width: 100%;
            background: #fff;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);
        }
        h1 { font-size: 1.35rem; margin: 0 0 12px; }
        p { margin: 0 0 16px; line-height: 1.55; color: #475569; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 24px; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.95rem;
        }
        .btn-primary { background: #004ac6; color: #fff; }
        .btn-secondary { background: #e2e8f0; color: #0f172a; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Tu cuenta está suspendida</h1>
        <p>
            No puedes acceder al panel mientras la cuenta esté suspendida.
            Si tenías un periodo de prueba o una suscripción de pago, reactiva tu plan para volver a publicar tus cartas.
        </p>
        <p>Si crees que se trata de un error, escríbenos a {{ \App\PlatformSetting::contactPublicEmail() }}.</p>
        <div class="actions">
            @auth
                <a href="{{ route('admin.billing') }}" class="btn btn-primary">Ver facturación</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">Iniciar sesión</a>
            @endauth
            <a href="{{ url('/') }}" class="btn btn-secondary">Ir a webnu.es</a>
        </div>
    </div>
</body>
</html>
