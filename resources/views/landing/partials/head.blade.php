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
    .faq-item.faq-open .faq-content { max-height: 280px; }
    .faq-item.faq-open .faq-icon { transform: rotate(180deg); }
</style>
