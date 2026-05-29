/**
 * Preview en vivo de URLs públicas (registro, onboarding, admin).
 */
(function (global) {
    'use strict';

    function slugify(value) {
        return (value || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    }

    function debounce(fn, ms) {
        var timer;
        return function () {
            var args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                fn.apply(null, args);
            }, ms);
        };
    }

    function initPreview(root) {
        if (!root) {
            return;
        }

        var mode = root.getAttribute('data-url-mode') || 'simple';
        var host = root.getAttribute('data-url-host') || 'webnu.es';
        var checkUrl = root.getAttribute('data-check-url') || '';
        var companyId = root.getAttribute('data-company-id') || '';
        var userId = root.getAttribute('data-user-id') || '';
        var nameInput = root.querySelector('[data-url-name-input]');
        var slugInput = root.querySelector('[data-url-slug-input]');
        var ownerInput = root.querySelector('[data-url-owner-input]');
        var previewEl = root.querySelector('[data-url-preview-path]');
        var statusEl = root.querySelector('[data-url-preview-status]');

        if (!previewEl) {
            return;
        }

        function buildPath() {
            var slug = slugInput ? slugify(slugInput.value) : slugify(nameInput ? nameInput.value : '');
            if (!slug) {
                slug = 'tu-carta';
            }
            if (mode === 'nested') {
                var owner = ownerInput ? slugify(ownerInput.value) : 'tu-negocio';
                return '@' + owner;
            }
            if (mode === 'menu-simple') {
                var carta = root.getAttribute('data-company-slug') || 'tu-carta';
                return carta + '/' + slug;
            }
            if (mode === 'menu-nested') {
                var ownerSlug = ownerInput ? slugify(ownerInput.value) : 'tu-negocio';
                var companySlug = root.getAttribute('data-company-slug') || 'tu-carta';
                return companySlug + '/' + slug;
            }
            return slug;
        }

        function render() {
            previewEl.textContent = host + '/' + buildPath();
        }

        var checkAvailability = debounce(function () {
            if (!checkUrl || !statusEl) {
                return;
            }
            var path = buildPath();
            if (path.indexOf('tu-carta') !== -1 || path.indexOf('tu-negocio') !== -1) {
                statusEl.textContent = '';
                statusEl.className = 'wn-url-preview__status';
                return;
            }
            var url = checkUrl + '?path=' + encodeURIComponent(path);
            if (companyId) {
                url += '&company_id=' + encodeURIComponent(companyId);
            }
            if (userId) {
                url += '&user_id=' + encodeURIComponent(userId);
            }
            fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
                .then(function (r) {
                    return r.json();
                })
                .then(function (data) {
                    if (data.available) {
                        statusEl.textContent = 'URL disponible';
                        statusEl.className = 'wn-url-preview__status wn-url-preview__status--ok';
                    } else {
                        statusEl.textContent = data.message || 'URL no disponible';
                        statusEl.className = 'wn-url-preview__status wn-url-preview__status--error';
                    }
                })
                .catch(function () {
                    statusEl.textContent = '';
                });
        }, 400);

        function onInput() {
            if (nameInput && slugInput && document.activeElement === nameInput) {
                slugInput.value = slugify(nameInput.value);
            }
            render();
            checkAvailability();
        }

        [nameInput, slugInput, ownerInput].forEach(function (el) {
            if (el) {
                el.addEventListener('input', onInput);
            }
        });

        render();
    }

    function boot() {
        document.querySelectorAll('[data-webnu-url-preview]').forEach(initPreview);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    global.WebnuPublicUrlPreview = { slugify: slugify, initPreview: initPreview };
})(typeof window !== 'undefined' ? window : this);
