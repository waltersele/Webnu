@php
    $billingUrl = ($upgradeTriggers['billing_url'] ?? null) ?: ($planFeatures['billing_url'] ?? route('admin.settings'));
@endphp
<div class="modal fade" id="wn-upgrade-trigger-modal" tabindex="-1" aria-labelledby="wn-upgrade-trigger-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered wn-upgrade-modal-dialog">
        <div class="modal-content wn-upgrade-modal border-0">
            <button type="button"
                    class="btn-close wn-upgrade-modal__close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"></button>

            <div class="wn-upgrade-modal__shell">
                <div class="wn-upgrade-modal__atelier" aria-hidden="true">
                    <div class="wn-upgrade-modal__atelier-glow"></div>
                    <div class="wn-upgrade-modal__viewport">
                        <span class="wn-upgrade-modal__viewport-ring"></span>
                        <span class="wn-upgrade-modal__viewport-icon" id="wn-upgrade-trigger-modal-icon">
                            <i class="ri-movie-2-line"></i>
                        </span>
                    </div>
                    <div class="wn-upgrade-modal__stat d-none" id="wn-upgrade-trigger-modal-stat-wrap">
                        <span class="wn-upgrade-modal__stat-value" id="wn-upgrade-trigger-modal-stat"></span>
                        <span class="wn-upgrade-modal__stat-caption" id="wn-upgrade-trigger-modal-stat-caption"></span>
                    </div>
                </div>

                <div class="wn-upgrade-modal__panel">
                    <p class="wn-upgrade-modal__eyebrow">
                        <span class="wn-plan-pro-badge wn-plan-pro-badge--xs wn-plan-pro-badge--pro" id="wn-upgrade-trigger-modal-tier">Pro</span>
                        <span class="wn-upgrade-modal__eyebrow-dot" aria-hidden="true"></span>
                        <span id="wn-upgrade-trigger-modal-price"></span>
                    </p>

                    <h2 class="wn-upgrade-modal__title" id="wn-upgrade-trigger-modal-title"></h2>
                    <p class="wn-upgrade-modal__lead" id="wn-upgrade-trigger-modal-body"></p>

                    <ul class="wn-upgrade-modal__perks d-none" id="wn-upgrade-trigger-modal-perks"></ul>

                    <div class="wn-upgrade-modal__actions">
                        <a href="{{ $billingUrl }}" class="wn-upgrade-modal__cta" id="wn-upgrade-trigger-modal-cta">Ver plan Pro</a>
                        <button type="button" class="wn-upgrade-modal__dismiss" data-bs-dismiss="modal">Ahora no</button>
                    </div>

                    <a href="#" class="wn-upgrade-modal__fallback d-none" id="wn-upgrade-trigger-modal-fallback">Ver idiomas</a>
                </div>
            </div>
        </div>
    </div>
</div>
