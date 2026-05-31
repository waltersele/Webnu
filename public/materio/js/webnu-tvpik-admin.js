(function () {
    'use strict';

    function initPlayerTools() {
        var tools = document.getElementById('wn-tvpik-player-tools');
        if (!tools) return;

        var adminBase = tools.getAttribute('data-player-admin');
        var tvRoot = tools.getAttribute('data-tv-root') || '';
        var layouts = {};
        try {
            layouts = JSON.parse(tools.getAttribute('data-layouts') || '{}');
        } catch (e) {}

        var companySel = document.getElementById('wn-player-company');
        var tplSel = document.getElementById('wn-player-template');
        var hint = document.getElementById('wn-player-url-hint');

        function adminPlayerUrl() {
            var cid = companySel && companySel.value;
            var tpl = tplSel && tplSel.value;
            if (!cid || !tpl) return '';
            return adminBase + '?company_id=' + encodeURIComponent(cid) + '&template_key=' + encodeURIComponent(tpl);
        }

        function tvPlayerUrl() {
            var opt = companySel && companySel.options[companySel.selectedIndex];
            var slug = opt && opt.getAttribute('data-slug');
            var tpl = tplSel && tplSel.value;
            var layout = layouts[tpl] || tpl || 'menu';
            if (!slug) return '';
            return tvRoot.replace(/\/$/, '') + '/' + slug + '/' + layout + '?player=1';
        }

        function updateHint() {
            if (hint) {
                var direct = tvPlayerUrl();
                hint.textContent = direct ? 'URL para la TV: ' + direct : '';
            }
        }

        companySel && companySel.addEventListener('change', updateHint);
        tplSel && tplSel.addEventListener('change', updateHint);
        updateHint();

        document.getElementById('wn-player-open')?.addEventListener('click', function () {
            var url = adminPlayerUrl();
            if (!url) return;
            var w = window.open(url, 'webnu_tv_player', 'noopener,noreferrer');
            if (w) w.focus();
        });

        document.getElementById('wn-player-copy')?.addEventListener('click', function () {
            var url = tvPlayerUrl();
            if (!url || !navigator.clipboard) return;
            navigator.clipboard.writeText(url).then(function () {
                var btn = document.getElementById('wn-player-copy');
                if (btn) {
                    var t = btn.innerHTML;
                    btn.innerHTML = '<i class="ti ti-check me-1"></i> Copiado';
                    setTimeout(function () { btn.innerHTML = t; }, 2000);
                }
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

    function initGalleryFilters() {
        document.querySelectorAll('.wn-tvpik-gallery').forEach(function (gallery) {
            var filters = gallery.querySelectorAll('.wn-tvpik-gallery__filter');
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
