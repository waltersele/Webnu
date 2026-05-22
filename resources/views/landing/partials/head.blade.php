<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="description" content="{{ __('landing.meta.description') }}"/>
<title>{{ __('landing.meta.title') }}</title>
@isset($landingLocales, $locale)
    @php
        $homeUrl = url('/');
    @endphp
    @foreach($landingLocales as $code => $meta)
        <link rel="alternate" hreflang="{{ $meta['hreflang'] ?? $code }}" href="{{ $homeUrl }}?lang={{ $code }}"/>
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $homeUrl }}?lang={{ config('landing.fallback_locale', 'en') }}"/>
@endisset
<link rel="icon" type="image/png" href="{{ \App\PlatformSetting::brandUrl('favicon') }}"/>
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="theme-color" content="#004ac6">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Webnu">
<link rel="apple-touch-icon" href="{{ asset('img/pwa/icon-192.png') }}">
<style>
    .landing-pwa-install.is-ready,
    .landing-pwa-install.wn-pwa-installable {
        display: inline-flex !important;
        animation: landing-pwa-pulse 2s ease-in-out 3;
    }
    @keyframes landing-pwa-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(0, 74, 198, 0.35); }
        50% { box-shadow: 0 0 0 6px rgba(0, 74, 198, 0); }
    }
    .wn-shell-topbar__pwa-btn.wn-pwa-installable {
        border-color: #378add;
        background: #eff6ff;
        box-shadow: 0 0 0 2px rgba(55, 138, 221, 0.25);
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "text-muted": "#4b5563",
                    "on-surface": "#141b2b",
                    "surface": "#f9f9ff",
                    "background": "#f9f9ff",
                    "primary": "#004ac6",
                    "primary-container": "#2563eb",
                    "on-primary": "#ffffff",
                    "border-subtle": "#e5e7eb",
                    "surface-container-low": "#f1f3ff",
                    "surface-container-lowest": "#ffffff",
                    "surface-container": "#e9edff",
                    "surface-container-high": "#e1e8fd",
                    "surface-alt": "#f9fafb",
                    "outline-variant": "#c3c6d7",
                    "on-surface-variant": "#434655",
                    "primary-fixed": "#dbe1ff",
                },
                borderRadius: {
                    DEFAULT: "0.25rem",
                    lg: "0.5rem",
                    xl: "0.75rem",
                    full: "9999px",
                },
                spacing: {
                    "container-max": "1200px",
                    "margin-mobile": "16px",
                    "gutter": "24px",
                },
                fontFamily: {
                    headline: ["Inter", "system-ui", "sans-serif"],
                    body: ["Inter", "system-ui", "sans-serif"],
                },
                fontSize: {
                    "headline-xl": ["clamp(2rem,5vw,3rem)", { lineHeight: "1.12", letterSpacing: "-0.02em", fontWeight: "800" }],
                    "headline-lg": ["2rem", { lineHeight: "1.2", letterSpacing: "-0.01em", fontWeight: "700" }],
                    "headline-md": ["1.5rem", { lineHeight: "1.3", fontWeight: "600" }],
                    "body-lg": ["1.125rem", { lineHeight: "1.65" }],
                    "body-md": ["1rem", { lineHeight: "1.5" }],
                    "label-md": ["0.875rem", { lineHeight: "1.4", fontWeight: "500" }],
                    "label-sm": ["0.75rem", { lineHeight: "1.3", fontWeight: "600" }],
                },
            },
        },
    };
