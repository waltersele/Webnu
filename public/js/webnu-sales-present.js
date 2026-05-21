(function () {
    'use strict';

    var cfg = window.WebnuSalesPresent || {};
    var iframe = document.getElementById('wn-sales-present-frame');
    var templateChips = document.getElementById('wn-sales-templates');
    var presetChips = document.getElementById('wn-sales-presets');
    var saveBtn = document.getElementById('wn-sales-save-design');
    var debounceTimer = null;

    var state = {
        template: cfg.template || 'lumiere',
        colors: {}
    };

    function readColorsFromInputs() {
        document.querySelectorAll('.wn-sales-theme-color').forEach(function (input) {
            var key = input.getAttribute('data-key');
            if (key && /^#[0-9A-Fa-f]{6}$/i.test(input.value)) {
                state.colors[key] = input.value.toLowerCase();
            }
        });
    }

    function buildPreviewUrl() {
        var base = (cfg.previewUrl || '').split('?')[0];
        var params = new URLSearchParams();
        params.set('sales_demo', '1');
        params.set('t', String(Date.now()));

        if (state.template) {
            params.set('preview_template', state.template);
        }

        Object.keys(state.colors).forEach(function (key) {
            params.set('theme_' + key, state.colors[key]);
        });

        return base + '?' + params.toString();
    }

    function refreshPreview() {
        if (!iframe) {
            return;
        }
        iframe.src = buildPreviewUrl();
    }

    function schedulePreview() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(refreshPreview, 300);
    }

    function setActiveTemplateChip(template) {
        if (!templateChips) {
            return;
        }
        templateChips.querySelectorAll('[data-template]').forEach(function (btn) {
            btn.classList.toggle('is-active', btn.getAttribute('data-template') === template);
        });
    }

    if (templateChips) {
        templateChips.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-template]');
            if (!btn) {
                return;
            }
            state.template = btn.getAttribute('data-template');
            setActiveTemplateChip(state.template);
            schedulePreview();
        });
    }

    if (presetChips) {
        presetChips.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-preset-colors]');
            if (!btn) {
                return;
            }
            var template = btn.getAttribute('data-preset-template');
            var colorsRaw = btn.getAttribute('data-preset-colors');
            try {
                var colors = JSON.parse(colorsRaw);
                if (template) {
                    state.template = template;
                    setActiveTemplateChip(state.template);
                }
                Object.keys(colors).forEach(function (key) {
                    state.colors[key] = colors[key];
                    var input = document.querySelector('.wn-sales-theme-color[data-key="' + key + '"]');
                    if (input) {
                        input.value = colors[key];
                    }
                });
                schedulePreview();
            } catch (err) {
                /* ignore */
            }
        });
    }

    document.querySelectorAll('.wn-sales-theme-color').forEach(function (input) {
        input.addEventListener('input', function () {
            readColorsFromInputs();
            schedulePreview();
        });
    });

    if (saveBtn) {
        saveBtn.addEventListener('click', function () {
            readColorsFromInputs();
            saveBtn.disabled = true;

            var body = new URLSearchParams();
            body.set('_method', 'PUT');
            body.set('template', state.template);
            Object.keys(state.colors).forEach(function (key) {
                body.set('theme_' + key, state.colors[key]);
            });

            fetch(cfg.saveUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': cfg.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: body.toString(),
                credentials: 'same-origin'
            })
                .then(function (res) {
                    if (!res.ok) {
                        throw new Error('save failed');
                    }
                    saveBtn.textContent = 'Guardado';
                    setTimeout(function () {
                        saveBtn.textContent = 'Guardar diseño';
                        saveBtn.disabled = false;
                    }, 1500);
                })
                .catch(function () {
                    saveBtn.disabled = false;
                    alert('No se pudo guardar el diseño. Inténtalo de nuevo.');
                });
        });
    }

    readColorsFromInputs();
    setActiveTemplateChip(state.template);
    refreshPreview();
})();
