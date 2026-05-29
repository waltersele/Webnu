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
<meta name="theme-color" content="#004ac6">
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    // Webnu brand manual
                    "primary": "#003594",
                    "primary-container": "#004AC6",
                    "primary-fixed": "#dbe1ff",
                    "primary-fixed-dim": "#b4c5ff",
                    "on-primary": "#ffffff",
                    "on-primary-container": "#ffffff",
                    "secondary": "#a73a00",
                    "secondary-container": "#fd651e",
                    "on-secondary": "#ffffff",
                    "on-secondary-container": "#ffffff",
                    "tertiary": "#363d4f",
                    "tertiary-container": "#4d5467",
                    "text-main": "#141B2B",
                    "text-muted": "#64748B",
                    "on-surface": "#141B2B",
                    "on-surface-variant": "#434654",
                    "surface": "#f9f9ff",
                    "background": "#f9f9ff",
                    "surface-bright": "#f9f9ff",
                    "surface-card": "#ffffff",
                    "surface-alt": "#f9fafb",
                    "surface-container-lowest": "#ffffff",
                    "surface-container-low": "#f3f3f9",
                    "surface-container": "#ededf3",
                    "surface-container-high": "#e7e8ee",
                    "surface-container-highest": "#e2e2e8",
                    "outline": "#737685",
                    "outline-variant": "#c3c6d6",
                    "border-subtle": "#e5e7eb",
                    "success-green": "#10B981",
                    "error": "#ba1a1a",
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
                    "section-gap": "48px",
                },
                fontFamily: {
                    display: ["'Plus Jakarta Sans'", "system-ui", "sans-serif"],
                    headline: ["'Plus Jakarta Sans'", "system-ui", "sans-serif"],
                    body: ["Inter", "system-ui", "sans-serif"],
                },
                fontSize: {
                    "headline-xl": ["clamp(2rem,5vw,3rem)", { lineHeight: "1.12", letterSpacing: "-0.02em", fontWeight: "800" }],
                    "headline-lg": ["2rem", { lineHeight: "1.2", letterSpacing: "-0.01em", fontWeight: "700" }],
                    "headline-md": ["1.5rem", { lineHeight: "1.3", fontWeight: "700" }],
                    "headline-sm": ["1.25rem", { lineHeight: "1.4", fontWeight: "700" }],
                    "body-lg": ["1.125rem", { lineHeight: "1.65", fontWeight: "400" }],
                    "body-md": ["1rem", { lineHeight: "1.5", fontWeight: "400" }],
                    "label-md": ["0.875rem", { lineHeight: "1.4", fontWeight: "500" }],
                    "label-sm": ["0.75rem", { lineHeight: "1.3", fontWeight: "600" }],
                },
            },
        },
    };
