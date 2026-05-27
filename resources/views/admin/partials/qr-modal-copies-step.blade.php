{{-- Paso 2: elegir copias por hoja A4 (reutilizable para QR hub y QR por carta) --}}
<div class="wn-qr-modal__step" data-qr-step="copies" hidden>
    <div class="wn-qr-modal__step-head">
        <button type="button"
                class="wn-qr-modal__back"
                data-qr-back
                aria-label="Volver">
            <i class="ri ri-arrow-left-line"></i> Volver
        </button>
        <h6 class="wn-qr-modal__step-title" data-qr-step-title>
            ¿Cuántos QR por hoja A4?
        </h6>
    </div>

    <p class="wn-qr-modal__step-help" data-qr-step-help>
        Elige el formato y confirma para generarlo.
    </p>

    <div class="wn-qr-modal__copies-grid" role="radiogroup" aria-label="Copias por hoja">
        <button type="button"
                class="wn-qr-modal__copy-card is-active"
                data-qr-copies="1"
                role="radio"
                aria-checked="true">
            <span class="wn-qr-modal__copy-mini wn-qr-modal__copy-mini--1" aria-hidden="true">
                <span></span>
            </span>
            <span class="wn-qr-modal__copy-number">1</span>
            <span class="wn-qr-modal__copy-label">QR por hoja</span>
            <span class="wn-qr-modal__copy-hint">A toda página, ideal para mesa o entrada.</span>
        </button>
        <button type="button"
                class="wn-qr-modal__copy-card"
                data-qr-copies="4"
                role="radio"
                aria-checked="false">
            <span class="wn-qr-modal__copy-mini wn-qr-modal__copy-mini--4" aria-hidden="true">
                <span></span><span></span><span></span><span></span>
            </span>
            <span class="wn-qr-modal__copy-number">4</span>
            <span class="wn-qr-modal__copy-label">QR por hoja</span>
            <span class="wn-qr-modal__copy-hint">2 × 2. Recorta y pega en cada mesa.</span>
        </button>
        <button type="button"
                class="wn-qr-modal__copy-card"
                data-qr-copies="12"
                role="radio"
                aria-checked="false">
            <span class="wn-qr-modal__copy-mini wn-qr-modal__copy-mini--12" aria-hidden="true">
                <span></span><span></span><span></span>
                <span></span><span></span><span></span>
                <span></span><span></span><span></span>
                <span></span><span></span><span></span>
            </span>
            <span class="wn-qr-modal__copy-number">12</span>
            <span class="wn-qr-modal__copy-label">QR por hoja</span>
            <span class="wn-qr-modal__copy-hint">3 × 4. Para reponer o repartir.</span>
        </button>
    </div>

    <p class="wn-qr-modal__feedback" data-qr-feedback role="status" aria-live="polite"></p>

    <div class="wn-qr-modal__confirm-bar">
        <button type="button"
                class="btn btn-label-secondary wn-qr-modal__back-btn"
                data-qr-back>
            Atrás
        </button>
        <button type="button"
                class="btn btn-primary wn-qr-modal__confirm"
                data-qr-confirm>
            <i class="ri ri-check-line me-1" data-qr-confirm-icon></i>
            <span data-qr-confirm-label>Confirmar</span>
        </button>
    </div>
</div>
