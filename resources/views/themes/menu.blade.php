@php
    $brandName = $company->name ?? 'Webnu';
    $brandLogo = $company->logo ? url('img/' . ltrim($company->logo, '/')) : null;
    $themeSettings = method_exists($company, 'resolvedThemeSettings') ? $company->resolvedThemeSettings() : [];
    $accent = $themeSettings['accent'] ?? $themeSettings['primary'] ?? '#004ac6';
    $menuImage = $menu->imageUrl();
    $hubUrl = $ownerSlug ? route('public.owner.hub', ['ownerSlug' => $ownerSlug]) : null;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $menu->name }} · {{ $brandName }}</title>
    <meta name="description" content="{{ $menu->name }} en {{ $brandName }}. {{ $menu->subtitle ?? '' }}">
    <link rel="icon" type="image/png" href="{{ \App\PlatformSetting::brandUrl('favicon') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --wn-menu-accent: {{ $accent }};
            --wn-menu-bg: #f7f7f9;
            --wn-menu-card: #ffffff;
            --wn-menu-text: #0f172a;
            --wn-menu-muted: #64748b;
            --wn-menu-border: #e5e7eb;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            background: var(--wn-menu-bg);
            color: var(--wn-menu-text);
            font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .wn-pmenu-wrap {
            max-width: 720px;
            margin: 0 auto;
            padding: 0 0 80px;
        }
        .wn-pmenu-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            font-size: 13px;
        }
        .wn-pmenu-topbar a {
            color: var(--wn-menu-muted);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
        }
        .wn-pmenu-topbar a:hover { color: var(--wn-menu-accent); }
        .wn-pmenu-topbar__brand {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--wn-menu-text);
            font-weight: 700;
        }
        .wn-pmenu-topbar__brand img {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            object-fit: cover;
            background: #fff;
            border: 1px solid var(--wn-menu-border);
        }
        .wn-pmenu-hero {
            position: relative;
            margin: 0 16px;
            border-radius: 24px;
            overflow: hidden;
            background: linear-gradient(135deg, #e6efff 0%, #c5d6ff 100%);
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
        }
        .wn-pmenu-hero__img {
            width: 100%;
            aspect-ratio: 16 / 10;
            object-fit: cover;
            display: block;
        }
        .wn-pmenu-hero__overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(15,23,42,0) 40%, rgba(15,23,42,0.65) 100%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 20px 22px;
            color: #fff;
        }
        .wn-pmenu-hero__overlay--solid {
            position: relative;
            inset: auto;
            background: none;
            color: var(--wn-menu-text);
            padding: 22px;
        }
        .wn-pmenu-hero__name {
            font-family: 'Playfair Display', serif;
            font-size: clamp(26px, 6vw, 36px);
            font-weight: 700;
            margin: 0;
            line-height: 1.1;
        }
        .wn-pmenu-hero__sub {
            margin: 6px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .wn-pmenu-hero__price {
            margin-top: 12px;
            display: inline-flex;
            padding: 6px 14px;
            border-radius: 999px;
            background: var(--wn-menu-accent);
            color: #fff;
            font-weight: 700;
            font-size: 15px;
            align-self: flex-start;
        }
        .wn-pmenu-includes {
            margin: 18px 16px 0;
            padding: 14px 18px;
            background: var(--wn-menu-card);
            border: 1px solid var(--wn-menu-border);
            border-radius: 14px;
            font-size: 14px;
            color: var(--wn-menu-muted);
            line-height: 1.55;
        }
        .wn-pmenu-section {
            margin: 26px 16px 0;
        }
        .wn-pmenu-section__title {
            margin: 0 0 10px;
            padding: 0 4px;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--wn-menu-accent);
        }
        .wn-pmenu-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .wn-pmenu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--wn-menu-card);
            border: 1px solid var(--wn-menu-border);
            border-radius: 14px;
            padding: 10px 14px;
            transition: border-color 160ms ease, box-shadow 160ms ease;
        }
        .wn-pmenu-item:hover {
            border-color: var(--wn-menu-accent);
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
        }
        .wn-pmenu-item__media {
            flex: 0 0 56px;
            width: 56px;
            height: 56px;
            border-radius: 12px;
            overflow: hidden;
            background: linear-gradient(135deg, #e6efff 0%, #c5d6ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .wn-pmenu-item__media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .wn-pmenu-item__body {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .wn-pmenu-item__name {
            font-size: 16px;
            font-weight: 600;
            color: var(--wn-menu-text);
            margin: 0;
            line-height: 1.25;
            overflow-wrap: break-word;
        }
        .wn-pmenu-item__desc {
            font-size: 13px;
            color: var(--wn-menu-muted);
            margin: 2px 0 0;
            line-height: 1.35;
        }
        .wn-pmenu-item__price {
            flex: 0 0 auto;
            font-weight: 700;
            font-size: 14px;
            color: var(--wn-menu-accent);
            white-space: nowrap;
        }
        .wn-pmenu-empty {
            margin: 28px 16px;
            padding: 22px;
            text-align: center;
            color: var(--wn-menu-muted);
            background: var(--wn-menu-card);
            border: 1px dashed var(--wn-menu-border);
            border-radius: 14px;
        }
        .wn-pmenu-footer {
            margin-top: 36px;
            text-align: center;
            font-size: 12px;
            color: var(--wn-menu-muted);
        }
        .wn-pmenu-footer a {
            color: var(--wn-menu-accent);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <main class="wn-pmenu-wrap">
        <div class="wn-pmenu-topbar">
            <a href="{{ $hubUrl }}" title="Volver">
                <span aria-hidden="true">←</span>
                <span>Volver</span>
            </a>
            <span class="wn-pmenu-topbar__brand">
                @if($brandLogo)<img src="{{ $brandLogo }}" alt="{{ $brandName }}">@endif
                {{ $brandName }}
            </span>
        </div>

        <header class="wn-pmenu-hero">
            @if($menuImage)
                <img src="{{ $menuImage }}" alt="{{ $menu->name }}" class="wn-pmenu-hero__img" loading="lazy">
                <div class="wn-pmenu-hero__overlay">
                    <h1 class="wn-pmenu-hero__name">{{ $menu->name }}</h1>
                    @if($menu->subtitle)
                        <p class="wn-pmenu-hero__sub">{{ $menu->subtitle }}</p>
                    @endif
                    @if($menu->formattedPrice())
                        <span class="wn-pmenu-hero__price">{{ $menu->formattedPrice() }}</span>
                    @endif
                </div>
            @else
                <div class="wn-pmenu-hero__overlay wn-pmenu-hero__overlay--solid">
                    <h1 class="wn-pmenu-hero__name">{{ $menu->name }}</h1>
                    @if($menu->subtitle)
                        <p class="wn-pmenu-hero__sub" style="color: var(--wn-menu-muted);">{{ $menu->subtitle }}</p>
                    @endif
                    @if($menu->formattedPrice())
                        <span class="wn-pmenu-hero__price">{{ $menu->formattedPrice() }}</span>
                    @endif
                </div>
            @endif
        </header>

        @if(! empty($menu->includes))
            <div class="wn-pmenu-includes">
                {{ $menu->includes }}
            </div>
        @endif

        @if($menu->sections->count())
            @foreach($menu->sections as $section)
                @php
                    $sectionTitle = $section->name !== '' ? $section->name : 'Sin título';
                @endphp
                <section class="wn-pmenu-section">
                    @if($section->name !== '')
                        <h2 class="wn-pmenu-section__title">{{ $sectionTitle }}</h2>
                    @endif
                    @if($section->items->count())
                        <ul class="wn-pmenu-list">
                            @foreach($section->items as $item)
                                @php
                                    $img = $item->imageUrl();
                                    $name = $item->displayName();
                                    $price = $item->displayPrice();
                                    $desc = optional($item->product)->description;
                                @endphp
                                <li class="wn-pmenu-item">
                                    <div class="wn-pmenu-item__media">
                                        @if($img)
                                            <img src="{{ $img }}" alt="{{ $name }}" loading="lazy">
                                        @else
                                            <span aria-hidden="true" style="font-family: 'Playfair Display', serif; font-size: 22px; color: rgba(15,23,42,0.35);">{{ mb_substr($name, 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <div class="wn-pmenu-item__body">
                                        <p class="wn-pmenu-item__name">{{ $name }}</p>
                                        @if($desc)
                                            <p class="wn-pmenu-item__desc">{{ $desc }}</p>
                                        @endif
                                    </div>
                                    @if($price)
                                        <span class="wn-pmenu-item__price">{{ $price }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endforeach
        @else
            <div class="wn-pmenu-empty">Este menú aún no tiene platos.</div>
        @endif

        @if(! empty($showWebnuBadge))
            <footer class="wn-pmenu-footer">
                Hecho con <a href="https://webnu.es" target="_blank" rel="noopener">webnu.es</a>
            </footer>
        @endif
    </main>
</body>
</html>
