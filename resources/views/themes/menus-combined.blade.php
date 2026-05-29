@php
    $brandName = $company->name ?? 'Webnu';
    $brandLogo = $company->logo ? url('img/' . ltrim($company->logo, '/')) : null;
    $themeSettings = method_exists($company, 'resolvedThemeSettings') ? $company->resolvedThemeSettings() : [];
    $accent = $themeSettings['accent'] ?? $themeSettings['primary'] ?? '#004ac6';
    $hubUrl = $ownerSlug ? route('public.owner.hub', ['ownerSlug' => $ownerSlug]) : null;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $brandName }} · Menús</title>
    <meta name="description" content="Carta de menús de {{ $brandName }}.">
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
            margin: 0; padding: 0;
            background: var(--wn-menu-bg);
            color: var(--wn-menu-text);
            font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .wn-pmenu-wrap { max-width: 760px; margin: 0 auto; padding: 0 0 80px; }

        .wn-pmenu-topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 16px; font-size: 13px;
        }
        .wn-pmenu-topbar__brand {
            display: inline-flex; align-items: center; gap: 8px;
            color: var(--wn-menu-text); font-weight: 700;
        }
        .wn-pmenu-topbar__brand img {
            width: 28px; height: 28px; border-radius: 8px; object-fit: cover;
            background: #fff; border: 1px solid var(--wn-menu-border);
        }

        .wn-pmenu-cover {
            margin: 0 16px 4px;
            padding: 18px 22px 14px;
            background: linear-gradient(135deg, color-mix(in srgb, var(--wn-menu-accent) 12%, #ffffff) 0%, #ffffff 100%);
            border: 1px solid var(--wn-menu-border);
            border-radius: 20px;
        }
        .wn-pmenu-cover__title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(22px, 5vw, 30px);
            font-weight: 700;
            margin: 0 0 4px;
        }
        .wn-pmenu-cover__sub { color: var(--wn-menu-muted); font-size: 14px; margin: 0; }

        .wn-pmenu-tabs {
            position: sticky;
            top: 0;
            z-index: 30;
            background: var(--wn-menu-bg);
            padding: 10px 16px;
            margin-top: 12px;
            border-bottom: 1px solid var(--wn-menu-border);
        }
        .wn-pmenu-tabs__inner {
            display: flex; gap: 8px;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .wn-pmenu-tabs__inner::-webkit-scrollbar { display: none; }
        .wn-pmenu-tab {
            flex: 0 0 auto;
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 14px;
            border-radius: 999px;
            background: var(--wn-menu-card);
            border: 1px solid var(--wn-menu-border);
            color: var(--wn-menu-muted);
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            white-space: nowrap;
            transition: background 160ms ease, color 160ms ease, border-color 160ms ease, transform 160ms ease;
        }
        .wn-pmenu-tab:hover { color: var(--wn-menu-text); border-color: color-mix(in srgb, var(--wn-menu-accent) 35%, var(--wn-menu-border)); }
        .wn-pmenu-tab.is-active {
            background: var(--wn-menu-accent);
            border-color: var(--wn-menu-accent);
            color: #fff;
            transform: translateY(-1px);
        }
        .wn-pmenu-tab__price {
            font-size: 11px;
            font-weight: 800;
            opacity: 0.85;
        }

        .wn-pmenu-block { margin-top: 18px; scroll-margin-top: 70px; }
        .wn-pmenu-hero {
            position: relative;
            margin: 0 16px;
            border-radius: 24px;
            overflow: hidden;
            background: linear-gradient(135deg, #e6efff 0%, #c5d6ff 100%);
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
        }
        .wn-pmenu-hero__img {
            width: 100%; aspect-ratio: 16 / 10; object-fit: cover; display: block;
        }
        .wn-pmenu-hero__overlay {
            position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(15,23,42,0) 40%, rgba(15,23,42,0.65) 100%);
            display: flex; flex-direction: column; justify-content: flex-end;
            padding: 20px 22px; color: #fff;
        }
        .wn-pmenu-hero__overlay--solid {
            position: relative; inset: auto; background: none;
            color: var(--wn-menu-text); padding: 22px;
        }
        .wn-pmenu-hero__name {
            font-family: 'Playfair Display', serif;
            font-size: clamp(24px, 5.5vw, 32px);
            font-weight: 700; margin: 0; line-height: 1.1;
        }
        .wn-pmenu-hero__sub { margin: 6px 0 0; font-size: 14px; opacity: 0.9; }
        .wn-pmenu-hero__price {
            margin-top: 12px;
            display: inline-flex; padding: 6px 14px;
            border-radius: 999px;
            background: var(--wn-menu-accent); color: #fff;
            font-weight: 700; font-size: 15px; align-self: flex-start;
        }

        .wn-pmenu-includes {
            margin: 14px 16px 0; padding: 14px 18px;
            background: var(--wn-menu-card);
            border: 1px solid var(--wn-menu-border);
            border-radius: 14px;
            font-size: 14px; color: var(--wn-menu-muted); line-height: 1.55;
        }
        .wn-pmenu-section { margin: 22px 16px 0; }
        .wn-pmenu-section__title {
            margin: 0 0 10px; padding: 0 4px;
            font-size: 13px; font-weight: 800;
            text-transform: uppercase; letter-spacing: 0.08em;
            color: var(--wn-menu-accent);
        }
        .wn-pmenu-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px; }
        .wn-pmenu-item {
            display: flex; align-items: center; gap: 12px;
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
            flex: 0 0 56px; width: 56px; height: 56px;
            border-radius: 12px; overflow: hidden;
            background: linear-gradient(135deg, #e6efff 0%, #c5d6ff 100%);
            display: flex; align-items: center; justify-content: center;
        }
        .wn-pmenu-item__media img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .wn-pmenu-item__body { flex: 1 1 auto; display: flex; flex-direction: column; min-width: 0; }
        .wn-pmenu-item__name {
            font-size: 16px; font-weight: 600;
            color: var(--wn-menu-text);
            margin: 0; line-height: 1.25; overflow-wrap: break-word;
        }
        .wn-pmenu-item__desc {
            font-size: 13px; color: var(--wn-menu-muted);
            margin: 2px 0 0; line-height: 1.35;
        }
        .wn-pmenu-item__price {
            flex: 0 0 auto; font-weight: 700; font-size: 14px;
            color: var(--wn-menu-accent); white-space: nowrap;
        }
        .wn-pmenu-empty {
            margin: 22px 16px; padding: 22px; text-align: center;
            color: var(--wn-menu-muted);
            background: var(--wn-menu-card);
            border: 1px dashed var(--wn-menu-border);
            border-radius: 14px;
        }
        .wn-pmenu-footer {
            margin-top: 36px; text-align: center; font-size: 12px;
            color: var(--wn-menu-muted);
        }
        .wn-pmenu-footer a {
            color: var(--wn-menu-accent); text-decoration: none; font-weight: 600;
        }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body>
    <main class="wn-pmenu-wrap">
        <div class="wn-pmenu-topbar">
            @if($hubUrl)
                <a href="{{ $hubUrl }}" style="color: var(--wn-menu-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                    <span aria-hidden="true">←</span>
                    <span>Volver</span>
                </a>
            @else
                <span></span>
            @endif
            <span class="wn-pmenu-topbar__brand">
                @if($brandLogo)<img src="{{ $brandLogo }}" alt="{{ $brandName }}">@endif
                {{ $brandName }}
            </span>
        </div>

        <header class="wn-pmenu-cover">
            <h1 class="wn-pmenu-cover__title">Nuestros menús</h1>
            <p class="wn-pmenu-cover__sub">Elige uno de la barra superior o desplázate para verlos todos.</p>
        </header>

        @if($menus->isEmpty())
            <div class="wn-pmenu-empty">Aún no hay menús publicados.</div>
        @else
            <nav class="wn-pmenu-tabs" data-tabs>
                <div class="wn-pmenu-tabs__inner">
                    @foreach($menus as $i => $menu)
                        <a href="#menu-{{ $menu->id }}"
                           class="wn-pmenu-tab {{ $i === 0 ? 'is-active' : '' }}"
                           data-tab="menu-{{ $menu->id }}">
                            <span>{{ $menu->name }}</span>
                            @if($menu->formattedPrice())
                                <span class="wn-pmenu-tab__price">{{ $menu->formattedPrice() }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </nav>

            @foreach($menus as $menu)
                @php
                    $menuImage = $menu->imageUrl();
                @endphp
                <section class="wn-pmenu-block" id="menu-{{ $menu->id }}">
                    <header class="wn-pmenu-hero">
                        @if($menuImage)
                            <img src="{{ $menuImage }}" alt="{{ $menu->name }}" class="wn-pmenu-hero__img" loading="lazy">
                            <div class="wn-pmenu-hero__overlay">
                                <h2 class="wn-pmenu-hero__name">{{ $menu->name }}</h2>
                                @if($menu->subtitle)<p class="wn-pmenu-hero__sub">{{ $menu->subtitle }}</p>@endif
                                @if($menu->formattedPrice())
                                    <span class="wn-pmenu-hero__price">{{ $menu->formattedPrice() }}</span>
                                @endif
                            </div>
                        @else
                            <div class="wn-pmenu-hero__overlay wn-pmenu-hero__overlay--solid">
                                <h2 class="wn-pmenu-hero__name">{{ $menu->name }}</h2>
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
                        <div class="wn-pmenu-includes">{{ $menu->includes }}</div>
                    @endif

                    @if($menu->sections->count())
                        @foreach($menu->sections as $section)
                            @php $sectionTitle = $section->name !== '' ? $section->name : 'Sin título'; @endphp
                            <div class="wn-pmenu-section">
                                @if($section->name !== '')
                                    <h3 class="wn-pmenu-section__title">{{ $sectionTitle }}</h3>
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
                                                    @if($desc)<p class="wn-pmenu-item__desc">{{ $desc }}</p>@endif
                                                </div>
                                                @if($price)<span class="wn-pmenu-item__price">{{ $price }}</span>@endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="wn-pmenu-empty">Este menú aún no tiene platos.</div>
                    @endif
                </section>
            @endforeach
        @endif

        @if(! empty($showWebnuBadge))
            <footer class="wn-pmenu-footer">
                Hecho con <a href="https://webnu.es" target="_blank" rel="noopener">webnu.es</a>
            </footer>
        @endif
    </main>

    <script>
    (function () {
        var tabs = document.querySelectorAll('[data-tab]');
        if (!tabs.length || !('IntersectionObserver' in window)) return;

        var bySlug = {};
        tabs.forEach(function (tab) { bySlug[tab.dataset.tab] = tab; });

        function activate(id) {
            tabs.forEach(function (tab) {
                tab.classList.toggle('is-active', tab.dataset.tab === id);
            });
            // Mantén el tab activo visible en horizontal
            var active = bySlug[id];
            if (active && active.scrollIntoView) {
                try {
                    active.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                } catch (_) { /* ignore */ }
            }
        }

        var sections = document.querySelectorAll('.wn-pmenu-block');
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    activate(entry.target.id);
                }
            });
        }, { rootMargin: '-30% 0px -55% 0px', threshold: 0.01 });

        sections.forEach(function (s) { observer.observe(s); });
    })();
    </script>
</body>
</html>
