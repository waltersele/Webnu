<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $registration->restaurant_name }} — Vista previa Webnu</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; background: #f8fafc; color: #0f172a; }
        .wrap { max-width: 720px; margin: 0 auto; padding: 24px 16px 48px; }
        h1 { font-size: 1.5rem; margin: 0 0 8px; }
        .badge { display: inline-block; background: #e6f1fb; color: #2563eb; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 999px; margin-bottom: 20px; }
        section { margin-bottom: 28px; }
        h2 { font-size: 1.1rem; border-bottom: 2px solid #378add; padding-bottom: 6px; }
        .dish { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid #e2e8f0; }
        .dish img { width: 72px; height: 72px; object-fit: cover; border-radius: 8px; flex-shrink: 0; }
        .price { font-weight: 600; color: #378add; }
        .muted { color: #64748b; font-size: 14px; }
    </style>
</head>
<body>
<div class="wrap">
    <span class="badge">Vista previa — Pre-Alta</span>
    <h1>{{ $registration->restaurant_name }}</h1>
    <p class="muted">Esta carta está pendiente de activación. Caduca el {{ $registration->expires_at->format('d/m/Y') }}.</p>

    @foreach($sections as $si => $section)
        <section>
            <h2>{{ $section['name'] }}</h2>
            @foreach($section['products'] ?? [] as $pi => $product)
                @php
                    $stagingKey = "s{$si}_p{$pi}";
                    $stagingPath = $product['_staging_image'] ?? ($manifest[$stagingKey] ?? null);
                    $imgUrl = $stagingPath
                        ? $mediaBaseUrl . '?path=' . urlencode($stagingPath)
                        : null;
                @endphp
                <article class="dish">
                    @if($imgUrl)
                        <img src="{{ $imgUrl }}" alt="">
                    @endif
                    <div>
                        <strong>{{ $product['name'] }}</strong>
                        @if(!empty($product['description']))
                            <p class="muted">{{ $product['description'] }}</p>
                        @endif
                        @if(!empty($product['price_unit']))
                            <p class="price">{{ $product['price_unit'] }} €</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>
    @endforeach
</div>
</body>
</html>
