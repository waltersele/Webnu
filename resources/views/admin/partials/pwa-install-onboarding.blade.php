{{--
    Variante de instalación PWA específica para el onboarding.
    Usa solo Remix Icons (ri-...) porque el layout de onboarding no carga Tabler.
    Mantiene los data-attributes que espera webnu-pwa-install.js.
--}}
<div class="wn-onb-pwa-card" data-pwa-card>
    <div class="wn-onb-pwa-card__head">
        <div class="wn-onb-pwa-card__icon" aria-hidden="true">
            <i class="ri-smartphone-line"></i>
        </div>
        <div class="wn-onb-pwa-card__head-text">
            <h3 class="wn-onb-pwa-card__title">Instala Webnu en tu móvil</h3>
            <p class="wn-onb-pwa-card__lead">Accede al panel como una app, más rápido y desde la pantalla de inicio.</p>
        </div>
    </div>

    <div class="wn-onb-pwa-actions" data-pwa-actions>
        <button type="button" class="wn-onb-btn wn-onb-btn--primary wn-onb-pwa-actions__btn d-none" data-pwa-install>
            <i class="ri-download-2-line"></i> Instalar en este dispositivo
        </button>
    </div>

    <ul class="wn-onb-pwa-steps" data-pwa-instructions>
        <li class="wn-onb-pwa-step">
            <span class="wn-onb-pwa-step__platform">
                <i class="ri-android-line" aria-hidden="true"></i>
                <strong>Android</strong>
            </span>
            <span class="wn-onb-pwa-step__hint">Menú <i class="ri-more-2-fill" aria-hidden="true"></i> → «Instalar aplicación».</span>
        </li>
        <li class="wn-onb-pwa-step">
            <span class="wn-onb-pwa-step__platform">
                <i class="ri-apple-line" aria-hidden="true"></i>
                <strong>iPhone / iPad</strong>
            </span>
            <span class="wn-onb-pwa-step__hint">Safari → <i class="ri-share-box-line" aria-hidden="true"></i> Compartir → «Añadir a pantalla de inicio».</span>
        </li>
    </ul>

    <p class="wn-onb-pwa-installed d-none" data-pwa-installed>
        <i class="ri-checkbox-circle-line" aria-hidden="true"></i>
        Ya estás usando Webnu instalada como app.
    </p>
</div>