</script>
<style>
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        display: inline-block;
        vertical-align: middle;
    }
    body {
        font-family: 'Inter', system-ui, sans-serif;
        background-color: #f9f9ff;
        color: #141b2b;
    }
    .font-headline { letter-spacing: -0.02em; }
    #hero-headline {
        transition: opacity 0.45s ease, transform 0.45s ease;
    }
    #hero-headline.is-fading {
        opacity: 0;
        transform: translateY(12px);
    }
    .step-active { background-color: #2563eb; }
    .step-inactive { background-color: #e5e7eb; }
    .faq-content { max-height: 0; overflow: hidden; transition: max-height 0.35s ease-out; }
    .faq-item.faq-open .faq-content { max-height: 520px; }
    .faq-item.faq-open .faq-icon { transform: rotate(180deg); }

    /* Mock carta con reel en card */
    .landing-menu-mock {
        border: 1px solid #e5e7eb;
        border-radius: 1.25rem;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 24px 48px rgba(20, 27, 43, 0.08);
    }
    .landing-menu-mock__chrome {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 10px 14px;
        background: #f1f3ff;
        border-bottom: 1px solid #e5e7eb;
    }
    .landing-menu-mock__dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #c3c6d7;
    }
    .landing-menu-mock__title {
        margin-left: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #4b5563;
    }
    .landing-menu-mock__body {
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: #f9f9ff;
    }
    .landing-menu-card {
        display: grid;
        grid-template-columns: 108px 1fr;
        gap: 12px;
        padding: 10px;
        border-radius: 12px;
        background: #fff;
        border: 1px solid #e5e7eb;
    }
    .landing-menu-card__media {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        background: #0a0a0a;
        aspect-ratio: 4 / 5;
        min-height: 108px;
    }
    .landing-menu-card__media img,
    .landing-menu-card__reel {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .landing-menu-card__badge {
        position: absolute;
        top: 6px;
        left: 6px;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        gap: 2px;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 0.625rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        background: rgba(0, 0, 0, 0.55);
        color: #fff;
    }
    .landing-menu-card__content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 4px;
        min-width: 0;
    }
    .landing-menu-card__head {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 8px;
    }
    .landing-menu-card__head h4 {
        font-size: 0.9375rem;
        font-weight: 700;
        color: #141b2b;
        line-height: 1.2;
    }
    .landing-menu-card__price {
        font-size: 0.875rem;
        font-weight: 700;
        color: #004ac6;
        white-space: nowrap;
    }
    .landing-menu-card__content p {
        font-size: 0.75rem;
        line-height: 1.45;
        color: #4b5563;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Animación personalización carta */
    .landing-customize-wrap {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        align-items: center;
    }
    .landing-customize-phone {
        --cust-primary: #004ac6;
        --cust-bg: #ffffff;
        --cust-surface: #f1f3ff;
        --cust-text: #141b2b;
        --cust-muted: #4b5563;
        --cust-thumb: linear-gradient(135deg, #dbe1ff 0%, #93c5fd 100%);
        width: 100%;
        max-width: 280px;
        border-radius: 1.5rem;
        border: 1px solid #e5e7eb;
        background: var(--cust-bg);
        box-shadow: 0 24px 48px rgba(20, 27, 43, 0.12);
        overflow: hidden;
        transition: background 0.6s ease, border-color 0.6s ease, box-shadow 0.6s ease;
    }
    .landing-customize-phone.is-switching .landing-customize-phone__dish,
    .landing-customize-phone.is-switching .landing-customize-phone__price,
    .landing-customize-phone.is-switching .landing-customize-phone__desc,
    .landing-customize-phone.is-switching .landing-customize-phone__business {
        opacity: 0;
        transform: translateY(6px);
    }
    .landing-customize-phone__status {
        display: flex;
        gap: 5px;
        padding: 10px 14px 0;
    }
    .landing-customize-phone__status span {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #c3c6d7;
    }
    .landing-customize-phone__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        padding: 12px 14px 8px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    }
    .landing-customize-phone__business {
        font-size: 0.8125rem;
        font-weight: 700;
        color: var(--cust-text);
        transition: color 0.5s ease, opacity 0.35s ease, transform 0.35s ease;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .landing-customize-phone__tpl {
        flex-shrink: 0;
        font-size: 0.625rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 3px 8px;
        border-radius: 999px;
        background: var(--cust-primary);
        color: #fff;
        transition: background 0.6s ease;
    }
    .landing-customize-phone__section {
        padding: 8px 14px 4px;
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--cust-muted);
        transition: color 0.5s ease;
    }
    .landing-customize-phone__card {
        display: grid;
        grid-template-columns: 72px 1fr;
        gap: 10px;
        margin: 8px 14px 16px;
        padding: 10px;
        border-radius: 12px;
        background: var(--cust-surface);
        transition: background 0.6s ease;
    }
    .landing-customize-phone__thumb {
        border-radius: 8px;
        background: var(--cust-thumb);
        aspect-ratio: 1;
        transition: background 0.6s ease;
    }
    .landing-customize-phone__row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 6px;
        margin-bottom: 4px;
    }
    .landing-customize-phone__dish {
        font-size: 0.8125rem;
        font-weight: 700;
        color: var(--cust-text);
        line-height: 1.2;
        transition: color 0.5s ease, opacity 0.35s ease, transform 0.35s ease;
    }
    .landing-customize-phone__price {
        font-size: 0.8125rem;
        font-weight: 700;
        color: var(--cust-primary);
        white-space: nowrap;
        transition: color 0.5s ease, opacity 0.35s ease, transform 0.35s ease;
    }
    .landing-customize-phone__desc {
        font-size: 0.6875rem;
        line-height: 1.4;
        color: var(--cust-muted);
        transition: color 0.5s ease, opacity 0.35s ease, transform 0.35s ease;
    }
    .landing-customize-controls {
        width: 100%;
        max-width: 280px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 14px 16px;
        border-radius: 1rem;
        border: 1px solid #e5e7eb;
        background: #fff;
    }
    .landing-customize-controls__row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .landing-customize-controls__label {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #434655;
        white-space: nowrap;
    }
    .landing-customize-swatches {
        display: flex;
        gap: 6px;
    }
    .landing-customize-swatch {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 2px solid transparent;
        cursor: default;
        transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
    }
    .landing-customize-swatch.is-active {
        border-color: #141b2b;
        transform: scale(1.12);
        box-shadow: 0 0 0 2px #fff, 0 0 0 4px #141b2b;
    }
    .landing-customize-hint {
        font-size: 0.75rem;
        color: #4b5563;
        text-align: right;
        transition: opacity 0.35s ease;
    }
    .landing-customize-hint.is-fading {
        opacity: 0;
    }

    /* Hero — TVPik protagonista */
    .landing-hero-badge--tvpik {
        background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
        color: #fff;
        border: none;
        box-shadow: 0 4px 14px rgba(234, 88, 12, 0.35);
    }
    .landing-hero-visual {
        width: 100%;
    }
    .landing-hero-signup {
        margin-top: 0;
    }

    /* TVPik — escena bar + TV animada */
    .landing-tvpik-scene {
        position: relative;
        min-height: 420px;
        border-radius: 1.5rem;
        overflow: hidden;
        background: linear-gradient(165deg, #1a120c 0%, #2a2118 45%, #141010 100%);
        box-shadow: 0 28px 56px rgba(20, 27, 43, 0.22);
        padding: 1.75rem 1.5rem 1.25rem;
    }
    .landing-tvpik-scene--hero {
        min-height: 360px;
        padding: 1.35rem 1.25rem 1rem;
        box-shadow: 0 32px 64px rgba(234, 88, 12, 0.18), 0 20px 48px rgba(20, 27, 43, 0.2);
    }
    .landing-tvpik-scene--hero .landing-tvpik-tv {
        max-width: 100%;
    }
    .landing-tvpik-scene__hero-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        z-index: 5;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        background: rgba(234, 88, 12, 0.92);
        color: #fff;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }
    .landing-tvpik-scene__ambience {
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 70% 50% at 75% 35%, rgba(255, 140, 50, 0.12) 0%, transparent 55%),
            radial-gradient(ellipse 40% 30% at 15% 80%, rgba(0, 74, 198, 0.08) 0%, transparent 50%);
        pointer-events: none;
    }
    .landing-tvpik-phone {
        position: relative;
        z-index: 3;
        width: 148px;
        margin-bottom: 0.75rem;
        border-radius: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.12);
        background: #fff;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35);
        overflow: hidden;
        transition: transform 0.4s ease, box-shadow 0.4s ease;
    }
    .landing-tvpik-phone.is-publishing {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 16px 40px rgba(37, 99, 235, 0.25);
    }
    .landing-tvpik-phone__bar {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 8px 10px;
        background: #f1f3ff;
        border-bottom: 1px solid #e5e7eb;
    }
    .landing-tvpik-phone__bar span:first-child,
    .landing-tvpik-phone__bar span:nth-child(2),
    .landing-tvpik-phone__bar span:nth-child(3) {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #c3c6d7;
    }
    .landing-tvpik-phone__label {
        margin-left: 4px;
        font-size: 0.5625rem;
        font-weight: 700;
        color: #4b5563;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .landing-tvpik-phone__body {
        padding: 10px 12px 12px;
    }
    .landing-tvpik-phone__chip {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-size: 0.5625rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #004ac6;
        background: #dbe1ff;
        padding: 2px 8px;
        border-radius: 999px;
        margin-bottom: 6px;
    }
    .landing-tvpik-phone__action {
        font-size: 0.75rem;
        font-weight: 600;
        color: #141b2b;
        line-height: 1.35;
        margin: 0 0 6px;
        transition: opacity 0.35s ease, transform 0.35s ease;
    }
    .landing-tvpik-phone.is-switching .landing-tvpik-phone__action {
        opacity: 0;
        transform: translateY(4px);
    }
    .landing-tvpik-phone__status {
        font-size: 0.625rem;
        font-weight: 600;
        color: #2563eb;
    }
    .landing-tvpik-phone__status.is-done {
        color: #16a34a;
    }
    .landing-tvpik-sync {
        position: absolute;
        z-index: 2;
        left: 148px;
        top: 72px;
        display: flex;
        align-items: center;
        gap: 0;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }
    .landing-tvpik-sync.is-active {
        opacity: 1;
    }
    .landing-tvpik-sync__dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #2563eb;
        box-shadow: 0 0 12px rgba(37, 99, 235, 0.8);
        animation: landing-tvpik-pulse 0.8s ease-in-out infinite;
    }
    .landing-tvpik-sync__line {
        width: 0;
        height: 2px;
        background: linear-gradient(90deg, #2563eb, #7ec8ff);
        border-radius: 2px;
        transition: width 0.6s ease;
    }
    .landing-tvpik-sync.is-active .landing-tvpik-sync__line {
        width: 56px;
    }
    .landing-tvpik-sync__label {
        margin-left: 6px;
        font-size: 0.625rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: rgba(255, 255, 255, 0.7);
    }
    @keyframes landing-tvpik-pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.3); opacity: 0.7; }
    }
    .landing-tvpik-tv {
        position: relative;
        z-index: 1;
        max-width: 340px;
        margin: 0 auto;
    }
    .landing-tvpik-tv__mount {
        width: 60%;
        height: 8px;
        margin: 0 auto 4px;
        background: linear-gradient(180deg, #3a3a3a, #1a1a1a);
        border-radius: 2px 2px 0 0;
        position: relative;
    }
    .landing-tvpik-tv__mount::before,
    .landing-tvpik-tv__mount::after {
        content: '';
        position: absolute;
        top: 8px;
        width: 3px;
        height: 14px;
        background: #2a2a2a;
    }
    .landing-tvpik-tv__mount::before { left: 22%; }
    .landing-tvpik-tv__mount::after { right: 22%; }
    .landing-tvpik-tv__bezel {
        padding: 10px;
        background: linear-gradient(180deg, #2a2a2a 0%, #111 100%);
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
    }
    .landing-tvpik-tv__screen {
        position: relative;
        aspect-ratio: 16 / 10;
        border-radius: 4px;
        overflow: hidden;
        background: #0a0a0a;
    }
    .landing-tvpik-tv__screen::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(115deg, transparent 40%, rgba(255, 255, 255, 0.06) 50%, transparent 60%);
        animation: landing-tvpik-shine 4s ease-in-out infinite;
        pointer-events: none;
        z-index: 4;
    }
    @keyframes landing-tvpik-shine {
        0%, 100% { transform: translateX(-100%); }
        50% { transform: translateX(100%); }
    }
    .landing-tvpik-tv__screen--warm .landing-tvpik-tv__overlay {
        background: linear-gradient(0deg, rgba(20, 10, 0, 0.88) 0%, rgba(20, 10, 0, 0.2) 55%, transparent 100%);
    }
    .landing-tvpik-tv__screen--dark .landing-tvpik-tv__overlay {
        background: linear-gradient(0deg, rgba(5, 10, 20, 0.92) 0%, rgba(5, 10, 20, 0.35) 60%, transparent 100%);
    }
    .landing-tvpik-tv__screen--dark .landing-tvpik-tv__tag {
        background: rgba(126, 200, 255, 0.25);
        color: #7ec8ff;
    }
    .landing-tvpik-tv__screen--dark .landing-tvpik-tv__price {
        color: #7ec8ff;
    }
    .landing-tvpik-tv__screen--menu .landing-tvpik-tv__photo {
        width: 42%;
    }
    .landing-tvpik-tv__screen--menu .landing-tvpik-tv__overlay {
        left: 42%;
        right: 0;
        background: linear-gradient(90deg, rgba(255, 255, 255, 0.95) 0%, #fff 20%);
        color: #141b2b;
        justify-content: center;
        padding: 12px 14px;
    }
    .landing-tvpik-tv__screen--menu .landing-tvpik-tv__title,
    .landing-tvpik-tv__screen--menu .landing-tvpik-tv__price {
        color: #141b2b;
    }
    .landing-tvpik-tv__screen--menu .landing-tvpik-tv__tag {
        background: #004ac6;
        color: #fff;
    }
    .landing-tvpik-tv__screen--menu .landing-tvpik-tv__price {
        color: #004ac6;
    }
    .landing-tvpik-tv.is-switching .landing-tvpik-tv__photo,
    .landing-tvpik-tv.is-switching .landing-tvpik-tv__overlay > * {
        opacity: 0;
        transform: translateY(8px);
    }
    .landing-tvpik-tv__photo {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: opacity 0.45s ease, transform 0.45s ease;
    }
    .landing-tvpik-tv__overlay {
        position: absolute;
        inset: 0;
        z-index: 2;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 14px 16px;
        background: linear-gradient(0deg, rgba(20, 10, 0, 0.88) 0%, transparent 60%);
        transition: background 0.5s ease;
    }
    .landing-tvpik-tv__overlay > * {
        transition: opacity 0.4s ease, transform 0.4s ease;
    }
    .landing-tvpik-tv__tag {
        align-self: flex-start;
        font-size: 0.5625rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 3px 8px;
        border-radius: 999px;
        background: rgba(255, 120, 0, 0.85);
        color: #fff;
        margin-bottom: 6px;
    }
    .landing-tvpik-tv__title {
        font-size: 1rem;
        font-weight: 800;
        color: #fff;
        line-height: 1.15;
        margin: 0 0 2px;
    }
    .landing-tvpik-tv__items {
        list-style: none;
        margin: 4px 0 6px;
        padding: 0;
        font-size: 0.6875rem;
        line-height: 1.5;
        color: #434655;
    }
    .landing-tvpik-tv__items li::before {
        content: '· ';
        color: #004ac6;
        font-weight: 700;
    }
    .landing-tvpik-tv__items.hidden {
        display: none;
    }
    .landing-tvpik-tv__price {
        font-size: 0.9375rem;
        font-weight: 800;
        color: #ffb800;
        margin: 0;
    }
    .landing-tvpik-tv__brand {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 3;
        font-size: 0.5625rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.55);
    }
    .landing-tvpik-tv__live {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 3;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.5625rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        color: #fff;
        background: rgba(220, 38, 38, 0.85);
        padding: 3px 8px;
        border-radius: 999px;
    }
    .landing-tvpik-tv__live span {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #fff;
        animation: landing-tvpik-live 1.2s ease-in-out infinite;
    }
    @keyframes landing-tvpik-live {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    .landing-tvpik-tv__updated {
        position: absolute;
        bottom: 50%;
        left: 50%;
        transform: translate(-50%, 50%) scale(0.9);
        z-index: 5;
        font-size: 0.75rem;
        font-weight: 700;
        color: #fff;
        background: rgba(22, 163, 74, 0.92);
        padding: 6px 14px;
        border-radius: 999px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
    .landing-tvpik-tv__updated.is-visible {
        opacity: 1;
        transform: translate(-50%, 50%) scale(1);
    }
    .landing-tvpik-tv__glow {
        position: absolute;
        inset: 20% 5% -10%;
        background: radial-gradient(ellipse at center, rgba(37, 99, 235, 0.2) 0%, transparent 70%);
        pointer-events: none;
        z-index: 0;
    }
    .landing-tvpik-dots {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 1rem;
        position: relative;
        z-index: 2;
    }
    .landing-tvpik-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.25);
        transition: background 0.3s ease, transform 0.3s ease;
    }
    .landing-tvpik-dot.is-active {
        background: #fff;
        transform: scale(1.2);
    }
    @media (max-width: 640px) {
        .landing-tvpik-scene {
            min-height: 380px;
            padding: 1.25rem 1rem 1rem;
        }
        .landing-tvpik-scene--hero {
            min-height: 320px;
        }
        .landing-tvpik-tv {
            max-width: 100%;
        }
        .landing-tvpik-sync {
            left: 130px;
            top: 64px;
        }
        .landing-tvpik-sync.is-active .landing-tvpik-sync__line {
            width: 36px;
        }
    }

    /* Cartas en TV — ilustración fast food / restaurante */
    .landing-tv-wall__scene {
        position: relative;
        background: linear-gradient(180deg, #1a1f26 0%, #2d3540 55%, #3d4654 100%);
        border-radius: 1.25rem;
        padding: 1rem 1rem 2.5rem;
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
        overflow: hidden;
    }
    .landing-tv-wall__banner {
        background: linear-gradient(90deg, #c41e3a, #e63946);
        margin: -1rem -1rem 0.75rem;
        padding: 0.55rem 1rem;
        text-align: center;
    }
    .landing-tv-wall__banner-text {
        font-size: clamp(0.65rem, 2.5vw, 0.8rem);
        font-weight: 800;
        letter-spacing: 0.14em;
        color: #fff7c2;
        text-shadow: 0 1px 0 rgba(0, 0, 0, 0.25);
    }
    .landing-tv-wall__screens {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.45rem;
        min-height: 220px;
    }
    .landing-tv-wall__screen {
        background: #f4f5f7;
        border-radius: 6px;
        border: 2px solid #1f2937;
        padding: 0.45rem 0.4rem;
        font-size: 0.5rem;
        color: #111827;
        overflow: hidden;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.6);
    }
    .landing-tv-wall__screen-title {
        font-weight: 800;
        font-size: 0.48rem;
        letter-spacing: 0.06em;
        margin: 0 0 0.35rem;
        color: #b91c1c;
    }
    .landing-tv-wall__menu-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .landing-tv-wall__menu-list li {
        display: flex;
        justify-content: space-between;
        gap: 0.2rem;
        padding: 0.12rem 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        line-height: 1.2;
    }
    .landing-tv-wall__menu-list strong {
        color: #b91c1c;
        font-size: 0.46rem;
        white-space: nowrap;
    }
    .landing-tv-wall__screen--combo {
        background: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .landing-tv-wall__screen-kicker {
        font-weight: 900;
        font-size: 0.52rem;
        margin: 0 0 0.25rem;
        color: #111;
    }
    .landing-tv-wall__combo-photo {
        width: 100%;
        flex: 1;
        min-height: 72px;
        border-radius: 4px;
        background: linear-gradient(145deg, #d97706 0%, #92400e 45%, #451a03 100%);
        position: relative;
    }
    .landing-tv-wall__combo-photo::after {
        content: "";
        position: absolute;
        inset: 18% 12% 28%;
        border-radius: 40% 40% 8% 8%;
        background: linear-gradient(180deg, #fbbf24, #b45309);
        box-shadow: 0 4px 0 #78350f;
    }
    .landing-tv-wall__combo-price {
        margin: 0.25rem 0 0;
        font-weight: 700;
        font-size: 0.42rem;
        color: #374151;
    }
    .landing-tv-wall__drinks-row {
        display: flex;
        gap: 0.2rem;
        margin-bottom: 0.35rem;
    }
    .landing-tv-wall__drinks-row span {
        flex: 1;
        height: 36px;
        border-radius: 4px;
        background: linear-gradient(180deg, #f472b6, #db2777);
    }
    .landing-tv-wall__drinks-row span:nth-child(2) {
        background: linear-gradient(180deg, #60a5fa, #2563eb);
    }
    .landing-tv-wall__drinks-row span:nth-child(3) {
        background: linear-gradient(180deg, #fbbf24, #d97706);
    }
    .landing-tv-wall__counter {
        height: 28px;
        margin: 0.65rem -1rem -2.5rem;
        background: linear-gradient(180deg, #6b7280, #4b5563);
        border-top: 3px solid #9ca3af;
    }
    .landing-tv-wall__brand {
        position: absolute;
        bottom: 0.65rem;
        right: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        background: rgba(255, 255, 255, 0.95);
        padding: 0.25rem 0.5rem;
        border-radius: 999px;
        font-size: 0.6rem;
        font-weight: 700;
        color: #004ac6;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    .landing-tv-wall__logo {
        height: 18px;
        width: auto;
    }
    @media (max-width: 640px) {
        .landing-tv-wall__screens {
            grid-template-columns: repeat(2, 1fr);
            min-height: 280px;
        }
        .landing-tv-wall__screen--mirror {
            display: none;
        }
    }

    .landing-plan-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        font-size: 0.625rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        padding: 0.2rem 0.5rem;
        border-radius: 999px;
        line-height: 1.2;
    }
    .landing-plan-badge--pro {
        background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%);
        color: #fff;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
    }
    .landing-plan-badge--plus {
        background: linear-gradient(135deg, #b45309 0%, #ea580c 100%);
        color: #fff;
        box-shadow: 0 2px 8px rgba(234, 88, 12, 0.25);
    }
    .landing-plan-badge--unlimited {
        background: linear-gradient(135deg, #b45309 0%, #ea580c 100%);
        color: #fff;
        box-shadow: 0 2px 8px rgba(234, 88, 12, 0.25);
    }
    .landing-feat--premium {
        background: linear-gradient(180deg, #fafbff 0%, #ffffff 100%);
    }

    /* Modal sugerencias */
    .landing-modal {
        position: fixed;
        inset: 0;
        z-index: 100;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .landing-modal[hidden] {
        display: none;
    }
    .landing-modal__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(20, 27, 43, 0.45);
        backdrop-filter: blur(4px);
    }
    .landing-modal__dialog {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 440px;
        max-height: calc(100vh - 32px);
        overflow-y: auto;
        padding: 28px 24px 24px;
        border-radius: 1.25rem;
        border: 1px solid #e5e7eb;
        background: #fff;
        box-shadow: 0 24px 64px rgba(20, 27, 43, 0.18);
        animation: landing-modal-in 0.25s ease-out;
    }
    @keyframes landing-modal-in {
        from { opacity: 0; transform: translateY(16px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .landing-modal__close {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 50%;
        background: #f1f3ff;
        color: #434655;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s, color 0.2s;
    }
    .landing-modal__close:hover {
        background: #e1e8fd;
        color: #004ac6;
    }
    .landing-modal__header {
        padding-right: 36px;
        margin-bottom: 20px;
    }
    .landing-modal__icon {
        font-size: 28px;
        color: #004ac6;
        margin-bottom: 8px;
    }
    .landing-modal__form button:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .landing-user-menu__panel:not(.hidden) {
        display: block;
    }
    .landing-user-menu.is-open .landing-user-menu__chevron {
        transform: rotate(180deg);
    }

    .landing-lang-select {
        position: relative;
    }
    .landing-lang-select__trigger {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        padding: 6px 10px;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
        font-family: inherit;
        line-height: 1.2;
    }
    .landing-lang-select__trigger:hover,
    .landing-lang-select__trigger:focus-visible {
        border-color: #004ac6;
        box-shadow: 0 0 0 2px rgba(0, 74, 198, 0.15);
        outline: none;
    }
    .landing-lang-select__flag {
        width: 22px;
        height: 16px;
        flex-shrink: 0;
        border-radius: 2px;
        box-shadow: 0 0 0 1px rgba(15, 23, 42, 0.08);
    }
    .landing-lang-select__label {
        white-space: nowrap;
    }
    .landing-lang-select__chevron {
        font-size: 18px;
        color: #64748b;
        margin-left: 2px;
    }
    .landing-lang-select__menu[hidden] {
        display: none !important;
    }
    .landing-lang-select__menu {
        position: absolute;
        right: 0;
        top: calc(100% + 6px);
        z-index: 200;
        min-width: 100%;
        margin: 0;
        padding: 6px;
        list-style: none;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.14);
    }
    .landing-lang-select__option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 10px;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        text-decoration: none;
        white-space: nowrap;
        transition: background 0.15s;
    }
    .landing-lang-select__option:hover {
        background: #f1f3ff;
        color: #004ac6;
    }
    .landing-lang-select__option.is-active {
        background: rgba(0, 74, 198, 0.08);
        color: #004ac6;
    }

    .landing-brand-logo {
        display: block;
        height: 36px;
        width: auto;
        max-width: 160px;
        object-fit: contain;
    }
    .landing-brand-logo--footer {
        height: 32px;
    }

    .landing-template-card {
        display: flex;
        flex-direction: column;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .landing-template-card:hover {
        border-color: rgba(0, 74, 198, 0.35);
        box-shadow: 0 8px 24px rgba(20, 27, 43, 0.08);
    }
    .landing-template-card__thumb {
        aspect-ratio: 4 / 3;
        background: #f1f3ff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px;
    }
    .landing-template-card__thumb img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    .landing-template-card__body {
        padding: 12px 14px 14px;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .landing-template-card__label {
        font-size: 0.875rem;
        font-weight: 700;
        color: #141b2b;
        line-height: 1.2;
    }
    .landing-template-card__desc {
        font-size: 0.75rem;
        line-height: 1.4;
        color: #4b5563;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .landing-template-more {
        border: 2px dashed #c3c6d7;
        border-radius: 12px;
        background: linear-gradient(135deg, #f9f9ff 0%, #e9edff 100%);
    }

    .landing-scan-demo {
        position: relative;
        padding: 8px 8px 0;
    }
    .landing-scan-demo__frame {
        position: relative;
        background: #0f172a;
        border-radius: 20px;
        padding: 20px 18px 24px;
        overflow: hidden;
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.28);
    }
    .landing-scan-demo__section {
        margin: 0 0 10px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.12em;
        color: #94a3b8;
        text-transform: uppercase;
    }
    .landing-scan-demo__section--spaced {
        margin-top: 16px;
    }
    .landing-scan-demo__row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 12px;
        margin-bottom: 8px;
        font-size: 14px;
        color: #e2e8f0;
    }
    .landing-scan-demo__row strong {
        color: #38bdf8;
        font-weight: 700;
        white-space: nowrap;
    }
    .landing-scan-demo__scanline {
        position: absolute;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, transparent, #38bdf8 20%, #0074da 50%, #38bdf8 80%, transparent);
        box-shadow: 0 0 16px rgba(56, 189, 248, 0.9), 0 0 32px rgba(0, 116, 218, 0.5);
        animation: landing-scan-sweep 2.8s ease-in-out infinite;
        pointer-events: none;
    }
    @keyframes landing-scan-sweep {
        0%, 100% { top: 18%; opacity: 0.6; }
        50% { top: 72%; opacity: 1; }
    }
    @media (prefers-reduced-motion: reduce) {
        .landing-scan-demo__scanline { animation: none; top: 45%; }
    }
    .landing-scan-demo__badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 14px auto 0;
        padding: 10px 16px;
        border-radius: 999px;
        background: #16a34a;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        box-shadow: 0 8px 20px rgba(22, 163, 74, 0.35);
        width: fit-content;
        position: relative;
        left: 50%;
        transform: translateX(-50%);
    }
    .landing-scan-path-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 52px;
        padding: 14px 18px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        transition: opacity 0.2s, box-shadow 0.2s;
    }
    .landing-scan-path-btn--primary {
        background: #004ac6;
        color: #fff;
        box-shadow: 0 8px 24px rgba(0, 74, 198, 0.25);
    }
    .landing-scan-path-btn--primary:hover {
        opacity: 0.92;
    }
</style>
