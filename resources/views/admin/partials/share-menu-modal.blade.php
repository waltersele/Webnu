<div class="modal fade" id="modal-share-menu" tabindex="-1" aria-labelledby="modal-share-menu-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-share-menu-title">Compartir carta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                @include('admin.partials.share-menu', [
                    'company' => $company,
                    'shareUrl' => $shareUrl ?? null,
                    'sharePath' => $sharePath ?? null,
                    'shareTitle' => $shareTitle ?? null,
                ])
            </div>
        </div>
    </div>
</div>
