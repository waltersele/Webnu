@php
    $qrCompanies = collect($available_companies ?? []);
    $defaultQrCompany = $currentCompany ?? $qrCompanies->first();
    $hasMultiple = $qrCompanies->count() > 1;
@endphp
@if($defaultQrCompany)
<div class="modal fade wn-qr-modal" id="wn-qr-modal" tabindex="-1" aria-labelledby="wn-qr-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="wn-qr-modal-title">
                    <i class="ri ri-qr-code-line me-2"></i> Código QR de tu carta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                @if($hasMultiple)
                    <div class="wn-qr-modal__tabs mb-3" role="tablist" aria-label="Seleccionar carta">
                        @foreach($qrCompanies as $idx => $c)
                            <button type="button"
                                    class="wn-qr-modal__tab {{ (int) $c->id === (int) $defaultQrCompany->id ? 'is-active' : '' }}"
                                    data-qr-tab="{{ $c->id }}"
                                    role="tab"
                                    aria-selected="{{ (int) $c->id === (int) $defaultQrCompany->id ? 'true' : 'false' }}">
                                {{ $c->name }}
                            </button>
                        @endforeach
                    </div>
                @endif

                @foreach($qrCompanies as $c)
                    @php
                        $publicUrl = $c->publicUrl();
                        $pngUrl = route('admin.qrgenerator', ['company' => $c, 'format' => 'png']);
                        $pdfDownloadUrl = route('admin.qrgenerator', ['company' => $c, 'download' => 1]);
                        $pngDownloadUrl = route('admin.qrgenerator', ['company' => $c, 'format' => 'png', 'download' => 1]);
                        $isDefault = (int) $c->id === (int) $defaultQrCompany->id;
                    @endphp
                    <div class="wn-qr-modal__panel {{ $isDefault ? 'is-active' : '' }}" data-qr-panel="{{ $c->id }}">
                        <div class="wn-qr-modal__art">
                            <img src="{{ $pngUrl }}"
                                 alt="Código QR de {{ $c->name }}"
                                 class="wn-qr-modal__img"
                                 loading="lazy"
                                 width="260"
                                 height="260">
                        </div>

                        <div class="wn-qr-modal__url">
                            <label class="wn-qr-modal__url-label">URL pública</label>
                            <div class="input-group">
                                <input type="text"
                                       class="form-control wn-qr-modal__url-input"
                                       value="{{ $publicUrl }}"
                                       readonly
                                       aria-label="URL pública">
                                <button type="button"
                                        class="btn btn-outline-primary wn-qr-modal__copy"
                                        data-copy-url="{{ $publicUrl }}"
                                        title="Copiar enlace">
                                    <i class="ri ri-file-copy-line"></i>
                                    <span class="ms-1 d-none d-sm-inline">Copiar</span>
                                </button>
                            </div>
                        </div>

                        <div class="wn-qr-modal__actions">
                            <a href="{{ $pdfDownloadUrl }}"
                               class="btn btn-primary wn-qr-modal__btn"
                               target="_blank"
                               rel="noopener">
                                <i class="ri ri-file-pdf-2-line"></i> PDF
                            </a>
                            <a href="{{ $pngDownloadUrl }}"
                               class="btn btn-outline-secondary wn-qr-modal__btn"
                               download="carta-qr-{{ $c->slug }}.png">
                                <i class="ri ri-image-line"></i> PNG
                            </a>
                            <a href="{{ $publicUrl }}"
                               class="btn btn-outline-secondary wn-qr-modal__btn"
                               target="_blank"
                               rel="noopener">
                                <i class="ri ri-external-link-line"></i> Ver
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('wn-qr-modal');
    if (!modal || modal.dataset.wnQrInit === '1') return;
    modal.dataset.wnQrInit = '1';

    modal.addEventListener('click', function (e) {
        var tab = e.target.closest('[data-qr-tab]');
        if (tab) {
            var key = tab.getAttribute('data-qr-tab');
            modal.querySelectorAll('[data-qr-tab]').forEach(function (t) {
                var active = t.getAttribute('data-qr-tab') === key;
                t.classList.toggle('is-active', active);
                t.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            modal.querySelectorAll('[data-qr-panel]').forEach(function (p) {
                p.classList.toggle('is-active', p.getAttribute('data-qr-panel') === key);
            });
            return;
        }

        var copyBtn = e.target.closest('.wn-qr-modal__copy');
        if (copyBtn) {
            var url = copyBtn.getAttribute('data-copy-url');
            if (!url) return;
            var done = function () {
                var orig = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="ri ri-check-line"></i> <span class="ms-1 d-none d-sm-inline">Copiado</span>';
                copyBtn.classList.add('is-copied');
                setTimeout(function () {
                    copyBtn.innerHTML = orig;
                    copyBtn.classList.remove('is-copied');
                }, 1600);
            };
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(done).catch(function () {
                        var input = copyBtn.closest('.input-group').querySelector('input');
                        if (input) { input.select(); document.execCommand('copy'); done(); }
                    });
                } else {
                    var input = copyBtn.closest('.input-group').querySelector('input');
                    if (input) { input.select(); document.execCommand('copy'); done(); }
                }
            } catch (err) {}
        }
    });
})();
</script>
@endif
