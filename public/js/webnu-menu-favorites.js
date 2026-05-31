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

    function templateHtml(id) {
        var tpl = document.getElementById(id);
        return tpl ? tpl.innerHTML.trim() : '';
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

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
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
        var menuLocale = catalog.menuLocale || defaultLocale;
        var defaultLocaleLabel = labels.defaultLocaleLabel || defaultLocale;
        var menuLocaleLabel = labels.menuLocaleLabel || menuLocale;
        var ids = loadIds(companyId);
        var removeIcon = templateHtml('wn-fav-icon-remove');
        var placeholderIcon = templateHtml('wn-fav-icon-placeholder');

        var countEl = root.querySelector('[data-fav-count]');
        var listEl = root.querySelector('[data-fav-list]');
        var emptyEl = root.querySelector('[data-fav-empty]');
        var panelEl = root.querySelector('[data-fav-panel]');
        var openBtn = root.querySelector('[data-fav-open]');

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

        function showBilingual(item) {
            if (!item) {
                return false;
            }
            if (menuLocale !== defaultLocale) {
                return true;
            }
            return item.nameOriginal && item.nameLocale && item.nameOriginal !== item.nameLocale;
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

                var bilingual = showBilingual(item);
                var li = document.createElement('li');
                li.className = 'wn-favorites-item';

                var imgHtml = item.imageUrl
                    ? '<img class="wn-favorites-item__img" src="' + escapeHtml(item.imageUrl) + '" alt="" loading="lazy">'
                    : '<div class="wn-favorites-item__img wn-favorites-item__img--placeholder" aria-hidden="true">' + placeholderIcon + '</div>';

                var localeTag = '';
                if (bilingual) {
                    localeTag = '<span class="wn-favorites-item__locale-tag">' + escapeHtml(menuLocaleLabel) + '</span>';
                }

                var originalBlock = '';
                if (bilingual) {
                    originalBlock =
                        '<div class="wn-favorites-item__original">' +
                            '<span class="wn-favorites-item__original-label">' + escapeHtml(defaultLocaleLabel) + '</span>' +
                            '<span class="wn-favorites-item__original-name">' + escapeHtml(item.nameOriginal) + '</span>' +
                        '</div>';
                }

                var priceLine = item.priceLabel
                    ? '<p class="wn-favorites-item__price">' + escapeHtml(item.priceLabel) + '</p>'
                    : '';

                li.innerHTML =
                    '<div class="wn-favorites-item__media">' + imgHtml + '</div>' +
                    '<div class="wn-favorites-item__body">' +
                        localeTag +
                        '<p class="wn-favorites-item__name">' + escapeHtml(item.nameLocale) + '</p>' +
                        originalBlock +
                        priceLine +
                    '</div>' +
                    '<button type="button" class="wn-favorites-item__remove" data-fav-remove="' + id + '" aria-label="' + escapeHtml(labels.remove || 'Quitar') + '">' +
                        removeIcon +
                    '</button>';

                listEl.appendChild(li);
            });

            if (emptyEl) {
                emptyEl.hidden = hasItems;
            }
            if (listEl) {
                listEl.hidden = !hasItems;
            }
            if (panelEl) {
                panelEl.classList.toggle('wn-favorites-panel--has-items', hasItems);
            }
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
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && panelEl && !panelEl.hidden) {
                closePanel();
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