</script>
<style>
    .wn-splash {
        position: fixed;
        inset: 0;
        z-index: 2147483000;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #003594 0%, #004AC6 55%, #38bdf8 100%);
        transition: opacity 380ms ease, transform 380ms ease;
        opacity: 1;
        transform: translateY(0);
        pointer-events: none;
    }
    .wn-splash__inner {
        display: grid;
        place-items: center;
        padding: 24px;
        width: min(92vw, 420px);
    }
    .wn-splash__logo {
        width: min(70vw, 260px);
        height: auto;
        animation: wn-splash-float 1.5s ease-in-out infinite;
        filter: drop-shadow(0 16px 44px rgba(0, 0, 0, 0.22));
    }
    @keyframes wn-splash-float {
        0%, 100% { transform: translateY(0) scale(1); opacity: 1; }
        50% { transform: translateY(-8px) scale(1.01); opacity: 0.97; }
    }
    .wn-splash.is-hiding {
        opacity: 0;
        transform: translateY(8px);
    }
    @media (prefers-reduced-motion: reduce) {
        .wn-splash { transition: none; }
        .wn-splash__logo { animation: none; }
    }

    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        display: inline-block;
        vertical-align: middle;
    }
    body {
        font-family: 'Inter', system-ui, sans-serif;
        background-color: #f9f9ff;
        color: #141B2B;
        -webkit-font-smoothing: antialiased;
    }
    .font-headline,
    .font-display {
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        letter-spacing: -0.02em;
    }
    h1, h2, h3, h4, h5 {
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    }
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .step-active { background-color: #2563eb; }
    .step-inactive { background-color: #e5e7eb; }
    .faq-content { max-height: 0; overflow: hidden; transition: max-height 0.35s ease-out; }
    .faq-item.faq-open .faq-content { max-height: 520px; }
    .faq-item.faq-open .faq-icon { transform: rotate(180deg); }

    /* Hero — texto rotativo en azul.
       business: typewriter (escribe/borra letra a letra, con cursor).
       feature : slide-up lento con fade. Loop infinito y continuo. */
    .hero-cycle {
        position: relative;
        display: inline-block;
        line-height: 1.1;
        color: #004ac6;
        font-weight: inherit;
        vertical-align: baseline;
    }
    /* Measure invisible — mantiene baseline correcto y reserva el ancho/alto.
       Al ser inline-block visible-hidden, sigue contando para baseline alignment. */
    .hero-cycle__measure {
        display: inline-block;
        visibility: hidden;
        white-space: nowrap;
        pointer-events: none;
    }

    /* ---------- Typewriter (business) ---------- */
    /* Ancho reservado por measure; el texto vive en una capa absoluta encima. */
    .hero-cycle--typewriter .hero-cycle__layer {
        position: absolute;
        inset: 0;
        white-space: nowrap;
        display: inline-flex;
        align-items: baseline;
    }
    .hero-cycle--typewriter .hero-cycle__text { display: inline; }
    .hero-cycle--typewriter .hero-cycle__cursor {
        display: inline-block;
        width: 2px;
        height: 0.92em;
        margin-left: 3px;
        vertical-align: -0.08em;
        background: currentColor;
        border-radius: 1px;
        animation: hero-cycle-blink 0.9s steps(2, end) infinite;
    }
    @keyframes hero-cycle-blink {
        0%, 50% { opacity: 1; }
        50.01%, 100% { opacity: 0; }
    }

    /* En móvil, la palabra rotativa ocupa su propia línea — evita
       que el texto que sigue ("comienza aquí") se mueva o quede colgando. */
    @media (max-width: 767px) {
        .hero-cycle--typewriter { display: block; }
    }

    /* Línea «La plataforma que…» + frase azul en slide */
    .hero-platform-line {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 0.35em;
        line-height: 1.35;
    }
    .hero-platform-line__lead {
        flex: 0 0 auto;
    }

    /* ---------- Slide-up (feature) ---------- */
    .hero-cycle--slide {
        display: inline-grid;
        vertical-align: baseline;
        line-height: inherit;
        font-size: inherit;
        font-weight: 600;
        color: #004ac6;
    }
    .hero-cycle--slide .hero-cycle__measure {
        grid-area: 1 / 1;
        min-height: 1em;
    }
    .hero-cycle--slide .hero-cycle__viewport {
        grid-area: 1 / 1;
        position: relative;
        overflow: hidden;
        min-height: 1em;
        line-height: inherit;
    }
    .hero-cycle--slide .hero-cycle__item {
        position: absolute;
        left: 0;
        top: 0;
        white-space: nowrap;
        transform: translateY(0);
        opacity: 1;
        will-change: transform, opacity;
        transition: transform 1.1s cubic-bezier(0.22, 0.61, 0.36, 1),
                    opacity 1.0s ease;
    }
    .hero-cycle--slide .hero-cycle__item.is-entering {
        transform: translateY(100%);
        opacity: 0.35;
    }
    .hero-cycle--slide .hero-cycle__item.is-active:not(.is-leaving) {
        transform: translateY(0);
        opacity: 1;
    }
    .hero-cycle--slide .hero-cycle__item.is-leaving {
        transform: translateY(-100%);
        opacity: 0.35;
    }
    @media (max-width: 767px) {
        .hero-platform-line {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.15em;
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .hero-cycle--slide .hero-cycle__item,
        .hero-cycle--typewriter .hero-cycle__cursor { transition: none; animation: none; }
    }

    /* Hero — mockup móvil (proporción real) + chips en órbita alrededor */
    .hero-phone-stage {
        position: relative;
        width: fit-content;
        max-width: 100%;
        margin: 0 auto;
        padding: 1.5rem clamp(3.25rem, 9vw, 4.5rem);
        box-sizing: border-box;
    }
    .hero-phone {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 252px;
        margin: 0 auto;
        aspect-ratio: 9 / 19.5;
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border-radius: 36px;
        border: 9px solid #0f172a;
        box-shadow: 0 32px 60px rgba(15, 23, 42, 0.22), 0 8px 20px rgba(15, 23, 42, 0.12);
        overflow: hidden;
    }
    .hero-phone__notch {
        position: absolute;
        top: 8px;
        left: 50%;
        transform: translateX(-50%);
        width: 92px;
        height: 22px;
        background: #0f172a;
        border-radius: 0 0 14px 14px;
        z-index: 6;
    }
    .hero-phone__status {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 18px 4px;
        font-size: 0.75rem;
        font-weight: 700;
        color: #0f172a;
        position: relative;
        z-index: 2;
    }
    .hero-phone__status-icons {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .hero-phone__status-icons .material-symbols-outlined { font-size: 14px; }
    .hero-phone__hero {
        position: relative;
        margin: 0;
        flex: 0 0 38%;
        min-height: 0;
        overflow: hidden;
    }
    .hero-phone__hero img {
        width: 100%; height: 100%;
        object-fit: cover;
        display: block;
    }
    .hero-phone__hero figcaption {
        position: absolute;
        left: 16px;
        right: 16px;
        bottom: 14px;
        color: #fff;
        display: flex;
        flex-direction: column;
        gap: 2px;
        text-shadow: 0 4px 18px rgba(0, 0, 0, 0.45);
    }
    .hero-phone__hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(15, 23, 42, 0) 50%, rgba(15, 23, 42, 0.55) 100%);
        pointer-events: none;
    }
    .hero-phone__hero-name {
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        font-size: 1.25rem;
        font-weight: 800;
        letter-spacing: -0.01em;
    }
    .hero-phone__hero-tag {
        font-size: 0.625rem;
        font-weight: 700;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: #c4d5ff;
    }
    .hero-phone__body {
        flex: 1 1 auto;
        min-height: 0;
        padding: 10px 12px 14px;
        background: #f7f8fb;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .hero-phone__section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 4px 4px 10px;
    }
    .hero-phone__section-title {
        font-size: 0.625rem;
        font-weight: 800;
        letter-spacing: 0.16em;
        color: #64748b;
        text-transform: uppercase;
    }
    .hero-phone__lang-chip {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        padding: 3px 8px;
        font-size: 0.625rem;
        font-weight: 700;
        color: #475569;
    }
    .hero-phone__lang-chip .material-symbols-outlined {
        font-size: 13px;
        color: #004ac6;
    }
    .hero-phone__item {
        display: flex;
        gap: 8px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 8px;
        margin-bottom: 8px;
        flex-shrink: 0;
    }
    .hero-phone__item:last-child { margin-bottom: 0; }
    .hero-phone__item-media {
        position: relative;
        flex: 0 0 52px;
        width: 52px; height: 52px;
        border-radius: 12px;
        overflow: hidden;
        background: linear-gradient(135deg, #e6efff 0%, #c5d6ff 100%);
    }
    .hero-phone__item-media img {
        width: 100%; height: 100%;
        object-fit: cover;
        display: block;
    }
    .hero-phone__item-media--placeholder {
        background: linear-gradient(135deg, #e5e7eb 0%, #cbd5e1 100%);
    }
    .hero-phone__item-play {
        position: absolute;
        inset: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(15, 23, 42, 0.35);
        color: #fff;
    }
    .hero-phone__item-play .material-symbols-outlined { font-size: 22px; }
    .hero-phone__item-body { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; gap: 4px; }
    .hero-phone__item-row {
        display: flex; align-items: baseline; justify-content: space-between;
        gap: 8px;
    }
    .hero-phone__item-name {
        margin: 0;
        font-size: 0.8125rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }
    .hero-phone__item-desc {
        margin: 0;
        font-size: 0.6875rem;
        line-height: 1.35;
        color: #64748b;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .hero-phone__item-price {
        font-size: 0.8125rem;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
    }

    /* Hero — chips individuales pegados al teléfono */
    .hero-chip {
        position: absolute;
        z-index: 4;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        font-weight: 700;
        font-size: 0.75rem;
        line-height: 1.2;
        white-space: nowrap;
        border-radius: 999px;
        padding: 8px 14px 8px 8px;
        background: #0f172a;
        color: #fff;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.22);
        opacity: 0;
        transform: translateX(-8px);
        transition: opacity 0.45s ease, transform 0.45s ease;
        pointer-events: none;
    }
    .hero-chip.is-visible {
        opacity: 1;
        transform: translateX(0);
        pointer-events: auto;
    }
    .hero-chip.is-hiding {
        opacity: 0;
        transform: translateX(-8px);
    }
    .hero-chip--light {
        background: #fff;
        color: #0f172a;
        border-radius: 14px;
        padding: 10px 14px;
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.14);
        transform: translateX(8px);
    }
    .hero-chip--light.is-hiding {
        transform: translateX(8px);
    }
    .hero-chip--light.is-visible {
        transform: translateX(0);
    }
    .hero-chip__icon {
        display: inline-flex; align-items: center; justify-content: center;
        width: 28px; height: 28px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.18);
        color: #fff;
        flex-shrink: 0;
    }
    .hero-chip--light .hero-chip__icon {
        background: rgba(0, 74, 198, 0.1);
        color: #004ac6;
    }
    .hero-chip__icon .material-symbols-outlined { font-size: 16px; }
    .hero-chip__body {
        display: flex; flex-direction: column; line-height: 1;
    }
    .hero-chip__label {
        font-size: 0.5625rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #64748b;
    }
    .hero-chip__value {
        margin-top: 3px;
        font-size: 1rem;
        font-weight: 800;
        color: #004ac6;
    }
    /* Chip A — izquierda, ~28% desde arriba, solapando borde izq. del teléfono */
    .hero-chip--a {
        left: calc(50% - 126px - 150px);
        top: 28%;
    }
    /* Chip B — izquierda, ~58% desde arriba */
    .hero-chip--b {
        left: calc(50% - 126px - 148px);
        top: 58%;
    }
    /* Chip C — derecha, ~36% desde arriba */
    .hero-chip--c {
        right: calc(50% - 126px - 148px);
        top: 36%;
    }
    /* Animación de float suave en chip visible */
    @keyframes hero-chip-float {
        0%, 100% { transform: translateX(0) translateY(0); }
        50% { transform: translateX(0) translateY(-4px); }
    }
    .hero-chip.is-visible { animation: hero-chip-float 4.5s ease-in-out infinite; }
    .hero-chip--light.is-visible { animation: hero-chip-float 4.5s ease-in-out infinite; }

    @media (max-width: 1023px) {
        .hero-phone-stage {
            padding: 1.25rem clamp(2.75rem, 8vw, 3.75rem);
        }
        .hero-phone { max-width: 236px; }
        .hero-chip--a { left: calc(50% - 118px - 140px); }
        .hero-chip--b { left: calc(50% - 118px - 140px); }
        .hero-chip--c { right: calc(50% - 118px - 140px); }
    }
    /* hero-chips-row: wrapper transparente en desktop */
    .hero-chips-row {
        position: absolute;
        inset: 0;
        pointer-events: none;
    }
    .hero-chips-row .hero-chip {
        pointer-events: none;
    }
    .hero-chips-row .hero-chip.is-visible {
        pointer-events: auto;
    }
    @media (max-width: 767px) {
        /* En móvil los chips van en fila debajo del teléfono */
        .hero-chips-row {
            position: static;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
            order: 2;
        }
        .hero-chip--a, .hero-chip--c {
            position: static !important;
            display: inline-flex !important;
            transform: none !important;
            opacity: 1 !important;
            animation: none !important;
            pointer-events: auto;
            transition: none !important;
        }
        .hero-chip--b { display: none !important; }
        .hero-phone-stage {
            padding: 1rem 1.5rem 0.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }
    }
    @media (max-width: 480px) {
        .hero-phone {
            max-width: 210px;
            border-width: 8px;
            border-radius: 30px;
        }
        .hero-phone__notch { width: 76px; height: 18px; top: 6px; }
        .hero-chip--a, .hero-chip--c { font-size: 0.6875rem; padding: 6px 10px 6px 6px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .hero-chip { transition: none; animation: none !important; }
        .hero-chip { opacity: 1 !important; transform: none !important; }
    }

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

    /* Reels — bloque premium centrado con vídeo y tarjetas glass */
    .landing-reels-showcase {
        overflow: hidden;
    }
    .landing-reels-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(234, 88, 12, 0.14);
        color: #c2410c;
        border: 1px solid rgba(234, 88, 12, 0.22);
        font-size: 0.6875rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }
    .landing-reels-headline__accent {
        display: inline-flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0;
    }
    /* Efecto ola en cada letra — el gradiente va en cada span para que background-clip funcione */
    .kinetic-letter {
        display: inline-block;
        background: linear-gradient(90deg, #004ac6 0%, #c2410c 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        animation: kinetic-wave 2.4s ease-in-out infinite;
        animation-delay: calc(var(--ki, 0) * 0.07s);
        will-change: transform;
    }
    @keyframes kinetic-wave {
        0%,  60%, 100% { transform: translateY(0); }
        30%             { transform: translateY(-0.22em); }
    }
    @media (prefers-reduced-motion: reduce) {
        .kinetic-letter { animation: none; }
    }
    .landing-reels-stage {
        position: relative;
        max-width: 56rem;
        margin: 0 auto;
        padding: 0 0.75rem;
    }
    .landing-reels-browser {
        border-radius: 1.25rem;
        overflow: hidden;
        background: #fff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 28px 60px rgba(15, 23, 42, 0.14);
    }
    .landing-reels-browser__chrome {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
    }
    .landing-reels-browser__dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #cbd5e1;
    }
    .landing-reels-browser__url {
        margin-left: 10px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 600;
        flex: 1;
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .landing-reels-browser__body {
        position: relative;
        aspect-ratio: 16 / 10;
        background: #0b1220;
        overflow: hidden;
    }
    .landing-reels-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scale(1.02);
        animation: landingReelsKenBurns 22s ease-in-out infinite alternate;
        display: block;
    }
    @keyframes landingReelsKenBurns {
        0% { transform: scale(1.02) translateY(0); }
        100% { transform: scale(1.08) translateY(-1.5%); }
    }
    .landing-reels-play {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255, 255, 255, 0.9);
        text-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
        pointer-events: none;
    }
    .landing-reels-play .material-symbols-outlined {
        font-size: 44px;
        padding: 14px;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.35);
        border: 1px solid rgba(255, 255, 255, 0.22);
        backdrop-filter: blur(10px);
    }
    .landing-reels-float {
        position: absolute;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 14px 14px;
        width: min(240px, 44vw);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.55);
        background: rgba(255, 255, 255, 0.78);
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.12);
        backdrop-filter: blur(12px);
    }
    .landing-reels-float__icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 74, 198, 0.12);
        color: #004ac6;
        flex-shrink: 0;
    }
    .landing-reels-float--fast .landing-reels-float__icon {
        background: rgba(234, 88, 12, 0.14);
        color: #c2410c;
    }
    .landing-reels-float__title {
        margin: 0;
        font-size: 0.8125rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .landing-reels-float__desc {
        margin: 4px 0 0;
        font-size: 0.6875rem;
        line-height: 1.35;
        color: #475569;
    }
    .landing-reels-float__link {
        color: #004ac6;
        font-weight: 700;
        text-decoration: none;
    }
    .landing-reels-float__link:hover {
        text-decoration: underline;
    }
    .landing-reels-float--sell { left: -18px; top: 24%; }
    .landing-reels-float--fast { left: -18px; bottom: 18%; }
    .landing-reels-float--style { right: -18px; top: 32%; }
    @media (max-width: 900px) {
        .landing-reels-float--sell,
        .landing-reels-float--fast,
        .landing-reels-float--style {
            position: static;
            width: 100%;
        }
        .landing-reels-stage {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .landing-reels-video { animation: none; }
    }

    /* Estudio visual — layout del boceto (picker | móvil | copy) */
    .landing-templates-showcase {
        display: grid;
        grid-template-columns: 1fr minmax(0, 380px);
        align-items: start;
        gap: 2rem 3rem;
        max-width: 50rem;
        margin: 0 auto;
    }
    .landing-template-picker {
        position: relative;
        z-index: 2;
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .landing-template-picker__title {
        margin: 0 0 4px;
        font-size: 0.625rem;
        font-weight: 800;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #64748b;
    }
    .landing-template-picker__card {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        width: 100%;
        padding: 10px 12px;
        border: 2px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.1);
        cursor: pointer;
        text-align: left;
        font-family: inherit;
        opacity: 0.55;
        transition: opacity 0.4s ease, border-color 0.25s ease, box-shadow 0.25s ease, transform 0.25s ease;
    }
    .landing-template-picker__card:hover {
        opacity: 0.85;
        border-color: rgba(0, 74, 198, 0.25);
    }
    .landing-template-picker__card.is-active {
        opacity: 1;
        border-color: #004ac6;
        box-shadow: 0 0 0 1px #004ac6, 0 12px 32px rgba(0, 74, 198, 0.14);
    }
    .landing-template-picker__thumb {
        flex: 0 0 52px;
        width: 52px;
        height: 52px;
        border-radius: 10px;
        overflow: hidden;
        background: #f1f3ff;
    }
    .landing-template-picker__thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .landing-template-picker__body {
        flex: 1 1 auto;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .landing-template-picker__category {
        font-size: 0.625rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        color: #004ac6;
    }
    .landing-template-picker__name {
        font-size: 0.8125rem;
        font-weight: 700;
        color: #141b2b;
        line-height: 1.2;
    }
    .landing-template-picker__desc {
        font-size: 0.6875rem;
        line-height: 1.35;
        color: #64748b;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .landing-template-picker__tags {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 4px;
    }
    .landing-template-picker__tag {
        font-size: 0.625rem;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 999px;
        background: #f1f3ff;
        color: #475569;
    }
    .landing-template-phone-col {
        display: flex;
        justify-content: center;
    }
    .landing-customize-wrap {
        position: relative;
        z-index: 2;
        margin: 0 auto;
        width: min(100%, 280px);
        display: flex;
        flex-direction: column;
        gap: 1rem;
        align-items: center;
    }
    .landing-template-picker__cta-row {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
    }
    .landing-template-picker__cta-bullet {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 500;
    }
    .landing-template-picker__cta-bullet .material-symbols-outlined {
        font-size: 16px;
        color: #004ac6;
    }
    .landing-template-picker__cta-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 4px;
        padding: 10px 18px;
        border-radius: 10px;
        background: #004ac6;
        color: #fff;
        font-size: 0.8125rem;
        font-weight: 700;
        text-decoration: none;
        transition: opacity 0.2s;
    }
    .landing-template-picker__cta-btn:hover { opacity: 0.88; }
    .landing-template-picker__cta-btn .material-symbols-outlined { font-size: 18px; }

    /* Controles flotantes sobre la pantalla del teléfono */
    .tpl-phone__controls-overlay {
        margin: auto 10px 10px;
        background: rgba(255,255,255,0.92);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 12px;
        padding: 10px 12px;
        display: flex;
        flex-direction: column;
        gap: 7px;
        box-shadow: 0 4px 16px rgba(15,23,42,0.10);
    }
    .tpl-phone__controls-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .tpl-phone__controls-label {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.625rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        flex-shrink: 0;
    }
    .tpl-phone__controls-label .material-symbols-outlined { font-size: 13px; }
    .customize-hint-text {
        font-size: 0.5625rem;
        color: #94a3b8;
        text-align: right;
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        transition: opacity 0.35s ease;
    }
    .customize-hint-text.is-fading { opacity: 0; }

    @media (max-width: 767px) {
        .landing-templates-showcase {
            grid-template-columns: 1fr;
            max-width: 34rem;
        }
    }

    /* Teléfono real sección Plantillas */
    .tpl-phone {
        --cust-primary: #004ac6;
        --cust-bg: #ffffff;
        --cust-surface: #f1f3ff;
        --cust-text: #141b2b;
        --cust-muted: #4b5563;
        --cust-thumb: linear-gradient(135deg, #dbe1ff 0%, #93c5fd 100%);
        position: relative;
        width: 100%;
        max-width: 280px;
        aspect-ratio: 9 / 19.5;
        border: 10px solid #0f172a;
        border-radius: 42px;
        background: var(--cust-bg);
        box-shadow:
            0 0 0 1px rgba(255,255,255,0.08) inset,
            0 32px 56px rgba(15, 23, 42, 0.22);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: background 0.6s ease;
    }
    .tpl-phone.is-switching .tpl-phone__dish-name,
    .tpl-phone.is-switching .tpl-phone__dish-price,
    .tpl-phone.is-switching .tpl-phone__dish-desc,
    .tpl-phone.is-switching .tpl-phone__business {
        opacity: 0;
        transform: translateY(6px);
    }
    .tpl-phone__notch {
        position: absolute;
        top: 8px;
        left: 50%;
        transform: translateX(-50%);
        width: 90px;
        height: 22px;
        background: #0f172a;
        border-radius: 0 0 16px 16px;
        z-index: 2;
    }
    .tpl-phone__status {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 36px 14px 4px;
        font-size: 0.625rem;
        font-weight: 700;
        color: var(--cust-text);
        flex-shrink: 0;
    }
    .tpl-phone__status-icons {
        display: flex;
        gap: 3px;
        align-items: center;
        color: var(--cust-text);
    }
    .tpl-phone__screen {
        flex: 1;
        background: var(--cust-bg);
        display: flex;
        flex-direction: column;
        transition: background 0.6s ease;
        overflow: hidden;
    }
    .tpl-phone__app-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-bottom: 1px solid rgba(0,0,0,0.08);
        flex-shrink: 0;
    }
    .tpl-phone__business {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--cust-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: color 0.5s ease, opacity 0.35s ease, transform 0.35s ease;
    }
    .tpl-phone__badge {
        flex-shrink: 0;
        font-size: 0.5625rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 2px 7px;
        border-radius: 999px;
        background: var(--cust-primary);
        color: #fff;
        transition: background 0.6s ease;
    }
    .tpl-phone__section {
        padding: 6px 14px 3px;
        font-size: 0.5625rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--cust-muted);
        transition: color 0.5s ease;
        flex-shrink: 0;
    }
    .tpl-phone__dish {
        display: grid;
        grid-template-columns: 60px 1fr;
        gap: 10px;
        margin: 6px 14px;
        padding-bottom: 8px;
        border-bottom: 1px solid rgba(0,0,0,0.06);
        align-items: start;
    }
    .tpl-phone__dish--2 { opacity: 0.65; }
    .tpl-phone__dish-thumb {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        background: var(--cust-thumb-img, var(--cust-thumb, linear-gradient(135deg, #dbe1ff 0%, #93c5fd 100%)));
        background-size: cover;
        background-position: center;
        flex-shrink: 0;
        transition: background 0.6s ease;
    }
    .tpl-phone__dish-thumb--alt {
        background: linear-gradient(135deg, #fde8d8 0%, #fbbf6c 100%);
    }
    .tpl-phone__dish-info { min-width: 0; }
    .tpl-phone__dish-row {
        display: flex;
        justify-content: space-between;
        gap: 4px;
        align-items: flex-start;
    }
    .tpl-phone__dish-name {
        font-size: 0.6875rem;
        font-weight: 600;
        color: var(--cust-text);
        transition: color 0.5s ease, opacity 0.35s ease, transform 0.35s ease;
        line-height: 1.3;
    }
    .tpl-phone__dish-name--muted { opacity: 0.6; }
    .tpl-phone__dish-price {
        font-size: 0.6875rem;
        font-weight: 700;
        color: var(--cust-primary);
        flex-shrink: 0;
        transition: color 0.6s ease, opacity 0.35s ease, transform 0.35s ease;
    }
    .tpl-phone__dish-desc {
        font-size: 0.5625rem;
        color: var(--cust-muted);
        margin-top: 2px;
        line-height: 1.4;
        transition: color 0.5s ease, opacity 0.35s ease, transform 0.35s ease;
    }

    /* Animación personalización carta */
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

    /* TVPik — stage centrado con cards flotantes */
    .landing-tv-stage {
        position: relative;
        max-width: 640px;
        margin: 0 auto 3rem;
        padding: 2rem 2.5rem; /* espacio para que las cards sobresalgan */
    }
    /* Cards flotantes de ventajas */
    .tv-feat-card {
        position: absolute;
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 14px;
        padding: 10px 14px;
        box-shadow: 0 8px 28px rgba(15, 23, 42, 0.13);
        z-index: 10;
        animation: hero-chip-float 4.5s ease-in-out infinite;
        white-space: nowrap;
    }
    .tv-feat-card--sync {
        top: 0;
        left: 0;
        animation-delay: 0s;
    }
    .tv-feat-card--price {
        bottom: 3.5rem; /* encima de los controles */
        right: 0;
        animation-delay: 1.2s;
    }
    .tv-feat-card--mobile {
        top: 38%;
        right: 0;
        animation-delay: 2.4s;
    }
    .tv-feat-card__icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .tv-feat-card__icon--blue {
        background: rgba(0, 74, 198, 0.12);
        color: #004ac6;
    }
    .tv-feat-card__icon--orange {
        background: rgba(234, 88, 12, 0.12);
        color: #ea580c;
    }
    .tv-feat-card__icon--dark {
        background: rgba(15, 23, 42, 0.08);
        color: #0f172a;
    }
    .tv-feat-card__icon .material-symbols-outlined { font-size: 18px; }
    .tv-feat-card__text {
        display: flex;
        flex-direction: column;
        gap: 1px;
    }
    .tv-feat-card__label {
        font-size: 0.5625rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #94a3b8;
        line-height: 1;
    }
    .tv-feat-card__value {
        font-size: 0.8125rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }
    .tv-feat-card__price-row {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .tv-feat-card__price-old {
        font-size: 0.75rem;
        font-weight: 600;
        color: #94a3b8;
        text-decoration: line-through;
    }
    .tv-feat-card__arrow {
        font-size: 16px !important;
        color: #ea580c;
    }
    .tv-feat-card__price-new {
        font-size: 0.875rem;
        font-weight: 800;
        color: #ea580c;
    }
    /* 4 beneficios en columnas */
    .landing-tvpik-benefits {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem 2rem;
        max-width: 72rem;
        margin: 0 auto;
        padding-top: 1rem;
        border-top: 1px solid rgba(15, 23, 42, 0.08);
    }
    .landing-tvpik-benefit {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .landing-tvpik-benefit__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: rgba(234, 88, 12, 0.12);
        color: #ea580c;
        flex-shrink: 0;
    }
    .landing-tvpik-benefit__icon .material-symbols-outlined { font-size: 20px; }
    .landing-tvpik-benefit__title {
        font-size: 0.9375rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        line-height: 1.3;
    }
    .landing-tvpik-benefit__desc {
        font-size: 0.8125rem;
        color: #64748b;
        line-height: 1.55;
        margin: 0;
    }
    @media (max-width: 1023px) {
        .landing-tvpik-benefits {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 640px) {
        .landing-tv-stage {
            padding: 1.5rem 1rem;
        }
        .tv-feat-card--mobile { display: none; }
        .tv-feat-card--sync { top: -8px; left: -8px; }
        .tv-feat-card--price { bottom: 3rem; right: -8px; }
        .landing-tvpik-benefits {
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .tv-feat-card { animation: none; }
        .landing-tv-show__video-inset,
        .landing-tv-show__video-template::after {
            animation: none !important;
        }
    }

    /* TVPik unificado — slider plantillas TV */
    .landing-tv-show {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        align-items: center;
    }
    .landing-tv-show__frame {
        width: 100%;
        max-width: 540px;
        margin: 0 auto;
    }
    .landing-tv-show__bezel {
        padding: 10px;
        background: linear-gradient(180deg, #2a2a2a 0%, #111 100%);
        border-radius: 18px;
        box-shadow: 0 24px 56px rgba(0, 0, 0, 0.42), 0 8px 24px rgba(0, 0, 0, 0.32);
    }
    .landing-tv-show__screen {
        position: relative;
        aspect-ratio: 16 / 10;
        border-radius: 10px;
        overflow: hidden;
        background: #0a0a0a;
        color: #fff;
    }
    .landing-tv-show__screen::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(115deg, transparent 40%, rgba(255, 255, 255, 0.06) 50%, transparent 60%);
        animation: landing-tv-show-shine 4.5s ease-in-out infinite;
        pointer-events: none;
        z-index: 4;
    }
    @keyframes landing-tv-show-shine {
        0%, 100% { transform: translateX(-110%); }
        50% { transform: translateX(110%); }
    }
    .landing-tv-show__stand {
        width: 60%;
        height: 8px;
        margin: 0 auto;
        background: linear-gradient(180deg, #2a2a2a 0%, #1a1a1a 100%);
        border-radius: 0 0 4px 4px;
    }
    .landing-tv-show__brand {
        position: absolute;
        top: 10px;
        left: 14px;
        z-index: 5;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.55);
    }
    .landing-tv-show__live {
        position: absolute;
        top: 10px;
        right: 14px;
        z-index: 5;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.55rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        color: #fff;
        background: rgba(220, 38, 38, 0.78);
        padding: 2px 8px;
        border-radius: 999px;
    }
    .landing-tv-show__live span {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #fff;
        animation: landing-tv-show-live 1.2s ease-in-out infinite;
    }
    @keyframes landing-tv-show-live {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    .landing-tv-show__updated {
        position: absolute;
        bottom: 8px;
        right: 12px;
        z-index: 5;
        font-size: 0.5rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: rgba(255, 255, 255, 0.55);
    }
    .landing-tv-show__stage {
        position: absolute;
        inset: 0;
        z-index: 1;
    }
    .landing-tv-show__slide {
        position: absolute;
        inset: 0;
        display: flex;
        opacity: 0;
        transform: scale(1.02);
        transition: opacity 0.55s ease, transform 0.55s ease;
        pointer-events: none;
    }
    .landing-tv-show__slide.is-active {
        opacity: 1;
        transform: scale(1);
        pointer-events: auto;
        z-index: 2;
    }
    .landing-tv-show__photo {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .landing-tv-show__overlay {
        position: absolute;
        inset: 0;
        z-index: 3;
        padding: 18px 22px;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        gap: 6px;
    }
    .landing-tv-show__overlay--hero,
    .landing-tv-show__overlay--video {
        background: linear-gradient(0deg, rgba(15, 23, 42, 0.92) 0%, rgba(15, 23, 42, 0.4) 50%, transparent 100%);
    }
    .landing-tv-show__tag {
        align-self: flex-start;
        font-size: 0.6rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 3px 10px;
        border-radius: 999px;
        background: rgba(234, 88, 12, 0.85);
        color: #fff;
    }
    .landing-tv-show__title {
        margin: 0;
        font-family: 'Playfair Display', 'Georgia', serif;
        font-size: clamp(1.05rem, 2.4vw, 1.6rem);
        font-weight: 700;
        line-height: 1.15;
        color: #fff;
    }
    .landing-tv-show__title--sm {
        font-size: clamp(0.95rem, 2vw, 1.25rem);
    }
    .landing-tv-show__sub {
        margin: 0;
        font-size: 0.78rem;
        color: rgba(255, 255, 255, 0.78);
    }
    .landing-tv-show__price {
        align-self: flex-start;
        margin-top: 4px;
        padding: 4px 12px;
        background: #ea580c;
        color: #fff;
        font-weight: 800;
        font-size: 0.95rem;
        border-radius: 999px;
        letter-spacing: 0.02em;
    }
    .landing-tv-show__price--xl {
        font-size: 1.2rem;
        padding: 6px 16px;
    }

    /* Tapas */
    .landing-tv-show__slide--tapas {
        background: linear-gradient(160deg, #1f1206 0%, #2a1a0a 100%);
    }
    .landing-tv-show__tapas {
        position: relative;
        z-index: 3;
        padding: 16px 18px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        width: 100%;
    }
    .landing-tv-show__tapas-head {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .landing-tv-show__tapas-grid {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    .landing-tv-show__tapa {
        display: flex;
        flex-direction: column;
        gap: 4px;
        align-items: center;
        text-align: center;
    }
    .landing-tv-show__tapa-photo {
        width: 100%;
        aspect-ratio: 1 / 1;
        border-radius: 10px;
        background: #2a1a0a center/cover no-repeat;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
    }
    .landing-tv-show__tapa-name {
        margin: 0;
        font-size: 0.7rem;
        font-weight: 600;
        color: #fff;
        line-height: 1.15;
    }
    .landing-tv-show__tapa-price {
        font-size: 0.72rem;
        font-weight: 800;
        color: #fcd34d;
    }

    /* Daily — aspecto premium oscuro (no beige plano) */
    .landing-tv-show__slide--daily {
        background: #0f172a;
        color: #f8fafc;
    }
    .landing-tv-show__slide--daily::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(0, 74, 198, 0.35) 0%, transparent 55%),
            url('/img/productos/cocktail-negroni.jpg') center/cover no-repeat;
        opacity: 0.45;
        z-index: 1;
    }
    .landing-tv-show__daily {
        position: relative;
        z-index: 3;
        padding: 18px 22px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 100%;
        justify-content: center;
        text-align: left;
    }
    .landing-tv-show__daily-head {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .landing-tv-show__slide--daily .landing-tv-show__title { color: #fff; }
    .landing-tv-show__slide--daily .landing-tv-show__tag { background: rgba(0, 116, 217, 0.9); }
    .landing-tv-show__slide--daily .landing-tv-show__price { background: #0074d9; }
    .landing-tv-show__slide--daily .landing-tv-show__daily-list { color: #e2e8f0; }
    .landing-tv-show__daily-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 4px;
        font-size: 0.82rem;
        color: #374151;
        font-weight: 500;
    }
    .landing-tv-show__daily-list li::before {
        content: '· ';
        color: #ea580c;
        font-weight: 800;
    }

    /* Video — plantilla TVPik + vídeo demo en el área de pantalla */
    .landing-tv-show__slide--video {
        background: #0f1419;
    }
    .landing-tv-show__video-template {
        position: absolute;
        inset: 0;
        z-index: 1;
        overflow: hidden;
    }
    .landing-tv-show__template-preview {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.92;
    }
    .landing-tv-show__video-inset {
        position: absolute;
        left: 15%;
        top: 13%;
        width: 70%;
        height: 66%;
        object-fit: cover;
        border-radius: 6px;
        z-index: 2;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.45);
        animation: landing-tv-ken-burns 14s ease-in-out infinite alternate;
    }
    @keyframes landing-tv-ken-burns {
        0%   { transform: scale(1) translate(0, 0); }
        100% { transform: scale(1.08) translate(-1%, -1%); }
    }
    .landing-tv-show__video-template::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(105deg, transparent 40%, rgba(255, 255, 255, 0.06) 50%, transparent 60%);
        z-index: 3;
        pointer-events: none;
        animation: landing-tv-shimmer 4s ease-in-out infinite;
    }
    @keyframes landing-tv-shimmer {
        0%, 100% { opacity: 0; transform: translateX(-30%); }
        50%      { opacity: 1; transform: translateX(30%); }
    }
    .landing-tv-show__play {
        position: absolute;
        z-index: 4;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.94);
        color: #1f2937;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45);
        animation: landing-tv-show-pulse 2.6s ease-in-out infinite;
    }
    .landing-tv-show__play .material-symbols-outlined {
        font-size: 32px;
        font-variation-settings: 'FILL' 1;
    }
    @keyframes landing-tv-show-pulse {
        0%, 100% { transform: translate(-50%, -50%) scale(1); }
        50% { transform: translate(-50%, -50%) scale(1.08); }
    }

    /* Menu */
    .landing-tv-show__slide--menu {
        background: linear-gradient(160deg, #f8fafc 0%, #e0e7ff 100%);
        color: #0f172a;
    }
    .landing-tv-show__menu {
        position: relative;
        z-index: 3;
        padding: 14px 18px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 100%;
    }
    .landing-tv-show__menu-head {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .landing-tv-show__slide--menu .landing-tv-show__title { color: #0f172a; }
    .landing-tv-show__menu-cols {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .landing-tv-show__menu-section {
        margin: 0 0 4px;
        font-size: 0.62rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #2563eb;
    }
    .landing-tv-show__menu-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .landing-tv-show__menu-list li {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        font-size: 0.7rem;
        line-height: 1.3;
        color: #374151;
        padding: 1px 0;
    }
    .landing-tv-show__menu-list strong {
        color: #2563eb;
        font-weight: 800;
        white-space: nowrap;
    }

    /* Slide dual — 2 pantallas (fast-food) */
    .landing-tv-show__slide--dual {
        background: #0a0a0a;
        display: block; /* override flex to allow grid child */
    }
    .landing-tv-show__dual {
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        height: 100%;
        width: 100%;
    }
    .landing-tv-show__dual-left {
        position: relative;
        overflow: hidden;
    }
    .landing-tv-show__dual-right {
        background: #0f172a;
        border-left: 2px solid rgba(255, 255, 255, 0.1);
        overflow: hidden;
        display: flex;
        align-items: flex-start;
    }
    .landing-tv-show__dual-right .landing-tv-show__menu {
        padding: 12px 14px;
        width: 100%;
    }
    .landing-tv-show__dual-right .landing-tv-show__menu-head {
        margin-bottom: 8px;
    }
    .landing-tv-show__menu-cols--dual {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .landing-tv-show__dual-right .landing-tv-show__menu-section {
        color: #ea580c;
        font-size: 0.55rem;
    }
    .landing-tv-show__dual-right .landing-tv-show__menu-list li {
        font-size: 0.62rem;
        color: #e2e8f0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        padding: 2px 0;
    }
    .landing-tv-show__dual-right .landing-tv-show__menu-list strong {
        color: #fcd34d;
    }

    /* Controles */
    .landing-tv-show__controls {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .landing-tv-show__nav {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 1px solid rgba(15, 23, 42, 0.12);
        background: #fff;
        color: #0f172a;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    }
    .landing-tv-show__nav:hover {
        background: #ea580c;
        color: #fff;
        border-color: transparent;
    }
    .landing-tv-show__dots {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        flex-wrap: wrap;
        justify-content: center;
    }
    .landing-tv-show__dot {
        background: rgba(15, 23, 42, 0.08);
        border: 0;
        color: #475569;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 999px;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }
    .landing-tv-show__dot:hover { background: rgba(234, 88, 12, 0.18); color: #ea580c; }
    .landing-tv-show__dot.is-active {
        background: #ea580c;
        color: #fff;
        transform: scale(1.05);
    }
    @media (prefers-reduced-motion: reduce) {
        .landing-tv-show__slide { transition: opacity 0.01s linear; transform: none; }
        .landing-tv-show__play,
        .landing-tv-show__live span,
        .landing-tv-show__screen::after { animation: none; }
    }
    @media (max-width: 640px) {
        .landing-tv-show__overlay { padding: 14px 16px; }
        .landing-tv-show__tapas { padding: 12px 14px; }
        .landing-tv-show__menu-cols { grid-template-columns: 1fr; }
        .landing-tv-show__dot { padding: 5px 9px; font-size: 0.6rem; }
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

    /* Slider compacto de funciones */
    .wn-feat-slider {
        display: flex;
        gap: 16px;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scroll-padding-left: 16px;
        padding: 4px 16px 24px;
        margin: 0 -16px;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .wn-feat-slider::-webkit-scrollbar { display: none; }
    .wn-feat-card {
        flex: 0 0 240px;
        scroll-snap-align: start;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 18px 16px;
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 8px;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .wn-feat-card:hover {
        transform: translateY(-2px);
        border-color: rgba(0, 53, 148, 0.25);
        box-shadow: 0 12px 32px rgba(20, 27, 43, 0.08);
    }
    .wn-feat-card__icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: #dbe1ff;
        color: #003594;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .wn-feat-card__title {
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        font-weight: 700;
        font-size: 0.9375rem;
        line-height: 1.25;
        color: #141B2B;
    }
    .wn-feat-card__desc {
        font-size: 0.78rem;
        line-height: 1.45;
        color: #64748B;
        flex: 1;
    }
    .wn-feat-card--highlight {
        background: linear-gradient(135deg, #003594 0%, #004AC6 100%);
        border-color: transparent;
        color: #ffffff;
    }
    .wn-feat-card--highlight .wn-feat-card__icon {
        background: rgba(255, 255, 255, 0.18);
        color: #ffffff;
    }
    .wn-feat-card--highlight .wn-feat-card__title { color: #ffffff; }
    .wn-feat-card--highlight .wn-feat-card__desc { color: rgba(255, 255, 255, 0.88); }
    .wn-feat-card__plan {
        position: absolute;
        top: 12px;
        right: 12px;
        font-size: 0.625rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 2px 8px;
        border-radius: 999px;
        background: #fd651e;
        color: #ffffff;
    }
    .wn-feat-card--highlight .wn-feat-card__plan {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
    }
    .wn-feat-slider__hint {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.75rem;
        color: #64748B;
        margin-top: 4px;
    }

    /* Slider wrap con flechas laterales y dots inferiores */
    .wn-feat-slider-wrap {
        position: relative;
    }
    .wn-feat-slider__arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 5;
        display: none;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        color: #003594;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.1);
        transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease, opacity 0.18s ease;
    }
    .wn-feat-slider__arrow:hover {
        background: #003594;
        color: #ffffff;
        transform: translateY(-50%) scale(1.05);
    }
    .wn-feat-slider__arrow:focus-visible {
        outline: 2px solid #003594;
        outline-offset: 2px;
    }
    .wn-feat-slider__arrow[aria-disabled="true"] { opacity: 0.45; cursor: default; }
    .wn-feat-slider__arrow--prev { left: -8px; }
    .wn-feat-slider__arrow--next { right: -8px; }
    .wn-feat-slider__arrow .material-symbols-outlined { font-size: 22px; }

    .wn-feat-slider.is-grabbing {
        cursor: grabbing;
        scroll-snap-type: none;
        user-select: none;
    }
    .wn-feat-slider.is-grabbing .wn-feat-card { pointer-events: none; }

    .wn-feat-slider__dots {
        display: none;
        justify-content: center;
        gap: 6px;
        margin-top: 4px;
        padding: 0 16px 4px;
    }
    .wn-feat-slider__dot {
        appearance: none;
        background: rgba(20, 27, 43, 0.18);
        border: 0;
        width: 8px;
        height: 8px;
        border-radius: 999px;
        padding: 0;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.2s ease, width 0.25s ease;
    }
    .wn-feat-slider__dot:hover { background: rgba(0, 53, 148, 0.45); }
    .wn-feat-slider__dot.is-active {
        background: #003594;
        width: 22px;
    }
    .wn-feat-slider__dot:focus-visible {
        outline: 2px solid #003594;
        outline-offset: 2px;
    }

    @media (min-width: 768px) {
        .wn-feat-slider { padding-left: 0; padding-right: 0; margin: 0; cursor: grab; }
        .wn-feat-slider:active { cursor: grabbing; }
        .wn-feat-card { flex: 0 0 260px; }
        .wn-feat-slider__arrow { display: inline-flex; }
        .wn-feat-slider__dots { display: flex; }
        .wn-feat-slider__arrow--prev { left: -20px; }
        .wn-feat-slider__arrow--next { right: -20px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .wn-feat-slider__arrow:hover { transform: translateY(-50%); }
        .wn-feat-slider__dot.is-active { transition: none; }
    }

    .wn-demos-scroll {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .wn-demos-scroll::-webkit-scrollbar { display: none; }

    /* Proceso 3 pasos — slider móvil y visuales sin iconos cuadrados */
    .wn-process-slider-wrap {
        position: relative;
    }
    .wn-process-steps {
        position: relative;
    }
    @media (max-width: 767.98px) {
        .wn-process-steps {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            padding: 0 0.25rem 0.5rem;
        }
        .wn-process-steps::-webkit-scrollbar { display: none; }
    }
    /* Proceso 3 pasos — ilustraciones animadas */
    .wn-process-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 1rem;
    }
    .wn-process-step__body {
        max-width: 18rem;
        width: 100%;
    }
    .proc-illus {
        height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        margin: 0 auto;
        max-width: 280px;
        overflow: visible;
    }
    @media (min-width: 768px) {
        .proc-illus { max-width: none; }
    }

    /* Por defecto, todos los elementos animables están parados (paused)
       Solo se activan cuando el artículo padre recibe .is-animated */
    .proc-illus__line,
    .proc-illus__img-ph,
    .proc-illus__cam-badge,
    .proc-illus__ai-row,
    .proc-illus__qr-grid span.proc-illus__qr-cell--dark,
    .proc-illus__miniphone {
        animation-play-state: paused;
    }
    [data-process-slide].is-animated .proc-illus__line,
    [data-process-slide].is-animated .proc-illus__img-ph,
    [data-process-slide].is-animated .proc-illus__cam-badge,
    [data-process-slide].is-animated .proc-illus__ai-row,
    [data-process-slide].is-animated .proc-illus__qr-grid span.proc-illus__qr-cell--dark,
    [data-process-slide].is-animated .proc-illus__miniphone {
        animation-play-state: running;
    }
    /* Paso 1 y 3: animación continua tras la entrada */
    [data-process-slide].is-animated[data-process-animate] .proc-illus__paper {
        animation: proc-paper-float 3.2s ease-in-out infinite;
    }
    [data-process-slide].is-animated[data-process-animate] .proc-illus__cam-badge {
        animation: proc-cam-pop 0.45s cubic-bezier(.34,1.56,.64,1) forwards,
                   proc-cam-pulse 2.2s ease-in-out 0.9s infinite;
    }
    [data-process-slide].is-animated[data-process-animate] .proc-illus__qr-grid {
        animation: proc-qr-settle 0.5s ease forwards;
    }
    [data-process-slide].is-animated[data-process-animate] .proc-illus__miniphone {
        animation: proc-miniphone-slide 0.55s cubic-bezier(.34,1.2,.64,1) forwards,
                   proc-miniphone-float 2.8s ease-in-out 0.6s infinite;
    }
    @keyframes proc-paper-float {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-5px); }
    }
    @keyframes proc-cam-pulse {
        0%, 100% { box-shadow: 0 4px 12px rgba(15,23,42,0.25); transform: scale(1); }
        50%      { box-shadow: 0 6px 18px rgba(0,74,198,0.35); transform: scale(1.06); }
    }
    @keyframes proc-qr-settle {
        from { opacity: 0.85; transform: scale(0.98); }
        to   { opacity: 1; transform: scale(1); }
    }
    @keyframes proc-miniphone-float {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-4px); }
    }

    /* --- Paso 1: Documento + cámara --- */
    .proc-illus--doc {
        gap: 0;
        position: relative; /* contexto para el cam-badge absoluto */
        align-items: center;
        justify-content: center;
    }
    .proc-illus__paper {
        position: relative;
        width: 120px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 24px rgba(15,23,42,0.10);
        padding: 14px 14px 12px;
        display: flex;
        flex-direction: column;
        gap: 7px;
    }
    .proc-illus__line {
        display: block;
        height: 7px;
        border-radius: 999px;
        background: #e2e8f0;
        width: 100%;
        opacity: 0;
        transform: translateX(-6px);
        animation: proc-line-appear 0.5s ease forwards;
        animation-delay: calc(var(--i, 0) * 0.16s + 0.1s);
    }
    .proc-illus__line--title {
        height: 9px;
        width: 70%;
        background: #cbd5e1;
    }
    .proc-illus__line--short { width: 55%; }
    .proc-illus__img-ph {
        width: 100%;
        height: 44px;
        border-radius: 8px;
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        opacity: 0;
        transform: scale(0.95);
        animation: proc-ph-appear 0.5s ease forwards 0.05s;
        flex-shrink: 0;
    }
    @keyframes proc-line-appear {
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes proc-ph-appear {
        to { opacity: 1; transform: scale(1); }
    }
    .proc-illus__cam-badge {
        position: absolute;
        bottom: -12px;
        right: -12px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #0f172a;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(15,23,42,0.25);
        animation: proc-cam-pop 0.45s cubic-bezier(.34,1.56,.64,1) forwards;
        animation-delay: 0.85s;
        opacity: 0;
        transform: scale(0.5);
    }
    .proc-illus__cam-badge .material-symbols-outlined { font-size: 18px; }
    @keyframes proc-cam-pop {
        to { opacity: 1; transform: scale(1); }
    }

    /* --- Paso 2: Tarjeta IA --- */
    .proc-illus__card {
        background: #0f172a;
        border-radius: 14px;
        padding: 16px 18px;
        width: 160px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        box-shadow: 0 12px 32px rgba(15,23,42,0.22);
    }
    .proc-illus__ai-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: rgba(0, 74, 198, 0.35);
        color: #7ec8ff;
        margin-bottom: 4px;
        flex-shrink: 0;
    }
    .proc-illus__ai-badge .material-symbols-outlined { font-size: 16px; }
    .proc-illus__ai-row {
        display: block;
        height: 8px;
        border-radius: 999px;
        background: var(--c, #004ac6);
        width: var(--w, 70%);
        transform-origin: left;
        transform: scaleX(0);
        opacity: 0;
        animation: proc-row-grow 3s ease infinite;
        animation-delay: calc(var(--i, 0) * 0.28s);
    }
    /* Paso 2: barras visibles sin animación (solo 1 y 3 animan) */
    [data-process-slide]:not([data-process-animate]) .proc-illus__ai-row {
        animation: none;
        opacity: 1;
        transform: scaleX(1);
    }
    @keyframes proc-row-grow {
        0%   { transform: scaleX(0); opacity: 0; }
        18%  { transform: scaleX(1); opacity: 1; }
        72%  { transform: scaleX(1); opacity: 1; }
        100% { transform: scaleX(0); opacity: 0; }
    }

    /* --- Paso 3: QR + miniphone --- */
    .proc-illus--qr { gap: 12px; align-items: center; justify-content: center; }
    .proc-illus__qr-grid {
        display: grid;
        grid-template-columns: repeat(7, 10px);
        grid-template-rows: repeat(7, 10px);
        gap: 2px;
        flex-shrink: 0;
    }
    .proc-illus__qr-grid span {
        width: 10px;
        height: 10px;
        border-radius: 1.5px;
        background: transparent;
    }
    .proc-illus__qr-grid span.proc-illus__qr-cell--dark {
        background: #0f172a;
        animation: proc-qr-appear 0.35s ease forwards;
    }
    .proc-illus__qr-grid span:nth-child(3n+1).proc-illus__qr-cell--dark { animation-delay: 0ms; }
    .proc-illus__qr-grid span:nth-child(3n+2).proc-illus__qr-cell--dark { animation-delay: 70ms; }
    .proc-illus__qr-grid span:nth-child(3n).proc-illus__qr-cell--dark    { animation-delay: 140ms; }
    @keyframes proc-qr-appear {
        from { opacity: 0; transform: scale(0.5); }
        to   { opacity: 1; transform: scale(1); }
    }
    .proc-illus__miniphone {
        width: 52px;
        flex-shrink: 0;
        background: #0f172a;
        border-radius: 10px;
        padding: 6px 5px 8px;
        display: flex;
        flex-direction: column;
        gap: 5px;
        box-shadow: 0 6px 18px rgba(15,23,42,0.22);
        animation: proc-miniphone-slide 0.5s cubic-bezier(.34,1.2,.64,1) forwards;
        animation-delay: 0.35s;
        opacity: 0;
        transform: translateY(10px);
    }
    @keyframes proc-miniphone-slide {
        to { opacity: 1; transform: translateY(0); }
    }
    .proc-illus__miniphone-url {
        font-size: 0.4375rem;
        font-weight: 600;
        color: #94a3b8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        background: rgba(255,255,255,0.07);
        border-radius: 3px;
        padding: 1px 3px;
        display: block;
    }
    .proc-illus__miniphone-thumb {
        width: 100%;
        height: 34px;
        border-radius: 5px;
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    }
    .wn-process-stagger-list li {
        opacity: 0;
        animation: wn-process-fade-in 0.55s ease forwards;
        animation-delay: calc(0.12s * (var(--stagger, 0) + 1));
    }
    @keyframes wn-process-fade-in {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .wn-process-slider__arrow {
        display: none;
        position: absolute;
        top: 42%;
        transform: translateY(-50%);
        z-index: 5;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 1px solid rgba(0, 74, 198, 0.2);
        background: #fff;
        box-shadow: 0 4px 14px rgba(0, 37, 89, 0.12);
        align-items: center;
        justify-content: center;
        color: #004ac6;
        cursor: pointer;
    }
    .wn-process-slider__arrow--prev { left: -6px; }
    .wn-process-slider__arrow--next { right: -6px; }
    .wn-process-slider__dots {
        display: none;
        justify-content: center;
        gap: 8px;
        margin-top: 1rem;
    }
    .wn-process-slider__dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        border: none;
        padding: 0;
        background: rgba(0, 53, 148, 0.25);
        cursor: pointer;
    }
    .wn-process-slider__dot.is-active {
        background: #004ac6;
        transform: scale(1.2);
    }
    @media (max-width: 767.98px) {
        .wn-process-slider__arrow { display: inline-flex; }
        .wn-process-slider__dots { display: flex; }
    }
    /* Proceso — responsive */
    @media (max-width: 767.98px) {
        #process {
            margin-left: 0;
            margin-right: 0;
            border-radius: 1.25rem;
        }
        .proc-illus {
            max-width: 100%;
        }
    }

    /* Plantillas — responsive tpl-phone */
    @media (max-width: 767.98px) {
        .tpl-phone {
            max-width: 220px;
            border-width: 8px;
            border-radius: 34px;
        }
        .tpl-phone__notch {
            width: 70px;
            height: 18px;
        }
    }

    /* prefers-reduced-motion — todos los bloques */
    @media (prefers-reduced-motion: reduce) {
        /* Proceso */
        .proc-illus__line,
        .proc-illus__img-ph,
        .proc-illus__cam-badge,
        .proc-illus__ai-row,
        .proc-illus__qr-grid span,
        .proc-illus__miniphone,
        .wn-process-stagger-list li,
        [data-process-slide].is-animated[data-process-animate] .proc-illus__paper,
        [data-process-slide].is-animated[data-process-animate] .proc-illus__cam-badge,
        [data-process-slide].is-animated[data-process-animate] .proc-illus__qr-grid,
        [data-process-slide].is-animated[data-process-animate] .proc-illus__miniphone {
            animation: none !important;
            animation-play-state: running !important;
            opacity: 1 !important;
            transform: none !important;
        }
        /* Plantillas */
        .tpl-phone,
        .tpl-phone__business,
        .tpl-phone__dish-name,
        .tpl-phone__dish-price,
        .tpl-phone__dish-desc,
        .tpl-phone__badge,
        .tpl-phone__dish-thumb {
            transition: none !important;
        }
    }

    /* CTA grande de escaneo IA */
    .wn-scan-cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 16px 22px;
        border-radius: 14px;
        background: linear-gradient(135deg, #fd651e 0%, #ea580c 100%);
        color: #ffffff;
        font-weight: 700;
        font-size: 1rem;
        text-decoration: none;
        box-shadow: 0 12px 28px rgba(234, 88, 12, 0.32);
        transition: transform 0.25s ease, box-shadow 0.25s ease, opacity 0.25s ease;
    }
    .wn-scan-cta:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 32px rgba(234, 88, 12, 0.42);
        color: #ffffff;
    }
    .wn-scan-cta__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.22);
        animation: wn-scan-pulse 2.2s ease-in-out infinite;
    }
    @keyframes wn-scan-pulse {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.45); }
        50%      { transform: scale(1.06); box-shadow: 0 0 0 10px rgba(255, 255, 255, 0); }
    }
    @media (prefers-reduced-motion: reduce) {
        .wn-scan-cta__icon { animation: none; }
    }
    .wn-scan-aside {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 16px 18px;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
    }
    .wn-scan-aside__list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        grid-template-columns: 1fr;
        gap: 6px;
    }
    .wn-scan-aside__list li {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 0.8125rem;
        color: #434654;
    }
    .wn-scan-aside__list .material-symbols-outlined {
        font-size: 18px;
        color: #003594;
    }

    /* Modificadores para la sección "Tu carta digital en 3 pasos" unificada */
    .landing-scan-demo--compact { padding: 0; max-width: 320px; margin: 0 auto; }
    .landing-scan-demo--compact .landing-scan-demo__frame { padding: 14px 14px 16px; }
    .landing-scan-demo--compact .landing-scan-demo__row { font-size: 13px; margin-bottom: 6px; }
    .landing-scan-demo--compact .landing-scan-demo__section { font-size: 10px; margin-bottom: 8px; }
    .landing-scan-demo--compact .landing-scan-demo__section--spaced { margin-top: 12px; }

    .wn-scan-aside__list--inline {
        background: transparent;
        border: 0;
        padding: 0;
    }
    .wn-scan-aside__list--inline li {
        font-size: 0.85rem;
        line-height: 1.4;
    }

    .wn-scan-cta--block { width: 100%; padding: 14px 18px; font-size: 0.95rem; }
    @media (min-width: 768px) {
        .landing-scan-demo--compact { margin: 0; }
    }
</style>
<script>
    (function () {
        var splash = null;
        function hideSplash() {
            if (!splash) return;
            splash.classList.add('is-hiding');
            window.setTimeout(function () {
                if (splash && splash.parentNode) splash.parentNode.removeChild(splash);
                splash = null;
            }, 420);
        }
        function init() {
            splash = document.getElementById('wn-splash');
            if (!splash) return;
            window.requestAnimationFrame(function () {
                window.setTimeout(hideSplash, 650);
            });
            window.addEventListener('load', hideSplash, { once: true });
            window.setTimeout(hideSplash, 3000);
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init, { once: true });
        } else {
            init();
        }
    })();
</script>
