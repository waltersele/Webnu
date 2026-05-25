<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex" />
    <meta name="theme-color" content="#141b2b">
    <title>Carta {{ $company->name }}</title>
    <link rel="icon" type="image/png" href="{{ \App\PlatformSetting::brandUrl('favicon') }}" />
    <style>
        :root {
            --pdf-bg: #141b2b;
            --pdf-surface: rgba(0, 0, 0, 0.35);
            --pdf-border: rgba(255, 255, 255, 0.08);
            --pdf-muted: rgba(255, 255, 255, 0.7);
            --pdf-fg: #fff;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            background: var(--pdf-bg);
            color: var(--pdf-fg);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            overscroll-behavior: none;
        }
        body {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
        }
        .menu-pdf-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 18px;
            background: var(--pdf-surface);
            border-bottom: 1px solid var(--pdf-border);
            flex-shrink: 0;
        }
        .menu-pdf-header img {
            height: 32px;
            width: auto;
        }
        .menu-pdf-header__title {
            font-size: 0.9375rem;
            font-weight: 600;
            margin: 0;
            line-height: 1.2;
        }
        .menu-pdf-header__url {
            font-size: 0.7rem;
            color: var(--pdf-muted);
            margin: 2px 0 0;
            word-break: break-all;
        }
        .menu-pdf-header__meta {
            text-align: right;
            min-width: 0;
        }
        .menu-pdf-stage {
            flex: 1 1 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            min-height: 0;
            position: relative;
        }
        #flipbook {
            margin: 0 auto;
            max-width: 100%;
            max-height: 100%;
            touch-action: pan-y;
        }
        #flipbook .pdf-page {
            background: #fff;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        #flipbook .pdf-page canvas {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .menu-pdf-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
            background: var(--pdf-surface);
            border-top: 1px solid var(--pdf-border);
            flex-shrink: 0;
        }
        .menu-pdf-controls button {
            appearance: none;
            background: rgba(255, 255, 255, 0.08);
            color: var(--pdf-fg);
            border: 1px solid var(--pdf-border);
            border-radius: 999px;
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            transition: background 0.15s ease, transform 0.15s ease;
        }
        .menu-pdf-controls button:hover:not(:disabled) {
            background: rgba(255, 255, 255, 0.16);
        }
        .menu-pdf-controls button:active:not(:disabled) {
            transform: scale(0.96);
        }
        .menu-pdf-controls button:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        .menu-pdf-controls__counter {
            font-variant-numeric: tabular-nums;
            font-size: 0.875rem;
            color: var(--pdf-muted);
            min-width: 78px;
            text-align: center;
        }
        .menu-pdf-loader {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: var(--pdf-bg);
            color: var(--pdf-muted);
            font-size: 0.875rem;
            z-index: 5;
            transition: opacity 0.3s ease;
        }
        .menu-pdf-loader[hidden] {
            opacity: 0;
            pointer-events: none;
            display: flex;
        }
        .menu-pdf-spinner {
            width: 36px;
            height: 36px;
            border: 3px solid rgba(255, 255, 255, 0.18);
            border-top-color: #fff;
            border-radius: 50%;
            animation: menu-pdf-spin 0.8s linear infinite;
        }
        @keyframes menu-pdf-spin {
            to { transform: rotate(360deg); }
        }
        .menu-pdf-error {
            color: #fca5a5;
            font-size: 0.875rem;
            text-align: center;
            padding: 0 16px;
        }
        /* Pseudo-fullscreen para iOS Safari (no soporta requestFullscreen sobre <html>) */
        body.is-pseudo-fullscreen {
            position: fixed;
            inset: 0;
            z-index: 9999;
        }
        body.is-pseudo-fullscreen .menu-pdf-header {
            display: none;
        }
        @media (max-width: 640px) {
            .menu-pdf-header {
                padding: 10px 12px;
            }
            .menu-pdf-header img {
                height: 26px;
            }
            .menu-pdf-header__title {
                font-size: 0.8125rem;
            }
            .menu-pdf-header__url {
                font-size: 0.625rem;
            }
            .menu-pdf-stage {
                padding: 8px;
            }
            .menu-pdf-controls {
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <header class="menu-pdf-header">
        <img src="{{ \App\PlatformSetting::brandUrl('logo') }}" alt="Webnu">
        <div class="menu-pdf-header__meta">
            <p class="menu-pdf-header__title">{{ $company->name }}</p>
            <p class="menu-pdf-header__url">{{ url('/carta/' . $company->slug) }}</p>
        </div>
    </header>

    <main class="menu-pdf-stage">
        <div id="flipbook" aria-label="Carta en PDF"></div>
        <div class="menu-pdf-loader" id="pdf-loader" role="status" aria-live="polite">
            <div class="menu-pdf-spinner" aria-hidden="true"></div>
            <span>Cargando carta…</span>
        </div>
    </main>

    <nav class="menu-pdf-controls" aria-label="Controles de carta">
        <button type="button" id="pdf-prev" title="Página anterior" aria-label="Página anterior" disabled>&#8592;</button>
        <span class="menu-pdf-controls__counter" id="pdf-counter">– / –</span>
        <button type="button" id="pdf-next" title="Página siguiente" aria-label="Página siguiente" disabled>&#8594;</button>
        <button type="button" id="pdf-fullscreen" title="Pantalla completa" aria-label="Pantalla completa" disabled>&#x26F6;</button>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.js"></script>
    <script>
    (function () {
        const PDF_URL = @json(asset('img/' . $company->menu_type_2_pdf));

        const stage = document.querySelector('.menu-pdf-stage');
        const loader = document.getElementById('pdf-loader');
        const flipbookEl = document.getElementById('flipbook');
        const prevBtn = document.getElementById('pdf-prev');
        const nextBtn = document.getElementById('pdf-next');
        const fsBtn = document.getElementById('pdf-fullscreen');
        const counter = document.getElementById('pdf-counter');

        if (typeof pdfjsLib === 'undefined' || typeof St === 'undefined') {
            showError('No se han podido cargar los componentes del visor. Recarga la página.');
            return;
        }

        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js';

        let pageFlip = null;

        function showError(msg) {
            loader.innerHTML = '<div class="menu-pdf-error">' + msg + '</div>';
            loader.hidden = false;
        }

        function computeStageSize(ratio) {
            const stageRect = stage.getBoundingClientRect();
            const availableW = Math.max(280, stageRect.width - 16);
            const availableH = Math.max(360, stageRect.height - 16);

            let width = availableW;
            let height = width / ratio;
            if (height > availableH) {
                height = availableH;
                width = height * ratio;
            }
            return { width: Math.floor(width), height: Math.floor(height) };
        }

        async function renderPage(pdf, pageNumber, targetWidth) {
            const page = await pdf.getPage(pageNumber);
            const baseViewport = page.getViewport({ scale: 1 });
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            const scale = Math.max(1, (targetWidth / baseViewport.width) * dpr);
            const viewport = page.getViewport({ scale });

            const canvas = document.createElement('canvas');
            canvas.width = Math.floor(viewport.width);
            canvas.height = Math.floor(viewport.height);
            const ctx = canvas.getContext('2d', { alpha: false });
            await page.render({ canvasContext: ctx, viewport }).promise;

            const wrapper = document.createElement('div');
            wrapper.className = 'pdf-page';
            wrapper.appendChild(canvas);
            return wrapper;
        }

        function updateCounter(currentZeroBased, total) {
            counter.textContent = (currentZeroBased + 1) + ' / ' + total;
            prevBtn.disabled = currentZeroBased <= 0;
            nextBtn.disabled = currentZeroBased >= total - 1;
        }

        async function init() {
            try {
                const pdf = await pdfjsLib.getDocument({ url: PDF_URL, withCredentials: false }).promise;
                const firstPage = await pdf.getPage(1);
                const firstViewport = firstPage.getViewport({ scale: 1 });
                const ratio = firstViewport.width / firstViewport.height;

                const initialSize = computeStageSize(ratio);
                const targetRenderWidth = Math.max(initialSize.width, 720);

                const pageEls = [];
                for (let i = 1; i <= pdf.numPages; i++) {
                    pageEls.push(await renderPage(pdf, i, targetRenderWidth));
                }

                pageFlip = new St.PageFlip(flipbookEl, {
                    width: initialSize.width,
                    height: initialSize.height,
                    size: 'stretch',
                    minWidth: 280,
                    maxWidth: 1400,
                    minHeight: 360,
                    maxHeight: 1800,
                    drawShadow: true,
                    flippingTime: 700,
                    usePortrait: true,
                    showCover: false,
                    showPageCorners: true,
                    disableFlipByClick: false,
                    mobileScrollSupport: false,
                    useMouseEvents: true,
                    swipeDistance: 30,
                });

                pageFlip.loadFromHTML(pageEls);

                updateCounter(0, pdf.numPages);
                pageFlip.on('flip', (e) => updateCounter(e.data, pdf.numPages));

                prevBtn.addEventListener('click', () => pageFlip.flipPrev());
                nextBtn.addEventListener('click', () => pageFlip.flipNext());

                fsBtn.disabled = false;
                fsBtn.addEventListener('click', toggleFullscreen);

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowLeft') { e.preventDefault(); pageFlip.flipPrev(); }
                    else if (e.key === 'ArrowRight') { e.preventDefault(); pageFlip.flipNext(); }
                    else if (e.key === 'Escape' && document.body.classList.contains('is-pseudo-fullscreen')) {
                        exitPseudoFullscreen();
                    }
                });

                let resizeTimer = null;
                window.addEventListener('resize', () => {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(() => {
                        const size = computeStageSize(ratio);
                        try {
                            pageFlip.update({
                                width: size.width,
                                height: size.height,
                            });
                        } catch (err) {
                            /* update() puede no existir en todas las builds; ignorar */
                        }
                    }, 150);
                });

                loader.hidden = true;
            } catch (err) {
                console.error('Error cargando PDF', err);
                showError('No se pudo cargar la carta. Comprueba tu conexión o vuelve a intentarlo.');
            }
        }

        function toggleFullscreen() {
            const inNativeFs = document.fullscreenElement || document.webkitFullscreenElement;
            const inPseudoFs = document.body.classList.contains('is-pseudo-fullscreen');

            if (inNativeFs || inPseudoFs) {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
                exitPseudoFullscreen();
                return;
            }

            const root = document.documentElement;
            if (root.requestFullscreen) {
                root.requestFullscreen().catch(() => enterPseudoFullscreen());
            } else if (root.webkitRequestFullscreen) {
                root.webkitRequestFullscreen();
            } else {
                enterPseudoFullscreen();
            }
        }

        function enterPseudoFullscreen() {
            document.body.classList.add('is-pseudo-fullscreen');
            window.dispatchEvent(new Event('resize'));
        }

        function exitPseudoFullscreen() {
            if (document.body.classList.contains('is-pseudo-fullscreen')) {
                document.body.classList.remove('is-pseudo-fullscreen');
                window.dispatchEvent(new Event('resize'));
            }
        }

        document.addEventListener('fullscreenchange', () => {
            if (!document.fullscreenElement) {
                window.dispatchEvent(new Event('resize'));
            }
        });

        init();
    })();
    </script>
</body>
</html>
