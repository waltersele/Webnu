@include('themes.partials.theme-fonts')
<style>
    :root {
        --wn-tv-accent: {{ $accent ?? '#004ac6' }};
        --wn-tv-theme-accent: {{ $themeAccent ?? ($accent ?? '#c4a574') }};
        --wn-tv-bg: {{ $themeBg ?? '#0a0e14' }};
        --wn-tv-surface: {{ $themeSurface ?? 'rgba(255, 255, 255, 0.06)' }};
        --wn-tv-text: {{ $themeText ?? '#f5f7fa' }};
        --wn-tv-text-muted: {{ $themeTextMuted ?? 'rgba(245, 247, 250, 0.65)' }};
        --wn-tv-font-display: {!! $themeFontDisplay ?? "'Segoe UI', system-ui, sans-serif" !!};
        --wn-tv-font-body: {!! $themeFontBody ?? "'Segoe UI', system-ui, sans-serif" !!};
        --wn-tv-display: var(--wn-tv-font-display);
        --wn-tv-body: var(--wn-tv-font-body);
        --wn-tv-badge-bg: var(--wn-tv-theme-accent);
        --wn-tv-badge-fg: {{ $themeBadgeFg ?? '#0f0e0d' }};
        --wn-tv-price: var(--wn-tv-accent);
        --wn-tv-dot-active: var(--wn-tv-theme-accent);
        --wn-tv-bg-deep: color-mix(in srgb, var(--wn-tv-bg) 72%, #000);
        --wn-tv-bg-deeper: color-mix(in srgb, var(--wn-tv-bg) 52%, #000);
        --wn-tv-panel-border: color-mix(in srgb, var(--wn-tv-text) 10%, transparent);
        --wn-tv-panel-border-strong: color-mix(in srgb, var(--wn-tv-text) 16%, transparent);
        --wn-tv-accent-soft: color-mix(in srgb, var(--wn-tv-accent) 26%, transparent);
        --wn-tv-accent-muted: color-mix(in srgb, var(--wn-tv-accent) 14%, transparent);
        --wn-tv-theme-accent-soft: color-mix(in srgb, var(--wn-tv-theme-accent) 24%, transparent);
        --wn-tv-theme-accent-border: color-mix(in srgb, var(--wn-tv-theme-accent) 42%, transparent);
        --wn-tv-scrim: color-mix(in srgb, var(--wn-tv-bg) 50%, transparent);
        --wn-tv-scrim-strong: color-mix(in srgb, var(--wn-tv-bg) 78%, transparent);
        --wn-tv-glass: color-mix(in srgb, var(--wn-tv-text) 10%, transparent);
        --wn-tv-glass-border: color-mix(in srgb, var(--wn-tv-text) 14%, transparent);
        --wn-tv-navy: var(--wn-tv-bg);
        --wn-tv-navy-deep: var(--wn-tv-bg-deep);
        --wn-tv-gold: var(--wn-tv-theme-accent);
        --wn-tv-cream: var(--wn-tv-text);
    }
</style>
