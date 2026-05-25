(function () {
    'use strict';

    function initCompanySelect() {
        var select = document.getElementById('company_selection');
        var form = document.getElementById('company-selection-form');
        if (select && form) {
            select.addEventListener('change', function () {
                form.submit();
            });
        }
    }

    function syncWeightLabelWrap(weightCheckbox) {
        if (!weightCheckbox || weightCheckbox.getAttribute('data-sale-type') !== 'weight') {
            return;
        }

        var option = weightCheckbox.closest('.webnu-sale-option-weight');
        var wrap = option ? option.querySelector('.webnu-weight-label-wrap') : null;
        if (!wrap) {
            return;
        }

        if (weightCheckbox.checked) {
            wrap.classList.remove('hidden');
        } else {
            wrap.classList.add('hidden');
            var field = wrap.querySelector('input[type="text"]');
            if (field) {
                field.value = '';
            }
        }
    }

    function initProductSaleTypeToggles() {
        document.querySelectorAll('.product-sale-type[data-sale-type="weight"]').forEach(syncWeightLabelWrap);

        document.addEventListener('change', function (e) {
            var input = e.target;
            if (!input.classList || !input.classList.contains('product-sale-type')) {
                return;
            }

            var scope = input.closest('.webnu-product-modal')
                || input.closest('.webnu-product-edit')
                || input.closest('.webnu-sale-options')
                || input.closest('form');

            if (input.checked && scope) {
                scope.querySelectorAll('.product-sale-type').forEach(function (other) {
                    if (other !== input) {
                        other.checked = false;
                    }
                });
            }

            if (input.getAttribute('data-sale-type') === 'weight') {
                syncWeightLabelWrap(input);
            } else if (!input.checked) {
                var weightCb = scope
                    ? scope.querySelector('.product-sale-type[data-sale-type="weight"]')
                    : null;
                if (weightCb) {
                    syncWeightLabelWrap(weightCb);
                }
            }
        });
    }

    function initProductEnabledToggles() {
        document.addEventListener('change', function (e) {
            var input = e.target;
            if (!input.classList || !input.classList.contains('product-enabled-toggle')) {
                return;
            }

            var url = input.getAttribute('data-url');
            var token = input.getAttribute('data-token');
            if (!url || !token) {
                return;
            }

            input.disabled = true;

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                body: JSON.stringify({ enabled: input.checked ? 1 : 0 }),
            })
                .then(function (res) {
                    if (!res.ok) {
                        throw new Error('toggle failed');
                    }
                    return res.json();
                })
                .then(function () {
                    input.disabled = false;
                })
                .catch(function () {
                    input.checked = !input.checked;
                    input.disabled = false;
                    if (typeof toastr !== 'undefined') {
                        toastr.error('No se pudo actualizar la visibilidad del plato.');
                    }
                });
        });
    }

    function initSectionAccordion() {
        function toggleSection(head) {
            var section = head.closest('.webnu-section-card');
            if (!section) return;
            section.classList.toggle('is-open');
            var toggleBtn = head.querySelector('.webnu-section-toggle');
            if (toggleBtn) {
                toggleBtn.setAttribute('aria-expanded', section.classList.contains('is-open') ? 'true' : 'false');
            }
        }

        document.querySelectorAll('.webnu-section-card__header').forEach(function (head) {
            // Inicializar estado aria del toggle
            var toggleBtn = head.querySelector('.webnu-section-toggle');
            if (toggleBtn) {
                var section = head.closest('.webnu-section-card');
                toggleBtn.setAttribute('aria-expanded', section && section.classList.contains('is-open') ? 'true' : 'false');
                toggleBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    toggleSection(head);
                });
            }

            head.addEventListener('click', function (e) {
                // Si el click viene de un botón funcional (añadir, drag, dropdown), no toggle.
                if (e.target.closest('.webnu-drag-handle, .dropdown-menu, .product-modify, .product-delete, .product-add-btn')) {
                    return;
                }
                // Pero sí toleramos el propio toggle button (ya lo gestiona su handler).
                if (e.target.closest('.webnu-section-toggle')) {
                    return;
                }
                // Para cualquier otro botón dentro del header, no togglear (evita choques).
                if (e.target.closest('.btn') && !e.target.closest('.webnu-section-toggle')) {
                    return;
                }
                toggleSection(head);
            });
        });
    }

    function initMenuViewToggle() {
        var stack = document.getElementById('sortable-section');
        if (!stack) {
            return;
        }

        var storageKey = 'webnu-menu-view';
        var saved = localStorage.getItem(storageKey) || 'grid';

        function applyView(view) {
            var resolved = view === 'list' ? 'list' : 'grid';
            stack.classList.remove('webnu-menu-view--grid', 'webnu-menu-view--list');
            stack.classList.add('webnu-menu-view--' + resolved);
            try { localStorage.setItem(storageKey, resolved); } catch (e) {}

            document.querySelectorAll('[data-menu-view]').forEach(function (btn) {
                var active = btn.getAttribute('data-menu-view') === resolved;
                btn.classList.toggle('is-active', active);
                btn.setAttribute('aria-pressed', active ? 'true' : 'false');
                // Compatibilidad hacia atrás con el switch antiguo (btn-primary / btn-label-secondary)
                if (btn.classList.contains('btn')) {
                    btn.classList.toggle('btn-primary', active);
                    btn.classList.toggle('btn-label-secondary', !active);
                }
            });
        }

        applyView(saved);

        document.querySelectorAll('[data-menu-view]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                applyView(btn.getAttribute('data-menu-view'));
            });
        });
    }

    window.initWebnuMenuView = initMenuViewToggle;
    window.syncWeightLabelWrap = syncWeightLabelWrap;

    function initMiCartaTabs() {
        var root = document.querySelector('[data-mi-carta]');
        if (!root) return;

        var tabs = Array.prototype.slice.call(root.querySelectorAll('.wn-tab-bar__btn[data-tab-target]'));
        var panels = Array.prototype.slice.call(root.querySelectorAll('.wn-tab-panel[data-tab]'));
        if (!tabs.length || !panels.length) return;

        var storageKey = 'webnu-mi-carta-tab';
        var validKeys = tabs.map(function (b) { return b.getAttribute('data-tab-target'); });

        function getInitial() {
            try {
                var hash = (window.location.hash || '').replace(/^#tab-/, '');
                if (hash && validKeys.indexOf(hash) !== -1) return hash;
                var saved = localStorage.getItem(storageKey);
                if (saved && validKeys.indexOf(saved) !== -1) return saved;
            } catch (e) {}
            return validKeys[0];
        }

        function activate(key, opts) {
            opts = opts || {};
            if (validKeys.indexOf(key) === -1) key = validKeys[0];

            tabs.forEach(function (btn) {
                var active = btn.getAttribute('data-tab-target') === key;
                btn.classList.toggle('is-active', active);
                btn.setAttribute('aria-selected', active ? 'true' : 'false');
                btn.setAttribute('tabindex', active ? '0' : '-1');
            });

            panels.forEach(function (panel) {
                var active = panel.getAttribute('data-tab') === key;
                panel.classList.toggle('is-active', active);
            });

            try { localStorage.setItem(storageKey, key); } catch (e) {}

            if (opts.focus) {
                var activeBtn = root.querySelector('.wn-tab-bar__btn.is-active');
                if (activeBtn) activeBtn.focus();
            }
        }

        tabs.forEach(function (btn, idx) {
            btn.addEventListener('click', function () {
                activate(btn.getAttribute('data-tab-target'));
            });

            btn.addEventListener('keydown', function (e) {
                if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft' && e.key !== 'Home' && e.key !== 'End') return;
                e.preventDefault();
                var nextIdx = idx;
                if (e.key === 'ArrowRight') nextIdx = (idx + 1) % tabs.length;
                if (e.key === 'ArrowLeft')  nextIdx = (idx - 1 + tabs.length) % tabs.length;
                if (e.key === 'Home')       nextIdx = 0;
                if (e.key === 'End')        nextIdx = tabs.length - 1;
                activate(tabs[nextIdx].getAttribute('data-tab-target'), { focus: true });
            });
        });

        document.addEventListener('click', function (e) {
            var link = e.target.closest('[data-mi-carta-tab]');
            if (!link) return;
            var key = link.getAttribute('data-mi-carta-tab');
            if (validKeys.indexOf(key) === -1) return;
            e.preventDefault();
            activate(key);
            try {
                root.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (err) {
                root.scrollIntoView();
            }
        });

        activate(getInitial());
    }

    window.initMiCartaTabs = initMiCartaTabs;

    /**
     * Imprime la carta pública a través de un iframe oculto.
     * Gestiona timeouts, cross-origin y avisos al usuario.
     */
    window.WebnuPrintMenu = function WebnuPrintMenu(options) {
        options = options || {};
        var frameName = options.frameName || 'printMenu';
        var win = (window.frames && window.frames[frameName]) || null;
        var iframeEl = document.querySelector('iframe[name="' + frameName + '"]');

        function safePrint() {
            try {
                if (win && typeof win.focus === 'function') {
                    win.focus();
                }
                if (win && typeof win.print === 'function') {
                    win.print();
                    return true;
                }
            } catch (err) {
                // Cross-origin u otro error: fallback abajo
                console.warn('[Webnu] Print iframe falló, abro nueva pestaña.', err);
            }
            return false;
        }

        // Si el iframe no existe, abrimos la carta en nueva pestaña
        if (!iframeEl || !win) {
            var fallbackUrl = iframeEl && iframeEl.getAttribute('src');
            if (fallbackUrl) {
                window.open(fallbackUrl, '_blank', 'noopener,noreferrer');
                return;
            }
            window.alert('No se puede imprimir ahora. Inténtalo en unos segundos.');
            return;
        }

        // Si todavía no ha cargado, esperamos a load (máx. 4s)
        var ready = false;
        try {
            ready = iframeEl.contentDocument && iframeEl.contentDocument.readyState === 'complete';
        } catch (e) {
            ready = false;
        }

        if (ready) {
            if (!safePrint() && iframeEl.getAttribute('src')) {
                window.open(iframeEl.getAttribute('src'), '_blank', 'noopener,noreferrer');
            }
            return;
        }

        var done = false;
        var timeout = setTimeout(function () {
            if (done) return;
            done = true;
            if (!safePrint() && iframeEl.getAttribute('src')) {
                window.open(iframeEl.getAttribute('src'), '_blank', 'noopener,noreferrer');
            }
        }, 4000);

        iframeEl.addEventListener('load', function onLoad() {
            iframeEl.removeEventListener('load', onLoad);
            if (done) return;
            done = true;
            clearTimeout(timeout);
            setTimeout(function () {
                if (!safePrint() && iframeEl.getAttribute('src')) {
                    window.open(iframeEl.getAttribute('src'), '_blank', 'noopener,noreferrer');
                }
            }, 50);
        }, { once: true });
    };

    function initListRowEdit() {
        document.addEventListener('click', function (e) {
            var row = e.target.closest('.wn-list-row');
            if (!row) return;
            if (e.target.closest('.product-enabled-toggle, .product-delete, .product-modify, .webnu-drag-handle, a, button, label, input, .form-check, .dropdown')) return;

            var url = row.getAttribute('data-edit-url');
            if (!url) return;
            window.location.href = url;
        });

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            var row = e.target.closest && e.target.closest('.wn-list-row');
            if (!row || row !== e.target) return;
            var url = row.getAttribute('data-edit-url');
            if (!url) return;
            e.preventDefault();
            window.location.href = url;
        });
    }

    function initCompanyEnabledToggles() {
        document.addEventListener('change', function (e) {
            var input = e.target;
            if (!input || !input.matches || !input.matches('[data-company-toggle]')) return;

            var url = input.getAttribute('data-url');
            var token = input.getAttribute('data-token');
            if (!url) return;

            var card = input.closest('.wn-company-card');
            var scope = input.closest('[data-company-toggle-scope]') || card || document;
            var statusEl = scope.querySelector('[data-company-toggle-status], .wn-company-toggle__status');
            var subEl = scope.querySelector('[data-company-toggle-sub]') ||
                (card ? card.querySelector('.wn-company-toggle__text small') : null);

            var labelOn = input.getAttribute('data-label-on') || 'Publicada';
            var labelOff = input.getAttribute('data-label-off') || 'Borrador';
            var subOn = input.getAttribute('data-sub-on') || 'Visible para tus clientes';
            var subOff = input.getAttribute('data-sub-off') || 'No accesible públicamente';
            var newState = input.checked;

            input.disabled = true;

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ enabled: newState ? 1 : 0 })
            })
            .then(function (r) {
                if (!r.ok) throw new Error('toggle failed');
                return r.json();
            })
            .then(function (data) {
                input.disabled = false;
                var enabled = !!data.enabled;
                if (card) {
                    card.classList.toggle('is-published', enabled);
                    card.classList.toggle('is-draft', !enabled);
                }
                if (statusEl) statusEl.textContent = enabled ? labelOn : labelOff;
                if (subEl) subEl.textContent = enabled ? subOn : subOff;
                if (typeof toastr !== 'undefined') {
                    toastr.success(enabled ? 'Carta publicada' : 'Carta despublicada');
                }
            })
            .catch(function () {
                input.checked = !newState;
                input.disabled = false;
                if (typeof toastr !== 'undefined') {
                    toastr.error('No se pudo cambiar la visibilidad. Inténtalo de nuevo.');
                } else {
                    alert('No se pudo cambiar la visibilidad. Inténtalo de nuevo.');
                }
            });
        });
    }

    function initProfileWizardDismiss() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-wizard-dismiss]');
            if (!btn) return;
            e.preventDefault();

            var url = btn.getAttribute('data-dismiss-url');
            var token = btn.getAttribute('data-csrf');
            var card = btn.closest('[data-wizard]');

            if (card) {
                card.style.transition = 'opacity 240ms ease, transform 240ms ease, max-height 320ms ease';
                card.style.opacity = '0';
                card.style.transform = 'translateY(-8px)';
                setTimeout(function () {
                    if (card.parentNode) card.parentNode.removeChild(card);
                }, 260);
            }

            if (!url) return;

            try {
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                }).catch(function () {});
            } catch (err) {}
        });
    }

    function init() {
        initCompanySelect();
        initProductSaleTypeToggles();
        initProductEnabledToggles();
        initSectionAccordion();
        initMenuViewToggle();
        initMiCartaTabs();
        initProfileWizardDismiss();
        initListRowEdit();
        initCompanyEnabledToggles();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
