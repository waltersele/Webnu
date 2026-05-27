@php
    $qrCompanies = collect($available_companies ?? []);
    $defaultQrCompany = $currentCompany ?? $qrCompanies->first();
    $hasMultiple = $qrCompanies->count() > 1;
    $qrUser = auth()->user();
    $qrUserEmail = $qrUser?->email;

    // Hub: si el negocio tiene >1 carta publicada o algún menú publicado,
    // el QR principal apunta al hub público del owner.
    $publishedCompanies = $qrCompanies->filter(function ($c) { return (bool) ($c->enabled ?? false); });
    $hasPublishedMenus = $publishedCompanies->contains(function ($c) {
        try { return $c->menus()->where('enabled', true)->exists(); } catch (\Throwable $e) { return false; }
    });
    $ownerSlugForHub = $qrUser && method_exists($qrUser, 'resolveSlug') ? $qrUser->resolveSlug() : ($qrUser?->slug ?? null);
    $useHub = $ownerSlugForHub && ($publishedCompanies->count() > 1 || $hasPublishedMenus);
    $hubUrl = $useHub ? route('public.hub', ['slug' => $ownerSlugForHub]) : null;
    $hubName = $qrUser?->name ?: ($qrUser?->legal_name ?? 'Mi negocio');
@endphp
@if($defaultQrCompany || $useHub)
<div class="modal fade wn-qr-modal" id="wn-qr-modal" tabindex="-1" aria-labelledby="wn-qr-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="wn-qr-modal-title">
                    <i class="ri ri-qr-code-line me-2"></i>
                    {{ $useHub ? 'Código QR de tu negocio' : 'Código QR de tu carta' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                @if($useHub)
                    @php
                        $hubPngUrl = route('admin.qr.hub.generator', ['format' => 'png']);
                        $hubPdfBase = route('admin.qr.hub.generator', ['download' => 1]);
                        $hubPrintBase = route('admin.qr.hub.print');
                        $hubEmailUrl = route('admin.qr.hub.email');
                    @endphp
                    <div class="wn-qr-modal__panel is-active"
                         data-qr-panel="hub"
                         data-qr-pdf-base="{{ $hubPdfBase }}"
                         data-qr-print-base="{{ $hubPrintBase }}"
                         data-qr-email-url="{{ $hubEmailUrl }}"
                         data-qr-public-url="{{ $hubUrl }}"
                         data-qr-active-step="method">
                        <div class="wn-qr-modal__step" data-qr-step="method">
                            <div class="wn-qr-modal__art">
                                <img src="{{ $hubPngUrl }}"
                                     alt="Código QR de {{ $hubName }}"
                                     class="wn-qr-modal__img"
                                     loading="lazy"
                                     width="240"
                                     height="240">
                            </div>

                            <div class="wn-qr-modal__url">
                                <label class="wn-qr-modal__url-label">URL pública del negocio</label>
                                <div class="input-group">
                                    <input type="text"
                                           class="form-control wn-qr-modal__url-input"
                                           value="{{ $hubUrl }}"
                                           readonly
                                           aria-label="URL pública del negocio">
                                    <button type="button"
                                            class="btn btn-outline-primary wn-qr-modal__copy"
                                            data-copy-url="{{ $hubUrl }}"
                                            title="Copiar enlace">
                                        <i class="ri ri-file-copy-line"></i>
                                        <span class="ms-1 d-none d-sm-inline">Copiar</span>
                                    </button>
                                </div>
                                <p class="wn-qr-modal__url-hint mt-2 mb-0 small text-muted">
                                    <i class="ri ri-information-line"></i>
                                    Este QR lleva al cliente a una página donde elige carta o menú.
                                </p>
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
                                <a href="{{ $hubUrl }}" class="wn-qr-modal__method" data-qr-method="view" target="_blank" rel="noopener">
                                    <i class="ri ri-external-link-line"></i><span>Ver</span>
                                </a>
                            </div>
                        </div>

                        @include('admin.partials.qr-modal-copies-step')
                    </div>

                    @if($qrCompanies->count() > 0)
                        <div class="wn-qr-modal__accordion mt-4">
                            <button type="button"
                                    class="wn-qr-modal__accordion-toggle"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#wn-qr-individuals"
                                    aria-expanded="false"
                                    aria-controls="wn-qr-individuals">
                                <i class="ri ri-arrow-down-s-line wn-qr-modal__accordion-icon"></i>
                                <span>QRs individuales por carta</span>
                                <span class="badge bg-label-secondary ms-2">{{ $qrCompanies->count() }}</span>
                            </button>
                            <div class="collapse wn-qr-modal__accordion-body" id="wn-qr-individuals">
                                <p class="text-muted small mt-3 mb-2">Útil si quieres un QR específico para barra, terraza o una carta concreta.</p>
                                @if($hasMultiple)
                                    <div class="wn-qr-modal__tabs mb-3" role="tablist" aria-label="Seleccionar carta">
                                        @foreach($qrCompanies as $c)
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
                                @include('admin.partials.qr-modal-company-panels', ['qrCompanies' => $qrCompanies, 'defaultQrCompany' => $defaultQrCompany])
                            </div>
                        </div>
                    @endif
                @else
                    @if($hasMultiple)
                        <div class="wn-qr-modal__tabs mb-3" role="tablist" aria-label="Seleccionar carta">
                            @foreach($qrCompanies as $c)
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

                    @include('admin.partials.qr-modal-company-panels', ['qrCompanies' => $qrCompanies, 'defaultQrCompany' => $defaultQrCompany])
                @endif
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('wn-qr-modal');
    if (!modal || modal.dataset.wnQrInit === '1') return;
    modal.dataset.wnQrInit = '1';

    var csrfToken = document.querySelector('meta[name="csrf-token"]');
    csrfToken = csrfToken ? csrfToken.getAttribute('content') : '';

    var USER_EMAIL = @json($qrUserEmail);

    var METHOD_LABELS = {
        pdf: {
            title: 'Cuántos QR por hoja en el PDF',
            help:  'Generaremos un PDF A4 listo para descargar.',
            confirmLabel: 'Abrir PDF',
            confirmIcon: 'ri-file-pdf-2-line'
        },
        print: {
            title: 'Cuántos QR por hoja al imprimir',
            help:  'Abriremos una vista lista para imprimir.',
            confirmLabel: 'Abrir vista de impresión',
            confirmIcon: 'ri-printer-line'
        },
        email: {
            title: 'Cuántos QR por hoja en el email',
            help:  USER_EMAIL ? ('Te enviaremos el PDF a ' + USER_EMAIL + '.') : 'Te enviaremos el PDF por email.',
            confirmLabel: USER_EMAIL ? ('Enviar a ' + USER_EMAIL) : 'Enviar por email',
            confirmIcon: 'ri-mail-send-line'
        }
    };

    function urlWithCopies(base, copies) {
        if (!base) return base;
        if (copies <= 1) return base;
        return base + (base.indexOf('?') === -1 ? '?' : '&') + 'copies=' + copies;
    }

    function showStep(panel, step) {
        panel.dataset.qrActiveStep = step;
        panel.querySelectorAll('[data-qr-step]').forEach(function (el) {
            if (el.closest('[data-qr-panel]') !== panel) return;
            var match = el.getAttribute('data-qr-step') === step;
            if (match) {
                el.removeAttribute('hidden');
            } else {
                el.setAttribute('hidden', '');
            }
        });
        if (step === 'method') {
            setFeedback(panel, '', '');
        }
    }

    function setMethod(panel, method) {
        panel.dataset.qrMethod = method;
        var labels = METHOD_LABELS[method];
        if (!labels) return;
        var titleEl = panel.querySelector('[data-qr-step-title]');
        var helpEl = panel.querySelector('[data-qr-step-help]');
        var confirmLabelEl = panel.querySelector('[data-qr-confirm-label]');
        var confirmIconEl = panel.querySelector('[data-qr-confirm-icon]');
        if (titleEl) titleEl.textContent = labels.title;
        if (helpEl) helpEl.textContent = labels.help;
        if (confirmLabelEl) confirmLabelEl.textContent = labels.confirmLabel;
        if (confirmIconEl) confirmIconEl.className = 'ri me-1 ' + labels.confirmIcon;
    }

    function setCopies(panel, copies) {
        panel.dataset.qrCopies = String(copies);
        panel.querySelectorAll('[data-qr-copies]').forEach(function (card) {
            if (card.closest('[data-qr-panel]') !== panel) return;
            var active = parseInt(card.getAttribute('data-qr-copies'), 10) === copies;
            card.classList.toggle('is-active', active);
            card.setAttribute('aria-checked', active ? 'true' : 'false');
        });
    }

    function setFeedback(panel, message, level) {
        var fb = panel.querySelector('[data-qr-feedback]');
        if (!fb) return;
        fb.textContent = message || '';
        fb.dataset.level = level || '';
    }

    function resetPanel(panel) {
        setCopies(panel, 1);
        showStep(panel, 'method');
        panel.dataset.qrMethod = '';
    }

    function sendEmail(panel, copies) {
        var url = panel.getAttribute('data-qr-email-url');
        if (!url) return;
        var confirmBtn = panel.querySelector('[data-qr-confirm]');
        if (confirmBtn) confirmBtn.disabled = true;
        setFeedback(panel, 'Enviando…', 'info');

        var fd = new FormData();
        fd.append('_token', csrfToken);
        fd.append('copies', String(copies));

        fetch(url, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, json: j }; }); })
        .then(function (res) {
            if (confirmBtn) confirmBtn.disabled = false;
            if (res.ok && res.json && res.json.ok) {
                setFeedback(panel, res.json.message || 'Te lo hemos enviado por email.', 'success');
            } else {
                setFeedback(panel, (res.json && res.json.message) || 'No se pudo enviar.', 'error');
            }
        })
        .catch(function () {
            if (confirmBtn) confirmBtn.disabled = false;
            setFeedback(panel, 'Error de red al enviar el email.', 'error');
        });
    }

    function confirmAction(panel) {
        var method = panel.dataset.qrMethod;
        var copies = parseInt(panel.dataset.qrCopies || '1', 10);
        if (!method) return;

        if (method === 'pdf') {
            window.open(urlWithCopies(panel.getAttribute('data-qr-pdf-base'), copies), '_blank', 'noopener');
            closeModal();
            return;
        }
        if (method === 'print') {
            window.open(urlWithCopies(panel.getAttribute('data-qr-print-base'), copies), '_blank', 'noopener');
            closeModal();
            return;
        }
        if (method === 'email') {
            sendEmail(panel, copies);
        }
    }

    function closeModal() {
        if (window.bootstrap && window.bootstrap.Modal) {
            var instance = window.bootstrap.Modal.getInstance(modal);
            if (instance) instance.hide();
        }
    }

    modal.querySelectorAll('[data-qr-panel]').forEach(resetPanel);

    modal.addEventListener('hidden.bs.modal', function () {
        modal.querySelectorAll('[data-qr-panel]').forEach(resetPanel);
    });

    modal.addEventListener('click', function (e) {
        var tab = e.target.closest('[data-qr-tab]');
        if (tab) {
            var key = tab.getAttribute('data-qr-tab');
            // Solo cambiamos tabs dentro del mismo contenedor (acordeón individual o legacy)
            var scope = tab.closest('.wn-qr-modal__accordion-body, .modal-body');
            if (!scope) scope = modal;
            scope.querySelectorAll('[data-qr-tab]').forEach(function (t) {
                var active = t.getAttribute('data-qr-tab') === key;
                t.classList.toggle('is-active', active);
                t.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            // Solo activamos paneles "company" (no el panel "hub")
            scope.querySelectorAll('[data-qr-panel]').forEach(function (p) {
                if (p.getAttribute('data-qr-panel') === 'hub') return;
                var isActive = p.getAttribute('data-qr-panel') === key;
                p.classList.toggle('is-active', isActive);
                if (isActive) resetPanel(p);
            });
            return;
        }

        var copyBtn = e.target.closest('.wn-qr-modal__copy');
        if (copyBtn) {
            var copyUrl = copyBtn.getAttribute('data-copy-url');
            if (!copyUrl) return;
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
                    navigator.clipboard.writeText(copyUrl).then(done).catch(function () {
                        var input = copyBtn.closest('.input-group').querySelector('input');
                        if (input) { input.select(); document.execCommand('copy'); done(); }
                    });
                } else {
                    var input = copyBtn.closest('.input-group').querySelector('input');
                    if (input) { input.select(); document.execCommand('copy'); done(); }
                }
            } catch (err) {}
            return;
        }

        var methodBtn = e.target.closest('[data-qr-method]');
        if (methodBtn) {
            var method = methodBtn.getAttribute('data-qr-method');
            if (method === 'view') return; // anchor nativo
            e.preventDefault();
            var panel = methodBtn.closest('[data-qr-panel]');
            if (!panel) return;
            setMethod(panel, method);
            setCopies(panel, 1);
            setFeedback(panel, '', '');
            showStep(panel, 'copies');
            return;
        }

        var copyCard = e.target.closest('[data-qr-copies]');
        if (copyCard) {
            var panel2 = copyCard.closest('[data-qr-panel]');
            if (!panel2) return;
            setCopies(panel2, parseInt(copyCard.getAttribute('data-qr-copies'), 10) || 1);
            return;
        }

        var backBtn = e.target.closest('[data-qr-back]');
        if (backBtn) {
            var panel3 = backBtn.closest('[data-qr-panel]');
            if (!panel3) return;
            setFeedback(panel3, '', '');
            showStep(panel3, 'method');
            return;
        }

        var confirmBtn = e.target.closest('[data-qr-confirm]');
        if (confirmBtn) {
            e.preventDefault();
            var panel4 = confirmBtn.closest('[data-qr-panel]');
            if (!panel4) return;
            confirmAction(panel4);
            return;
        }
    });
})();
</script>
@endif
