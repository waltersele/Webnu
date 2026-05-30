@php
    $theme = $company->resolvedThemeSettings();
    $heroVars = $company->heroCssVars();
    $headerTone = $company->headerToneIsDark() ? 'dark' : 'light';
@endphp
@include('themes.partials.theme-fonts')
<style>
:root {
    --wn-primary: {{ $theme['primary'] ?? '#0074d9' }};
    --wn-accent: {{ $theme['accent'] ?? '#e65100' }};
    --wn-bg: {{ $theme['background'] ?? '#ffffff' }};
    --wn-surface: {{ $theme['surface'] ?? '#f8f9fa' }};
    --wn-text: {{ $theme['text'] ?? '#212529' }};
    --wn-text-muted: {{ $theme['text_muted'] ?? '#6c757d' }};
    --wn-font-heading: {!! $company->themeFontFamily('font_heading') !!};
    --wn-font-body: {!! $company->themeFontFamily('font_body') !!};
    --wn-header-height: 52px;
    --wn-nav-height: 52px;
    --wn-scroll-offset: calc(var(--wn-header-height) + var(--wn-nav-height) + 8px);
    --wn-header-tone: {{ $headerTone }};
    --wn-hero-overlay-strength: {{ $heroVars['--wn-hero-overlay-strength'] ?? '0.72' }};
    --wn-hero-overlay-mode: {{ $heroVars['--wn-hero-overlay-mode'] ?? 'dark' }};
    --wn-hero-text-tone: {{ $heroVars['--wn-hero-text-tone'] ?? 'light' }};
    --wn-hero-focal-x: {{ $heroVars['--wn-hero-focal-x'] ?? '50%' }};
    --wn-hero-focal-y: {{ $heroVars['--wn-hero-focal-y'] ?? '40%' }};
    --wn-card-radius: 16px;
    --wn-card-shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
}

html {
    scroll-behavior: smooth;
}

body {
    background-color: var(--wn-bg);
    color: var(--wn-text);
    font-family: var(--wn-font-body);
    font-size: 16px;
    line-height: 1.5;
    -webkit-text-size-adjust: 100%;
}

body h1,
body h2,
body h3,
body .wn-section-title,
body .wn-category-title,
body .wn-modern-header__title {
    font-family: var(--wn-font-heading);
    font-weight: 700;
}

a {
    color: inherit;
}

.fixed-bottom-bar {
    display: none !important;
}
</style>
