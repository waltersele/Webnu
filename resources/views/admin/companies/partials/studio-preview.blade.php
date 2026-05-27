<div class="wn-studio-preview-col">
    <div class="card wn-studio-preview-card border-0 shadow-sm h-100">
        <div class="card-header py-3">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                <h6 class="mb-0 fw-semibold"><i class="ri-smartphone-line me-1"></i> Vista previa en vivo</h6>
                <span class="badge bg-label-primary" id="wn-preview-template-label">—</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" id="wn-preview-refresh">
                    <i class="ri-refresh-line me-1"></i> Actualizar
                </button>
                <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-primary" id="wn-preview-open">
                    <i class="ri-external-link-line me-1"></i> Abrir
                </a>
            </div>
        </div>
        <div class="card-body pt-0 pb-3">
            <div class="wn-phone-frame">
                <div class="wn-phone-notch"></div>
                <div class="wn-phone-frame__viewport">
                    <div class="wn-phone-frame__loader">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <span class="small text-muted">Cargando carta…</span>
                    </div>
                    <iframe id="wn-carta-preview" title="Vista previa de la carta" loading="eager"></iframe>
                </div>
            </div>
            @if(!empty($previewUsesSamples))
                <p class="small text-primary mt-3 mb-2" id="wn-preview-sample-hint">
                    <i class="ri-information-line me-1"></i>
                    Aún no tienes platos: mostramos <strong>ejemplos</strong> para que veas el estilo. Al añadir tu carta, se sustituyen por tus platos reales.
                </p>
            @endif
            <ul class="list-unstyled small text-muted mb-0 mt-3 wn-preview-hints">
                <li><i class="ri-check-line text-success"></i> Plantilla, colores y fuentes se ven al instante aquí.</li>
                <li><i class="ri-save-line"></i> Pulsa <strong>Guardar</strong> para publicar en la carta real.</li>
            </ul>
        </div>
    </div>
</div>

