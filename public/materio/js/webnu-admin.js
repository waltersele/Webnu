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
        document.querySelectorAll('.webnu-section-card__header').forEach(function (head) {
            head.addEventListener('click', function (e) {
                if (e.target.closest('.webnu-drag-handle, .btn, .dropdown-menu, .product-modify, .product-delete')) {
                    return;
                }
                var section = head.closest('.webnu-section-card');
                if (section) {
                    section.classList.toggle('is-open');
                }
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
            stack.classList.remove('webnu-menu-view--grid', 'webnu-menu-view--list');
            stack.classList.add(view === 'list' ? 'webnu-menu-view--list' : 'webnu-menu-view--grid');
            localStorage.setItem(storageKey, view);

            document.querySelectorAll('[data-menu-view]').forEach(function (btn) {
                var active = btn.getAttribute('data-menu-view') === view;
                btn.classList.toggle('btn-primary', active);
                btn.classList.toggle('btn-label-secondary', !active);
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

    function init() {
        initCompanySelect();
        initProductSaleTypeToggles();
        initProductEnabledToggles();
        initSectionAccordion();
        initMenuViewToggle();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
