<style>
/* Tras Bootstrap + front-{template}.css: capas bloqueadas del modal.
   Los temas NO deben sobrescribir .wn-media-chrome ni el fav del modal. */

body .wn-dish-modal .modal-content.wn-dish-modal__content {
    background: var(--wn-surface) !important;
    color: var(--wn-text) !important;
    border: 1px solid rgba(128, 128, 128, 0.2);
}

body.wn-theme-lumiere .wn-dish-modal .modal-content.wn-dish-modal__content,
body.wn-theme-oriental .wn-dish-modal .modal-content.wn-dish-modal__content {
    border-color: rgba(255, 255, 255, 0.1);
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.55);
}

body .wn-dish-modal__title--solo {
    color: var(--wn-text) !important;
}

body.wn-theme-lumiere .wn-dish-modal__title--solo {
    font-family: 'Playfair Display', Georgia, serif;
    font-weight: 600;
}

/*
 * Chrome sobre media (foto/vídeo): misma pastilla que la X de cerrar.
 * Contraste garantizado en claro/oscuro — base sólida para todos los templates.
 */
body .wn-dish-modal .wn-media-chrome {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    width: 36px !important;
    height: 36px !important;
    min-width: 36px !important;
    min-height: 36px !important;
    padding: 0;
    border: none !important;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.58) !important;
    color: #fff !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.35) !important;
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    cursor: pointer;
}

body .wn-dish-modal .wn-media-chrome .wn-svg-icon {
    width: 1.05rem;
    height: 1.05rem;
    color: #fff !important;
}

body .wn-dish-modal .wn-media-chrome .wn-svg-icon path {
    stroke: currentColor;
}

body .wn-dish-modal .wn-dish-modal__close.wn-media-chrome {
    position: absolute;
    top: 0.65rem;
    right: 0.65rem;
    z-index: 6;
}

body .wn-dish-modal .wn-fav-btn.wn-fav-btn--modal.wn-media-chrome {
    position: absolute;
    top: 0.65rem;
    right: 3.15rem;
    z-index: 6;
}

body .wn-dish-modal .wn-fav-btn.wn-fav-btn--modal.wn-media-chrome.is-favorited,
body .wn-dish-modal .wn-fav-btn.wn-fav-btn--modal.wn-media-chrome[aria-pressed='true'] {
    background: #e11d48 !important;
    color: #fff !important;
}

body .wn-dish-modal .wn-fav-btn.wn-fav-btn--modal.wn-media-chrome.is-favorited .wn-svg-heart path,
body .wn-dish-modal .wn-fav-btn.wn-fav-btn--modal.wn-media-chrome[aria-pressed='true'] .wn-svg-heart path {
    fill: currentColor;
    stroke: currentColor;
}
</style>
