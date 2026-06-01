{{-- Vista previa TV embebida (plantilla seleccionada con «Usar») --}}
<div class="wn-tvpik-preview" id="wn-tvpik-preview">
    <div class="wn-tvpik-preview__header d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <div>
            <span class="text-muted small d-block">Vista previa</span>
            <strong class="wn-tvpik-preview__title" id="wn-tvpik-preview-title">Carta completa</strong>
        </div>
        <a href="#"
           class="btn btn-sm btn-label-secondary d-none"
           id="wn-tvpik-preview-open-tab"
           target="_blank"
           rel="noopener">
            <i class="ti ti-external-link me-1"></i> Abrir en pestaña
        </a>
    </div>
    <div class="wn-tvpik-preview__frame">
        <div class="wn-tvpik-preview__placeholder" id="wn-tvpik-preview-placeholder">
            <i class="ti ti-device-tv"></i>
            <p class="mb-0 small">Elige una plantilla abajo y pulsa <strong>Usar</strong> para verla aquí.</p>
        </div>
        <iframe id="wn-tvpik-preview-iframe"
                class="wn-tvpik-preview__iframe d-none"
                title="Vista previa de plantilla TV"
                loading="lazy"></iframe>
    </div>
</div>
