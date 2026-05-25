@php
    $pf = $planFeatures ?? [];
    $publicUrl = $company->publicUrl();
    $hasMenuScan = $pf['menu_scan'] ?? true;
    $hasPdfMenu = $pf['pdf_menu'] ?? true;
    $hasTranslation = $pf['translation'] ?? true;
    $languagesUrl = route('admin.companies.languages', $company);
    $plansSrv = app(\App\Services\UserPlanService::class);
    $authUser = auth()->user();
    $scanLimit = $authUser ? $plansSrv->menuScanLimit($authUser) : null;
    $scansRemaining = $authUser ? $plansSrv->menuScansRemaining($authUser) : null;
@endphp

<div class="wn-share-panel">

    <header class="mb-4">
        <h2 class="h5 mb-1">Comparte tu carta con tus clientes</h2>
        <p class="text-muted small mb-0">Tres formas rápidas para que vean tu carta hoy mismo.</p>
    </header>

    <div class="row g-3">
        <div class="col-md-6 col-xl-4">
            <button type="button" class="wn-share-card w-100 text-start" data-bs-toggle="modal" data-bs-target="#modal-share-menu">
                <span class="wn-share-card__icon"><i class="ri ri-share-forward-line"></i></span>
                <h6 class="wn-share-card__title">Compartir enlace</h6>
                <p class="wn-share-card__desc">Envía un enlace por WhatsApp, redes sociales o email. Lo más rápido para empezar a recibir clientes.</p>
                <span class="wn-share-card__cta">
                    Compartir ahora <i class="ri ri-arrow-right-line"></i>
                </span>
            </button>
        </div>

        <div class="col-md-6 col-xl-4">
            <a href="{{ route('admin.qrgenerator', $company) }}" target="_blank" rel="noopener" class="wn-share-card">
                <span class="wn-share-card__icon"><i class="ri ri-qr-code-line"></i></span>
                <h6 class="wn-share-card__title">Descargar QR</h6>
                <p class="wn-share-card__desc">Genera un código QR listo para imprimir y colocar en las mesas, escaparate o cartelería.</p>
                <span class="wn-share-card__cta">
                    Obtener QR <i class="ri ri-arrow-right-line"></i>
                </span>
            </a>
        </div>

        <div class="col-md-6 col-xl-4">
            <button type="button" class="wn-share-card w-100 text-start" onclick="WebnuPrintMenu(); return false;">
                <span class="wn-share-card__icon"><i class="ri ri-printer-line"></i></span>
                <h6 class="wn-share-card__title">Imprimir versión web</h6>
                <p class="wn-share-card__desc">Imprime una versión rápida de tu carta desde el navegador, ideal para una prueba en el local.</p>
                <span class="wn-share-card__cta">
                    Imprimir <i class="ri ri-arrow-right-line"></i>
                </span>
            </button>
        </div>

        <div class="col-md-6 col-xl-4">
            <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="wn-share-card">
                <span class="wn-share-card__icon"><i class="ri ri-eye-line"></i></span>
                <h6 class="wn-share-card__title">Ver carta pública</h6>
                <p class="wn-share-card__desc">Abre tu carta tal y como la verán tus clientes. Pulsa para revisarla antes de compartirla.</p>
                <span class="wn-share-card__cta">
                    Abrir vista pública <i class="ri ri-arrow-right-line"></i>
                </span>
            </a>
        </div>
    </div>

    <header class="mt-5 mb-3">
        <h2 class="h6 mb-1 text-muted text-uppercase" style="letter-spacing:0.06em;font-size:11px;">Avanzado</h2>
    </header>

    <div class="row g-3">
        <div class="col-md-6 col-xl-4">
            @if($hasMenuScan)
                <a href="{{ route('admin.menu-scan.create') }}" class="wn-share-card">
                    <span class="wn-share-card__icon"><i class="ri ri-camera-line"></i></span>
                    <h6 class="wn-share-card__title d-flex align-items-center gap-2">
                        Importar carta con IA
                        @if($scanLimit !== null)
                            <span class="wn-share-card__plan" style="background:#dbeafe;color:#1d4ed8;">{{ $scansRemaining }}/{{ $scanLimit }}</span>
                        @endif
                    </h6>
                    <p class="wn-share-card__desc">Sube una foto o PDF de tu carta y deja que la IA cree platos y secciones por ti.</p>
                    <span class="wn-share-card__cta">
                        Importar ahora <i class="ri ri-arrow-right-line"></i>
                    </span>
                </a>
            @else
                @include('admin.partials.plan-feature-lock', [
                    'feature' => 'menu_scan',
                    'planLabel' => 'Plus',
                    'message' => 'Importa cartas escaneadas con IA en el plan Plus.',
                    'slot' => '<div class="wn-share-card is-locked"><span class="wn-share-card__icon"><i class="ri ri-camera-line"></i></span><h6 class="wn-share-card__title d-flex align-items-center gap-2">Importar carta con IA <span class="wn-share-card__plan">Plus</span></h6><p class="wn-share-card__desc">Sube una foto o PDF y deja que la IA genere tu carta digital al instante.</p></div>',
                ])
            @endif
        </div>

        <div class="col-md-6 col-xl-4">
            @if($hasPdfMenu)
                <a href="{{ route('admin.menu-print', $company) }}" target="_blank" rel="noopener" class="wn-share-card">
                    <span class="wn-share-card__icon"><i class="ri ri-file-pdf-line"></i></span>
                    <h6 class="wn-share-card__title">Generar PDF A4</h6>
                    <p class="wn-share-card__desc">Descarga tu carta en formato A4 listo para imprimir en folios.</p>
                    <span class="wn-share-card__cta">
                        Descargar PDF <i class="ri ri-arrow-right-line"></i>
                    </span>
                </a>
            @else
                @include('admin.partials.plan-feature-lock', [
                    'feature' => 'pdf_menu',
                    'planLabel' => 'Plus',
                    'message' => 'Genera PDFs A4 listos para imprimir con el plan Plus.',
                    'slot' => '<div class="wn-share-card is-locked"><span class="wn-share-card__icon"><i class="ri ri-file-pdf-line"></i></span><h6 class="wn-share-card__title d-flex align-items-center gap-2">Generar PDF A4 <span class="wn-share-card__plan">Plus</span></h6><p class="wn-share-card__desc">Descarga tu carta en formato A4 listo para imprimir.</p></div>',
                ])
            @endif
        </div>

        <div class="col-md-6 col-xl-4">
            @if($hasTranslation)
                <a href="{{ $languagesUrl }}" class="wn-share-card">
                    <span class="wn-share-card__icon"><i class="ri ri-translate-2"></i></span>
                    <h6 class="wn-share-card__title">Carta en otros idiomas</h6>
                    <p class="wn-share-card__desc">Ofrece tu carta traducida automáticamente para clientes internacionales.</p>
                    <span class="wn-share-card__cta">
                        Gestionar idiomas <i class="ri ri-arrow-right-line"></i>
                    </span>
                </a>
            @else
                @include('admin.partials.plan-feature-lock', [
                    'feature' => 'translation',
                    'planLabel' => 'Plus',
                    'message' => 'Activa idiomas en tu carta con el plan Plus.',
                    'slot' => '<div class="wn-share-card is-locked"><span class="wn-share-card__icon"><i class="ri ri-translate-2"></i></span><h6 class="wn-share-card__title d-flex align-items-center gap-2">Carta en otros idiomas <span class="wn-share-card__plan">Plus</span></h6><p class="wn-share-card__desc">Atiende clientes internacionales con tu carta traducida.</p></div>',
                ])
            @endif
        </div>
    </div>

</div>
