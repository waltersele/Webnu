(function () {
    'use strict';

    function init() {
        var sidebar = document.getElementById('webnu-sidebar');
        var overlay = document.getElementById('webnu-overlay');
        var menuBtn = document.getElementById('webnu-menu-toggle');

        if (!sidebar) {
            return;
        }

        function openSidebar() {
            sidebar.classList.add('is-open');
            sidebar.classList.remove('is-closed');
            if (overlay) {
                overlay.classList.add('is-visible');
            }
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('is-open');
            if (window.innerWidth < 992) {
                sidebar.classList.add('is-closed');
            }
            if (overlay) {
                overlay.classList.remove('is-visible');
            }
            document.body.style.overflow = '';
        }

        function toggleSidebar() {
            if (sidebar.classList.contains('is-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        }

        if (menuBtn) {
            menuBtn.addEventListener('click', function (e) {
                e.preventDefault();
                toggleSidebar();
            });
        }

        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }

        sidebar.querySelectorAll('.webnu-nav-item').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 992) {
                    closeSidebar();
                }
            });
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 992) {
                sidebar.classList.remove('is-closed', 'is-open');
                if (overlay) {
                    overlay.classList.remove('is-visible');
                }
                document.body.style.overflow = '';
            } else if (!sidebar.classList.contains('is-open')) {
                sidebar.classList.add('is-closed');
            }
        });

        if (window.innerWidth < 992) {
            sidebar.classList.add('is-closed');
        }

        var companySelect = document.getElementById('company_selection');
        var companyForm = document.getElementById('company-selection-form');
        if (companySelect && companyForm) {
            companySelect.addEventListener('change', function () {
                companyForm.submit();
            });
        }

        document.querySelectorAll('.webnu-flash .alert').forEach(function (alert) {
            var closeBtn = alert.querySelector('[data-dismiss="alert"]');
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    alert.remove();
                });
            }
            window.setTimeout(function () {
                if (alert.parentNode) {
                    alert.classList.add('is-hiding');
                    window.setTimeout(function () {
                        alert.remove();
                    }, 300);
                }
            }, 6000);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
