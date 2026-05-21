@php
    $billingUrl = ($upgradeTriggers['billing_url'] ?? null) ?: ($planFeatures['billing_url'] ?? route('admin.settings'));
@endphp
<div class="modal fade" id="wn-upgrade-trigger-modal" tabindex="-1" aria-labelledby="wn-upgrade-trigger-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content wn-upgrade-trigger-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    @include('admin.partials.plan-pro-badge', ['label' => 'Plus', 'size' => 'xs'])
                    <h5 class="modal-title mt-2 mb-0" id="wn-upgrade-trigger-modal-title"></h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-0 text-muted" id="wn-upgrade-trigger-modal-body"></p>
            </div>
            <div class="modal-footer border-0 flex-wrap gap-2 justify-content-between">
                <a href="#" class="btn btn-label-secondary btn-sm d-none" id="wn-upgrade-trigger-modal-fallback">Ver idiomas</a>
                <div class="d-flex gap-2 ms-auto">
                    <button type="button" class="btn btn-label-secondary btn-sm" data-bs-dismiss="modal">Ahora no</button>
                    <a href="{{ $billingUrl }}" class="btn btn-primary btn-sm" id="wn-upgrade-trigger-modal-cta">Ver plan Plus</a>
                </div>
            </div>
        </div>
    </div>
</div>
