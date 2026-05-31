{{-- Modal: nueva pantalla --}}
<div class="modal fade" id="wn-tvpik-new-screen-modal" tabindex="-1" aria-labelledby="wn-tvpik-new-screen-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.tvpik.screens.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="wn-tvpik-new-screen-label">Nueva pantalla</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Pon un nombre que identifique la TV del local (barra, terraza, comedor…).</p>
                    <div class="mb-0">
                        <label class="form-label" for="wn-new-screen-name">Nombre</label>
                        <input type="text"
                               name="name"
                               id="wn-new-screen-name"
                               class="form-control"
                               required
                               maxlength="255"
                               placeholder="Ej. Barra principal"
                               autofocus>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear pantalla</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: emparejar TV --}}
<div class="modal fade" id="wn-tvpik-pair-modal" tabindex="-1" aria-labelledby="wn-tvpik-pair-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.tvpik.screens.pair') }}">
                @csrf
                <input type="hidden" name="screen_id" id="wn-pair-screen-id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="wn-tvpik-pair-label">Emparejar TV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Abre la <strong>app TVPik</strong> en el televisor. Verás un código de 6 caracteres.
                        Introdúcelo aquí para vincular la pantalla <strong id="wn-pair-screen-name">—</strong>.
                    </p>
                    <div class="mb-0">
                        <label class="form-label" for="wn-pair-code">Código de la TV</label>
                        <input type="text"
                               name="code"
                               id="wn-pair-code"
                               class="form-control form-control-lg text-uppercase text-center font-monospace"
                               required
                               minlength="4"
                               maxlength="8"
                               autocomplete="off"
                               placeholder="ABC123"
                               style="letter-spacing: 0.2em;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Emparejar</button>
                </div>
            </form>
        </div>
    </div>
</div>
