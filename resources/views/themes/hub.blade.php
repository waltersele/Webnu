@php
    $brand = $brandCompany ?? $companies->first();
    $brandName = $brand->name ?? ($user->name ?? 'Webnu');
    $brandLogo = $brand && $brand->logo ? url('img/' . ltrim($brand->logo, '/')) : null;
    $themeSettings = $brand ? (method_exists($brand, 'resolvedThemeSettings') ? $brand->resolvedThemeSettings() : []) : [];
    $accent = $themeSettings['accent'] ?? $themeSettings['primary'] ?? '#004ac6';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $brandName }} · Carta y menús</title>
    <meta name="description" content="Elige carta o menú de {{ $brandName }}.">
    <link rel="icon" type="image/png" href="{{ \App\PlatformSetting::brandUrl('favicon') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --wn-hub-accent: {{ $accent }};
            --wn-hub-bg: #f7f7f9;
            --wn-hub-card: #ffffff;
            --wn-hub-text: #0f172a;
            --wn-hub-muted: #64748b;
            --wn-hub-border: #e5e7eb;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            background: var(--wn-hub-bg);
            color: var(--wn-hub-text);
            font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .wn-hub-wrap {
            max-width: 720px;
            margin: 0 auto;
            padding: 28px 18px 64px;
        }
        .wn-hub-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
            text-align: center;
            padding: 16px 0 28px;
        }
        .wn-hub-brand__logo {
            width: 96px;
            height: 96px;
            border-radius: 24px;
            object-fit: cover;
            background: #fff;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.08);
            border: 1px solid var(--wn-hub-border);
        }
        .wn-hub-brand__name {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: clamp(28px, 6vw, 38px);
            margin: 0;
            line-height: 1.1;
        }
        .wn-hub-brand__lead {
            font-size: 15px;
            color: var(--wn-hub-muted);
            margin: 0;
            max-width: 420px;
        }
        .wn-hub-section {
            margin-top: 28px;
        }
        .wn-hub-section__head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 12px;
            padding: 0 4px;
        }
        .wn-hub-section__title {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--wn-hub-accent);
            margin: 0;
        }
        .wn-hub-section__count {
            font-size: 12px;
            color: var(--wn-hub-muted);
        }
        .wn-hub-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }
        @media (min-width: 560px) {
            .wn-hub-grid { grid-template-columns: 1fr 1fr; gap: 14px; }
        }
        .wn-hub-card {
            display: flex;
            flex-direction: column;
            gap: 0;
            background: var(--wn-hub-card);
            border: 1px solid var(--wn-hub-border);
            border-radius: 18px;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
        }
        .wn-hub-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.09);
            border-color: var(--wn-hub-accent);
        }
        .wn-hub-card__media {
            position: relative;
            aspect-ratio: 16 / 10;
            background: linear-gradient(135deg, #e6efff 0%, #c5d6ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .wn-hub-card__media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .wn-hub-card__media-fallback {
            font-family: 'Playfair Display', serif;
            font-size: 38px;
            font-weight: 700;
            color: rgba(15, 23, 42, 0.35);
            line-height: 1;
        }
        .wn-hub-card__body {
            padding: 14px 16px 16px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .wn-hub-card__kind {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--wn-hub-muted);
        }
        .wn-hub-card__name {
            font-size: 17px;
            font-weight: 700;
            color: var(--wn-hub-text);
            margin: 0;
            line-height: 1.25;
        }
        .wn-hub-card__sub {
            font-size: 13px;
            color: var(--wn-hub-muted);
            margin: 2px 0 0;
        }
        .wn-hub-card__price {
            margin-top: 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            background: var(--wn-hub-accent);
            color: #fff;
            font-weight: 700;
            font-size: 13px;
            border-radius: 999px;
            align-self: flex-start;
        }
        .wn-hub-footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: var(--wn-hub-muted);
        }
        .wn-hub-footer a {
            color: var(--wn-hub-accent);
            text-decoration: none;
            font-weight: 600;
        }
        .wn-hub-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <main class="wn-hub-wrap">
        <header class="wn-hub-brand">
            @if($brandLogo)
                <img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="wn-hub-brand__logo" loading="lazy">
            @endif
            <h1 class="wn-hub-brand__name">{{ $brandName }}</h1>
            <p class="wn-hub-brand__lead">Elige qué quieres ver hoy.</p>
        </header>

        @if($companies->count())
            <section class="wn-hub-section">
                <div class="wn-hub-section__head">
                    <h2 class="wn-hub-section__title">Cartas</h2>
                    <span class="wn-hub-section__count">{{ $companies->count() }}</span>
                </div>
                <div class="wn-hub-grid">
                    @foreach($companies as $c)
                        @php
                            $cLogo = $c->logo ? url('img/' . ltrim($c->logo, '/')) : null;
                            $cUrl = route('see_menu', ['ownerSlug' => $ownerSlug, 'companySlug' => $c->slug]);
                            $initial = mb_strtoupper(mb_substr($c->name ?: 'C', 0, 1));
                        @endphp
                        <a href="{{ $cUrl }}" class="wn-hub-card" aria-label="Abrir {{ $c->name }}">
                            <div class="wn-hub-card__media">
                                @if($cLogo)
                                    <img src="{{ $cLogo }}" alt="{{ $c->name }}" loading="lazy">
                                @else
                                    <span class="wn-hub-card__media-fallback">{{ $initial }}</span>
                                @endif
                            </div>
                            <div class="wn-hub-card__body">
                                <span class="wn-hub-card__kind">Carta</span>
                                <h3 class="wn-hub-card__name">{{ $c->name }}</h3>
                                @if(! empty($c->subtitle))
                                    <p class="wn-hub-card__sub">{{ $c->subtitle }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($menus->count())
            <section class="wn-hub-section">
                <div class="wn-hub-section__head">
                    <h2 class="wn-hub-section__title">Menús</h2>
                    <span class="wn-hub-section__count">{{ $menus->count() }}</span>
                </div>
                <div class="wn-hub-grid">
                    @foreach($menus as $m)
                        @php
                            $mImage = $m->imageUrl();
                            $mUrl = route('public.menu', [
                                'ownerSlug' => $ownerSlug,
                                'companySlug' => $m->company->slug,
                                'menuSlug' => $m->slug,
                            ]);
                            $initial = mb_strtoupper(mb_substr($m->name ?: 'M', 0, 1));
                        @endphp
                        <a href="{{ $mUrl }}" class="wn-hub-card" aria-label="Ver {{ $m->name }}">
                            <div class="wn-hub-card__media">
                                @if($mImage)
                                    <img src="{{ $mImage }}" alt="{{ $m->name }}" loading="lazy">
                                @else
                                    <span class="wn-hub-card__media-fallback">{{ $initial }}</span>
                                @endif
                            </div>
                            <div class="wn-hub-card__body">
                                <span class="wn-hub-card__kind">Menú</span>
                                <h3 class="wn-hub-card__name">{{ $m->name }}</h3>
                                @if(! empty($m->subtitle))
                                    <p class="wn-hub-card__sub">{{ $m->subtitle }}</p>
                                @endif
                                @if($m->formattedPrice())
                                    <span class="wn-hub-card__price">{{ $m->formattedPrice() }}</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if(! empty($showWebnuBadge))
            <footer class="wn-hub-footer">
                Hecho con <a href="https://webnu.es" target="_blank" rel="noopener">webnu.es</a>
            </footer>
        @endif
    </main>
</body>
</html>
