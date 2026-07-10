(function () {
    (function initLandingLangSelect() {
        if (!window.__landingLangSelectGlobals) {
            window.__landingLangSelectGlobals = true;
            document.addEventListener('click', function (e) {
                document.querySelectorAll('[data-landing-lang].is-open').forEach(function (root) {
                    if (!root.contains(e.target)) {
                        closeLandingLangSelect(root);
                    }
                });
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('[data-landing-lang].is-open').forEach(closeLandingLangSelect);
                }
            });
        }

        function closeLandingLangSelect(root) {
            var menu = root.querySelector('.landing-lang-select__menu');
            var btn = root.querySelector('.landing-lang-select__trigger');
            root.classList.remove('is-open');
            if (menu) {
                menu.hidden = true;
            }
            if (btn) {
                btn.setAttribute('aria-expanded', 'false');
            }
        }

        function openLandingLangSelect(root) {
            document.querySelectorAll('[data-landing-lang].is-open').forEach(function (other) {
                if (other !== root) {
                    closeLandingLangSelect(other);
                }
            });
            var menu = root.querySelector('.landing-lang-select__menu');
            var btn = root.querySelector('.landing-lang-select__trigger');
            root.classList.add('is-open');
            if (menu) {
                menu.hidden = false;
            }
            if (btn) {
                btn.setAttribute('aria-expanded', 'true');
            }
        }

        document.querySelectorAll('[data-landing-lang]').forEach(function (root) {
            if (root.dataset.langReady === '1') {
                return;
            }
            root.dataset.langReady = '1';

            var btn = root.querySelector('.landing-lang-select__trigger');
            var menu = root.querySelector('.landing-lang-select__menu');
            if (!btn || !menu) {
                return;
            }

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (root.classList.contains('is-open')) {
                    closeLandingLangSelect(root);
                } else {
                    openLandingLangSelect(root);
                }
            });
        });
    })();

    document.querySelectorAll('[data-landing-user-menu]').forEach(function (wrap) {
        var toggle = wrap.querySelector('[data-landing-user-menu-toggle]');
        var panel = wrap.querySelector('[data-landing-user-menu-panel]');
        if (!toggle || !panel) {
            return;
        }

        function closeMenu() {
            wrap.classList.remove('is-open');
            panel.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
        }

        function openMenu() {
            wrap.classList.add('is-open');
            panel.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
        }

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            if (wrap.classList.contains('is-open')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeMenu();
            }
        });
    });
})();
