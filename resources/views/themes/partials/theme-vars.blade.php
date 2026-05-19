@php
    $theme = $company->resolvedThemeSettings();
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
