(function () {
    'use strict';

    function parseMaxExtra(container) {
        var raw = container.getAttribute('data-max-extra-locales');
        if (raw === null || raw === '') {
            return null;
        }
        var n = parseInt(raw, 10);
        return isNaN(n) ? null : n;
    }

    function applyExtraLimit(container) {
        var max = parseMaxExtra(container);
        if (max === null) {
            return;
        }

        var boxes = container.querySelectorAll('input[type="checkbox"][name="locales[]"]');
        var checked = 0;
        boxes.forEach(function (input) {
            if (input.checked && !input.disabled) {
                checked++;
            }
        });

        boxes.forEach(function (input) {
            var label = input.closest('.wn-onb-locale--extra, .wn-locale-extra-row, label[data-locale-code]');
            if (!label || label.hidden) {
                return;
            }

            var lock = !input.checked && checked >= max;
            input.disabled = lock;
            label.classList.toggle('is-locked', lock);

            var badge = label.querySelector('.wn-onb-locale__plus-badge');
            if (badge) {
                badge.hidden = !lock;
            }
        });
    }

    function syncBaseLocaleExtras(form) {
        if (!form) {
            return;
        }

        var select = form.querySelector('select[data-locale-base-select]');
        var selectedRadio = form.querySelector('input[data-locale-base-radio]:checked');
        var base = select ? select.value : (selectedRadio ? selectedRadio.value : null);
        var extrasWrap = form.querySelector('[data-locale-role="extras"], #onb-locale-extras, [data-locale-limit]');
        if (!extrasWrap) {
            return;
        }

        extrasWrap.querySelectorAll('[data-locale-code]').forEach(function (label) {
            var code = label.getAttribute('data-locale-code');
            var hide = base && code === base;
            label.hidden = hide;
            if (hide) {
                var input = label.querySelector('input[type="checkbox"]');
                if (input) {
                    input.checked = false;
                }
            }
        });

        applyExtraLimit(extrasWrap);
    }

    function initContainer(container) {
        container.addEventListener('change', function (event) {
            if (event.target.matches('input[type="checkbox"][name="locales[]"]')) {
                applyExtraLimit(container);
            }
        });
        applyExtraLimit(container);
    }

    document.querySelectorAll('[data-locale-limit]').forEach(initContainer);

    function syncSkipFormDefaultLocale(form) {
        var skipForm = document.getElementById('onb-locales-skip-form');
        if (!skipForm || !form || form.id !== 'onb-locales-form') {
            return;
        }
        var hidden = skipForm.querySelector('[data-locale-skip-default]');
        var select = form.querySelector('select[data-locale-base-select]');
        if (hidden && select) {
            hidden.value = select.value;
        }
    }

    document.querySelectorAll('#onb-locales-form, form[data-locale-form]').forEach(function (form) {
        form.addEventListener('change', function (event) {
            if (event.target.matches('select[data-locale-base-select], input[data-locale-base-radio]')) {
                syncBaseLocaleExtras(form);
                syncSkipFormDefaultLocale(form);
            }
        });
        syncBaseLocaleExtras(form);
        syncSkipFormDefaultLocale(form);
    });
})();
