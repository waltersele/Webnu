(function () {
    'use strict';

    function filterPlayerMenus() {
        var companySel = document.getElementById('wn-player-company');
        var menuSel = document.getElementById('wn-player-menu');
        if (!companySel || !menuSel) return;

        var cid = String(companySel.value || '');
        Array.from(menuSel.options).forEach(function (opt) {
            if (!opt.value) {
                opt.hidden = false;
                return;
            }
            var optCid = opt.getAttribute('data-company-id');
            opt.hidden = optCid !== cid;
        });
        if (menuSel.selectedOptions[0]?.hidden) {
            menuSel.value = '';
        }
    }

    function getPlayerTools() {
        var tools = document.getElementById('wn-tvpik-player-tools');
        if (!tools) return null;

        var layouts = {};
        var labels = {};
        try {
            layouts = JSON.parse(tools.getAttribute('data-layouts') || '{}');
        } catch (e) {}
        try {
            labels = JSON.parse(tools.getAttribute('data-template-labels') || '{}');
        } catch (e) {}

        return {
            el: tools,
            adminBase: tools.getAttribute('data-player-admin') || '',
            tvRoot: tools.getAttribute('data-tv-root') || '',
            defaultTemplate: tools.getAttribute('data-default-template') || 'menu',
            layouts: layouts,
            labels: labels,
            companySel: document.getElementById('wn-player-company'),
            menuSel: document.getElementById('wn-player-menu'),
        };
    }

    function getSelectedTemplateKey(tools) {
        return tools.el.getAttribute('data-selected-template') || tools.defaultTemplate;
    }

    function setSelectedTemplateKey(tools, key) {
        tools.el.setAttribute('data-selected-template', key);
    }

    function syncGalleryContext() {
        var tools = getPlayerTools();
        if (!tools || !tools.companySel) return;

        var opt = tools.companySel.options[tools.companySel.selectedIndex];
        var slug = opt && opt.getAttribute('data-slug');

        document.querySelectorAll('.wn-tvpik-copy-template-url, .wn-tvpik-use-template').forEach(function (btn) {
            if (slug) {
                btn.setAttribute('data-slug', slug);
            }
        });
    }

    function tvUrls(tools, templateKey, mode) {
        var opt = tools.companySel && tools.companySel.options[tools.companySel.selectedIndex];
        var slug = opt && opt.getAttribute('data-slug');
        var tpl = templateKey || getSelectedTemplateKey(tools);
        var layout = tools.layouts[tpl] || tpl || 'menu';
        if (!slug) return { slug: '', preview: '', player: '' };

        var query = mode === 'preview' ? 'preview=1' : 'player=1';
        var base = tools.tvRoot.replace(/\/$/, '') + '/' + slug + '/' + layout + '?' + query;
        var mid = tools.menuSel && tools.menuSel.value;
        if (mid) {
            base += '&menu=' + encodeURIComponent(mid);
        }

        return {
            slug: slug,
            preview: base,
            player: base.replace('preview=1', 'player=1'),
        };
    }

    function loadPreview(tools, templateKey, label) {
        var iframe = document.getElementById('wn-tvpik-preview-iframe');
        var placeholder = document.getElementById('wn-tvpik-preview-placeholder');
        var titleEl = document.getElementById('wn-tvpik-preview-title');
        var openTab = document.getElementById('wn-tvpik-preview-open-tab');
        if (!iframe) return;

        setSelectedTemplateKey(tools, templateKey);

        document.querySelectorAll('.wn-tvpik-template-card').forEach(function (card) {
            card.classList.remove('is-selected');
        });
        document.querySelectorAll('.wn-tvpik-use-template[data-template-key="' + templateKey + '"]').forEach(function (btn) {
            var card = btn.closest('.wn-tvpik-template-card');
            if (card) card.classList.add('is-selected');
        });

        var urls = tvUrls(tools, templateKey, 'preview');
        if (!urls.preview) return;

        var resolvedLabel = label || tools.labels[templateKey] || templateKey;
        if (titleEl) titleEl.textContent = resolvedLabel;
        if (openTab) {
            openTab.href = urls.preview;
            openTab.classList.remove('d-none');
        }

        iframe.src = urls.preview;
        iframe.classList.remove('d-none');
        if (placeholder) placeholder.classList.add('d-none');

        var hint = document.getElementById('wn-player-url-hint');
        if (hint) {
            hint.textContent = 'Enlace para la TV: ' + urls.player;
        }
    }

    function initPlayerTools() {
        var tools = getPlayerTools();
        if (!tools) return;

        var hint = document.getElementById('wn-player-url-hint');
        var shareHint = document.getElementById('wn-player-screenshare-hint');

        function onContextChange() {
            filterPlayerMenus();
            syncGalleryContext();
            loadPreview(tools, getSelectedTemplateKey(tools));
        }

        tools.companySel && tools.companySel.addEventListener('change', onContextChange);
        tools.menuSel && tools.menuSel.addEventListener('change', onContextChange);

        filterPlayerMenus();
        syncGalleryContext();
        loadPreview(tools, tools.defaultTemplate, tools.labels[tools.defaultTemplate]);

        document.getElementById('wn-player-open')?.addEventListener('click', function () {
            var urls = tvUrls(tools, getSelectedTemplateKey(tools), 'player');
            if (!urls.player) return;
            var w = window.open(urls.player, 'webnu_tv_player', 'noopener,noreferrer');
            if (w) w.focus();
        });

        document.getElementById('wn-player-copy')?.addEventListener('click', function () {
            var urls = tvUrls(tools, getSelectedTemplateKey(tools), 'player');
            if (!urls.player || !navigator.clipboard) return;
            navigator.clipboard.writeText(urls.player).then(function () {
                var btn = document.getElementById('wn-player-copy');
                if (btn) {
                    var t = btn.innerHTML;
                    btn.innerHTML = '<i class="ti ti-check me-1"></i> Copiado';
                    setTimeout(function () { btn.innerHTML = t; }, 2000);
                }
            });
        });

        document.getElementById('wn-player-screenshare')?.addEventListener('click', function () {
            var urls = tvUrls(tools, getSelectedTemplateKey(tools), 'player');
            if (!urls.player) return;

            function showShareHint(msg) {
                if (shareHint) {
                    shareHint.textContent = msg;
                    shareHint.classList.remove('d-none');
                }
            }

            if (typeof PresentationRequest !== 'undefined') {
                try {
                    var request = new PresentationRequest([urls.player]);
                    request.start().catch(function () {
                        var w = window.open(urls.player, 'webnu_tv_player', 'noopener,noreferrer');
                        if (w) w.focus();
                        showShareHint('Si no aparece la TV: en la pestaña abierta usa el menú del navegador (⋮) → Transmitir / Cast.');
                    });
                    return;
                } catch (e) {}
            }

            var win = window.open(urls.player, 'webnu_tv_player', 'noopener,noreferrer');
            if (win) win.focus();
            showShareHint('Comparte esa pestaña: menú del navegador → Transmitir / Cast, o compartir pantalla en Zoom/Meet.');
        });
    }

    function initTemplateUseButtons() {
        var tools = getPlayerTools();
        if (!tools) return;

        document.querySelectorAll('.wn-tvpik-use-template').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var key = btn.getAttribute('data-template-key');
                var label = btn.getAttribute('data-template-label');
                if (!key) return;
                loadPreview(tools, key, label);
                document.getElementById('wn-tvpik-preview')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        });
    }

    function initGalleryCopyButtons() {
        var tools = getPlayerTools();
        if (!tools) return;

        document.querySelectorAll('.wn-tvpik-copy-template-url').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var templateKey = btn.getAttribute('data-template-key');
                var urls = tvUrls(tools, templateKey, 'player');
                if (!urls.player || !navigator.clipboard) return;

                navigator.clipboard.writeText(urls.player).then(function () {
                    var icon = btn.innerHTML;
                    btn.innerHTML = '<i class="ti ti-check"></i>';
                    setTimeout(function () { btn.innerHTML = icon; }, 2000);
                });
            });
        });
    }

    function initCopyToken() {
        document.getElementById('copy-token')?.addEventListener('click', function () {
            var input = document.getElementById('api-token');
            if (!input) return;
            navigator.clipboard?.writeText(input.value);
            this.textContent = 'Copiado';
            var btn = this;
            setTimeout(function () { btn.textContent = 'Copiar'; }, 2000);
        });
    }

    function filterMenuOptions(form) {
        var companySel = form.querySelector('select[name="company_id"]');
        var menuSel = form.querySelector('select[name="menu_id"]');
        if (!companySel || !menuSel) return;

        var cid = String(companySel.value || '');
        Array.from(menuSel.options).forEach(function (opt) {
            if (!opt.value) {
                opt.hidden = false;
                return;
            }
            var optCid = opt.getAttribute('data-company-id');
            opt.hidden = optCid !== cid;
        });
        if (menuSel.selectedOptions[0]?.hidden) {
            menuSel.value = '';
        }
    }

    function syncMenuPicker(form) {
        var input = form.querySelector('[data-tvpik-template-input]');
        var picker = form.querySelector('[data-tvpik-menu-picker]');
        if (!input || !picker) return;

        var activeBtn = form.querySelector('.wn-tvpik-template-picker__item.is-active');
        var supports = activeBtn && activeBtn.getAttribute('data-supports-menu') === '1';
        picker.classList.toggle('d-none', !supports);
        if (!supports) {
            var menuSel = picker.querySelector('select[name="menu_id"]');
            if (menuSel) menuSel.value = '';
        } else {
            filterMenuOptions(form);
        }
    }

    function updateScreenThumb(form) {
        var input = form.querySelector('[data-tvpik-template-input]');
        var thumbImg = form.closest('.wn-tvpik-screen-card')?.querySelector('[data-tvpik-screen-thumb]');
        if (!input || !thumbImg) return;

        var key = input.value;
        var activeBtn = form.querySelector('.wn-tvpik-template-picker__item[data-template-key="' + key + '"]');
        if (activeBtn) {
            var img = activeBtn.querySelector('img');
            if (img && img.src) {
                thumbImg.src = img.src;
            }
        }
    }

    function initTemplatePickers() {
        document.querySelectorAll('[data-tvpik-picker]').forEach(function (pickerEl) {
            var form = pickerEl.closest('form');
            if (!form) return;

            pickerEl.querySelectorAll('.wn-tvpik-template-picker__item').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (btn.disabled) return;

                    var key = btn.getAttribute('data-template-key');
                    var input = pickerEl.querySelector('[data-tvpik-template-input]');
                    if (!input || !key) return;

                    input.value = key;
                    pickerEl.querySelectorAll('.wn-tvpik-template-picker__item').forEach(function (b) {
                        b.classList.toggle('is-active', b === btn);
                        b.setAttribute('aria-selected', b === btn ? 'true' : 'false');
                    });

                    syncMenuPicker(form);
                    updateScreenThumb(form);

                    var card = form.closest('.wn-tvpik-screen-card');
                    var summary = card?.querySelector('.wn-tvpik-screen-card__summary');
                    if (summary) {
                        var companySel = form.querySelector('select[name="company_id"]');
                        var companyName = companySel?.selectedOptions[0]?.textContent?.trim() || '—';
                        var label = btn.querySelector('.wn-tvpik-template-picker__label')?.textContent?.trim() || key;
                        summary.textContent = companyName + ' · ' + label;
                    }
                });
            });
        });

        document.querySelectorAll('[data-tvpik-screen-form]').forEach(function (form) {
            syncMenuPicker(form);
            form.querySelector('select[name="company_id"]')?.addEventListener('change', function () {
                syncMenuPicker(form);
                filterMenuOptions(form);
                var summary = form.closest('.wn-tvpik-screen-card')?.querySelector('.wn-tvpik-screen-card__summary');
                var companyName = this.selectedOptions[0]?.textContent?.trim() || '—';
                var label = form.querySelector('.wn-tvpik-template-picker__item.is-active .wn-tvpik-template-picker__label')?.textContent?.trim() || '';
                if (summary && label) {
                    summary.textContent = companyName + ' · ' + label;
                }
            });
        });
    }

    function bindGalleryFilters(gallery, filters) {
        var items = gallery.querySelectorAll('[data-template-filter]');
        if (!filters.length) return;

        filters.forEach(function (filterBtn) {
            filterBtn.addEventListener('click', function () {
                var filter = filterBtn.getAttribute('data-filter');
                filters.forEach(function (b) { b.classList.toggle('is-active', b === filterBtn); });
                items.forEach(function (item) {
                    var kind = item.getAttribute('data-template-filter');
                    var show = filter === 'all' || kind === filter;
                    item.classList.toggle('d-none', !show);
                });
            });
        });
    }

    function initGalleryFilters() {
        document.querySelectorAll('[data-gallery-filters-for]').forEach(function (filterBar) {
            var galleryId = filterBar.getAttribute('data-gallery-filters-for');
            var gallery = document.getElementById(galleryId);
            if (!gallery) return;
            bindGalleryFilters(gallery, filterBar.querySelectorAll('.wn-tvpik-gallery__filter'));
        });

        document.querySelectorAll('.wn-tvpik-gallery').forEach(function (gallery) {
            var filters = gallery.querySelectorAll('.wn-tvpik-gallery__filter');
            if (filters.length) {
                bindGalleryFilters(gallery, filters);
            }
        });
    }

    function initPairModal() {
        var modal = document.getElementById('wn-tvpik-pair-modal');
        if (!modal) return;

        modal.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            if (!trigger) return;

            var screenId = trigger.getAttribute('data-pair-screen-id') || '';
            var screenName = trigger.getAttribute('data-pair-screen-name') || '—';

            var idInput = document.getElementById('wn-pair-screen-id');
            var nameEl = document.getElementById('wn-pair-screen-name');
            var codeInput = document.getElementById('wn-pair-code');

            if (idInput) idInput.value = screenId;
            if (nameEl) nameEl.textContent = screenName;
            if (codeInput) {
                codeInput.value = '';
                setTimeout(function () { codeInput.focus(); }, 200);
            }
        });
    }

    function initScreenPoll() {
        var grid = document.getElementById('wn-tvpik-screens-grid');
        if (!grid) return;

        var pollUrl = grid.getAttribute('data-poll-url');
        if (!pollUrl) return;

        var intervalMs = 30000;

        function applyStatus(screens) {
            if (!Array.isArray(screens)) return;

            screens.forEach(function (screen) {
                var card = grid.querySelector('[data-screen-id="' + screen.id + '"]');
                if (!card) return;

                var badge = card.querySelector('[data-tvpik-status]');
                if (!badge) return;

                var online = !!screen.online;
                badge.textContent = online ? 'Online' : 'Offline';
                badge.classList.toggle('bg-success', online);
                badge.classList.toggle('bg-label-secondary', !online);
            });
        }

        function poll() {
            fetch(pollUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (data) {
                    if (data && data.screens) applyStatus(data.screens);
                })
                .catch(function () {});
        }

        setInterval(poll, intervalMs);
    }

    function init() {
        initPlayerTools();
        initTemplateUseButtons();
        initGalleryCopyButtons();
        initCopyToken();
        initTemplatePickers();
        initGalleryFilters();
        initPairModal();
        initScreenPoll();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
