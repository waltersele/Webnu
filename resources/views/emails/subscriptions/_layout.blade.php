<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>@yield('title', 'Webnu')</title>
<style>
    body { margin:0; padding:0; background:#f5f7fb; font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,sans-serif; color:#1f2937; }
    .wrap { max-width:560px; margin:0 auto; padding:24px 16px; }
    .card { background:#fff; border-radius:14px; padding:32px 28px; box-shadow:0 1px 3px rgba(15,23,42,.06); }
    .logo { display:block; height:36px; margin:0 auto 24px; max-width:200px; }
    h1 { font-size:20px; line-height:1.3; margin:0 0 16px; color:#0f172a; }
    p { font-size:15px; line-height:1.6; margin:0 0 14px; color:#374151; }
    .btn { display:inline-block; padding:12px 22px; background:#004ac6; color:#fff !important; text-decoration:none; font-weight:600; border-radius:10px; font-size:15px; }
    .btn--secondary { background:#fff; color:#004ac6 !important; border:1px solid #004ac6; }
    .actions { margin:24px 0 8px; }
    .meta { font-size:13px; color:#6b7280; margin-top:24px; padding-top:16px; border-top:1px solid #e5e7eb; }
    .meta a { color:#004ac6; text-decoration:none; }
</style>
</head>
<body>
<div class="wrap">
    <div class="card">
        @if(!empty($logoUrl))
            <img src="{{ $logoUrl }}" alt="Webnu" class="logo">
        @endif

        @yield('content')

        <div class="meta">
            ¿Dudas? Escríbenos a <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.<br>
            <a href="{{ $panelUrl }}">Entrar al panel</a> · <a href="{{ $billingUrl }}">Facturación</a>
        </div>
    </div>
</div>
</body>
</html>
