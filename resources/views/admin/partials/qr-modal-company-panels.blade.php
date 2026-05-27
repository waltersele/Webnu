{{--
    Renderiza un panel por cada Company.
    Requiere $qrCompanies (Collection) y $defaultQrCompany.
--}}
@foreach($qrCompanies as $c)
    @php
        $publicUrl = $c->publicUrl();
        $pngUrl = route('admin.qrgenerator', ['company' => $c, 'format' => 'png']);
        $pdfUrlBase = route('admin.qrgenerator', ['company' => $c, 'download' => 1]);
        $printUrlBase = route('admin.qr.print', ['company' => $c]);
        $emailUrl = route('admin.qr.email', ['company' => $c]);
        $isDefault = (int) $c->id === (int) ($defaultQrCompany?->id ?? 0);
    @endphp
    <div class="wn-qr-modal__panel {{ $isDefault ? 'is-active' : '' }}"
         data-qr-panel="{{ $c->id }}"
         data-qr-pdf-base="{{ $pdfUrlBase }}"
         data-qr-print-base="{{ $printUrlBase }}"
         data-qr-email-url="{{ $emailUrl }}"
         data-qr-public-url="{{ $publicUrl }}"
         data-qr-active-step="method">

        <div class="wn-qr-modal__step" data-qr-step="method">
            <div class="wn-qr-modal__art">
                <img src="{{ $pngUrl }}"
                     alt="Código QR de {{ $c->name }}"
                     class="wn-qr-modal__img"
                     loading="lazy"
                     width="240"
                     height="240">
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

            <p class="wn-qr-modal__step-label">¿Qué quieres hacer con el QR?</p>

            <div class="wn-qr-modal__methods">
                <button type="button" class="wn-qr-modal__method" data-qr-method="pdf">
                    <i class="ri ri-file-pdf-2-line"></i><span>PDF</span>
                </button>
                <button type="button" class="wn-qr-modal__method" data-qr-method="print">
                    <i class="ri ri-printer-line"></i><span>Imprimir</span>
                </button>
                <button type="button" class="wn-qr-modal__method" data-qr-method="email">
                    <i class="ri ri-mail-send-line"></i><span>Email</span>
                </button>
                <a href="{{ $publicUrl }}"
                   class="wn-qr-modal__method"
                   data-qr-method="view"
                   target="_blank"
                   rel="noopener">
                    <i class="ri ri-external-link-line"></i><span>Ver</span>
                </a>
            </div>
        </div>

        @include('admin.partials.qr-modal-copies-step')
    </div>
@endforeach
