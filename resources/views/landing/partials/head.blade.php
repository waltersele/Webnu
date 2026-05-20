<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="description" content="Webnu.es — Carta digital para restaurantes. Plan gratis, escaneo IA, reels en platos y QR al instante."/>
<title>Webnu.es — Carta digital para hostelería</title>
<link rel="icon" type="image/png" href="{{ asset('img/front/favicon.png') }}"/>
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
    .landing-plan-badge--plus {
        background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%);
        color: #fff;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
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
</style>
