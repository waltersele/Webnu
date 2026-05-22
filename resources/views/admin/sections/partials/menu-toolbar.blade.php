@php
    $pf = $planFeatures ?? [];
    $ut = $upgradeTriggers ?? [];
    $locked = $ut['locked_features'] ?? [];
    $publicUrl = route('see_menu', $company->slug);
    $languagesUrl = route('admin.companies.languages', $company);
    $hasTranslation = $pf['translation'] ?? true;
    $hasMenuScan = $pf['menu_scan'] ?? true;
    $hasPdfMenu = $pf['pdf_menu'] ?? true;
@endphp
<div class="wn-menu-toolbar d-flex flex-wrap align-items-center gap-2 mb-4">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-section">
        <i class="ri ri-add-line me-1"></i> Nueva sección
    </button>

    <a class="btn btn-outline-primary" href="{{ $publicUrl }}" target="_blank" rel="noopener">
        <i class="ri ri-eye-line me-1"></i> Vista previa
    </a>

    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="ri ri-share-line me-1"></i> Compartir
        </button>
        <ul class="dropdown-menu">
            <li>
                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modal-share-menu">
                    <i class="ri ri-share-line me-2"></i> Compartir carta
                </button>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('admin.qrgenerator', $company) }}" target="_blank" rel="noopener">
                    <i class="ri ri-qr-code-line me-2"></i> Descargar QR
                </a>
            </li>
            <li>
                <button type="button" class="dropdown-item" onclick="frames['printMenu'].print(); return false;">
                    <i class="ri ri-printer-line me-2"></i> Imprimir web
                </button>
            </li>
        </ul>
    </div>

    <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            Más
        </button>
        <ul class="dropdown-menu">
            <li>
                @component('admin.partials.plan-gated-action', [
                    'feature' => 'translation',
                    'enabled' => $hasTranslation,
                    'planLabel' => 'Pro',
                    'element' => 'a',
                    'href' => $languagesUrl,
                    'class' => 'dropdown-item',
                    'fallbackHref' => $languagesUrl,
                    'attrs' => '',
                ])
                    <i class="ri ri-translate-2 me-2"></i> Idiomas
                @endcomponent
            </li>
            <li>
                @component('admin.partials.plan-gated-action', [
                    'feature' => 'menu_scan',
                    'enabled' => $hasMenuScan,
                    'planLabel' => 'Pro',
                    'element' => 'a',
                    'href' => route('admin.menu-scan.create'),
                    'class' => 'dropdown-item',
                ])
                    <i class="ri ri-camera-line me-2"></i> Importar con IA
                @endcomponent
            </li>
            <li>
                @component('admin.partials.plan-gated-action', [
                    'feature' => 'pdf_menu',
                    'enabled' => $hasPdfMenu,
                    'planLabel' => 'Pro',
                    'element' => 'a',
                    'href' => route('admin.menu-print', $company),
                    'class' => 'dropdown-item',
                    'attrs' => 'target="_blank" rel="noopener"',
                ])
                    <i class="ri ri-file-pdf-line me-2"></i> Carta A4 (PDF)
                @endcomponent
            </li>
        </ul>
    </div>
</div>
