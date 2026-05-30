(function (global) {
    'use strict';

    function parseJson(el) {
        if (!el || !el.textContent) {
            return null;
        }
        try {
            return JSON.parse(el.textContent);
        } catch (e) {
            return null;
        }
    }

    function storageKey(companyId) {
        return 'webnu_favs_' + companyId;
    }

    function loadIds(companyId) {
        try {
            var raw = localStorage.getItem(storageKey(companyId));
            if (!raw) {
                return [];
            }
            var parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed.map(String) : [];
        } catch (e) {
            return [];
        }
    }

    function saveIds(companyId, ids) {
        try {
            localStorage.setItem(storageKey(companyId), JSON.stringify(ids));
        } catch (e) {
            /* ignore quota errors */
        }
    }

    function init() {
        var root = document.getElementById('webnu-favorites-root');
        var catalogEl = document.getElementById('webnu-favorites-catalog');
        if (!root || !catalogEl) {
            return;
        }

        var catalog = parseJson(catalogEl);
        if (!catalog || !catalog.companyId) {
            return;
        }

        var labels = {};
        try {
            labels = JSON.parse(root.getAttribute('data-ui-labels') || '{}');
        } catch (e) {
            labels = {};
        }

        var companyId = catalog.companyId;
        var products = catalog.products || {};
        var defaultLocale = catalog.defaultLocale || 'es';
        var defaultLocaleLabel = labels.defaultLocaleLabel || defaultLocale;
        var originalLabelTemplate = labels.originalLabel || 'En :locale';
        var ids = loadIds(companyId);
        var waiterMode = false;

        var countEl = root.querySelector('[data-fav-count]');
        var listEl = root.querySelector('[data-fav-list]');
        var emptyEl = root.querySelector('[data-fav-empty]');
        var panelEl = root.querySelector('[data-fav-panel]');
        var openBtn = root.querySelector('[data-fav-open]');
        var waiterBtn = root.querySelector('[data-fav-waiter-mode]');
        var sheetEl = root.querySelector('.wn-favorites-panel__sheet');

        function isFavorited(id) {
            return ids.indexOf(String(id)) !== -1;
        }

        function syncButtons() {
            document.querySelectorAll('[data-fav-toggle][data-product-id]').forEach(function (btn) {
                var id = btn.getAttribute('data-product-id');
                var on = isFavorited(id);
                btn.classList.toggle('is-favorited', on);
                btn.setAttribute('aria-pressed', on ? 'true' : 'false');
                var icon = btn.querySelector('i');
                if (icon) {
                    icon.classList.toggle('far', !on);
                    icon.classList.toggle('fa-heart', true);
                    icon.classList.toggle('fas', on);
                }
                var svgHeart = btn.querySelector('.wn-svg-heart');
                if (svgHeart) {
                    svgHeart.classList.toggle('is-filled', on);
                }
            });
        }

        function updateBadge() {
            var count = ids.length;
            if (countEl) {
                countEl.textContent = String(count);
                countEl.hidden = count === 0;
            }
        }

        function renderList() {
            if (!listEl) {
                return;
            }
            listEl.innerHTML = '';

            var hasItems = false;
            ids.forEach(function (id) {
                var item = products[id];
                if (!item) {
                    return;
                }
                hasItems = true;

                var li = document.createElement('li');
                li.className = 'wn-favorites-item' + (waiterMode ? ' wn-favorites-item--waiter' : '');

                var imgHtml = item.imageUrl
                    ? '<img class="wn-favorites-item__img" src="' + item.imageUrl + '" alt="" loading="lazy">'
                    : '<div class="wn-favorites-item__img wn-favorites-item__img--placeholder"><i class="fas fa-utensils"></i></div>';

                var originalLine = '';
                if (item.nameOriginal && item.nameOriginal !== item.nameLocale) {
                    var origLabel = originalLabelTemplate.replace(':locale', defaultLocaleLabel);
                    originalLine = '<p class="wn-favorites-item__original"><span class="wn-favorites-item__original-label">' + origLabel + '</span> ' + escapeHtml(item.nameOriginal) + '</p>';
                }

                var priceLine = item.priceLabel
                    ? '<p class="wn-favorites-item__price">' + escapeHtml(item.priceLabel) + '</p>'
                    : '';

                li.innerHTML =
                    '<div class="wn-favorites-item__media">' + imgHtml + '</div>' +
                    '<div class="wn-favorites-item__body">' +
                        '<p class="wn-favorites-item__name">' + escapeHtml(item.nameLocale) + '</p>' +
                        originalLine +
                        priceLine +
                    '</div>' +
                    '<button type="button" class="wn-favorites-item__remove" data-fav-remove="' + id + '" aria-label="' + escapeHtml(labels.remove || 'Quitar') + '">' +
                        '<i class="fas fa-times"></i>' +
                    '</button>';

                listEl.appendChild(li);
            });

            if (emptyEl) {
                emptyEl.hidden = hasItems;
            }
            if (listEl) {
                listEl.hidden = !hasItems;
            }
        }

        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function toggle(id) {
            id = String(id);
            var idx = ids.indexOf(id);
            if (idx === -1) {
                ids.push(id);
            } else {
                ids.splice(idx, 1);
            }
            saveIds(companyId, ids);
            syncButtons();
            updateBadge();
            renderList();
        }

        function openPanel() {
            if (!panelEl) {
                return;
            }
            panelEl.hidden = false;
            panelEl.setAttribute('aria-hidden', 'false');
            if (openBtn) {
                openBtn.setAttribute('aria-expanded', 'true');
            }
            document.body.classList.add('wn-favorites-open');
        }

        function closePanel() {
            if (!panelEl) {
                return;
            }
            panelEl.hidden = true;
            panelEl.setAttribute('aria-hidden', 'true');
            if (openBtn) {
                openBtn.setAttribute('aria-expanded', 'false');
            }
            document.body.classList.remove('wn-favorites-open');
            setWaiterMode(false);
        }

        function setWaiterMode(on) {
            waiterMode = !!on;
            if (sheetEl) {
                sheetEl.classList.toggle('wn-favorites-panel__sheet--waiter', waiterMode);
            }
            if (waiterBtn) {
                waiterBtn.textContent = waiterMode
                    ? (labels.closeWaiter || 'Volver')
                    : (labels.showWaiter || 'Mostrar al camarero');
            }
            renderList();
        }

        document.addEventListener('click', function (e) {
            var toggleBtn = e.target.closest('[data-fav-toggle]');
            if (toggleBtn) {
                e.preventDefault();
                e.stopPropagation();
                var pid = toggleBtn.getAttribute('data-product-id');
                if (pid) {
                    toggle(pid);
                }
                return;
            }

            var removeBtn = e.target.closest('[data-fav-remove]');
            if (removeBtn) {
                e.preventDefault();
                toggle(removeBtn.getAttribute('data-fav-remove'));
                return;
            }

            if (e.target.closest('[data-fav-open]')) {
                e.preventDefault();
                openPanel();
                return;
            }

            if (e.target.closest('[data-fav-close]')) {
                e.preventDefault();
                closePanel();
                return;
            }

            if (e.target.closest('[data-fav-waiter-mode]')) {
                e.preventDefault();
                setWaiterMode(!waiterMode);
            }
        });

        syncButtons();
        updateBadge();
        renderList();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    global.WebnuMenuFavorites = { init: init };
})(typeof window !== 'undefined' ? window : this);
