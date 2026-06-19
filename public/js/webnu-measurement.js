(function () {
    'use strict';

    var STORAGE_KEY = 'webnu_cookie_consent';
    var configEl = document.getElementById('webnu-measurement-config');

    if (!configEl) {
        return;
    }

    var config;
    try {
        config = JSON.parse(configEl.textContent || '{}');
    } catch (e) {
        return;
    }

    if (!config.enabled) {
        return;
    }

    function hasConsent() {
        try {
            return localStorage.getItem(STORAGE_KEY) === 'accepted';
        } catch (e) {
            return false;
        }
    }

    function saveConsent(value) {
        try {
            localStorage.setItem(STORAGE_KEY, value);
        } catch (e) {
            /* ignore */
        }
    }

    function loadScript(src, callback) {
        var script = document.createElement('script');
        script.async = true;
        script.src = src;
        if (callback) {
            script.onload = callback;
        }
        document.head.appendChild(script);
    }

    function loadGtag(id) {
        window.dataLayer = window.dataLayer || [];
        window.gtag = window.gtag || function () {
            window.dataLayer.push(arguments);
        };
        loadScript('https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id), function () {
            window.gtag('js', new Date());
            window.gtag('config', id);
            firePendingEvent();
        });
    }

    function loadGtm(id) {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
        loadScript('https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(id), firePendingEvent);
    }

    function loadClarity(id) {
        window.clarity = window.clarity || function () {
            (window.clarity.q = window.clarity.q || []).push(arguments);
        };
        loadScript('https://www.clarity.ms/tag/' + encodeURIComponent(id), firePendingEvent);
    }

    function firePendingEvent() {
        if (!config.pendingEvent) {
            return;
        }
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({ event: config.pendingEvent });
    }

    function activateTools() {
        var tools = config.tools || {};

        if (tools.gtm && tools.gtm.id) {
            loadGtm(tools.gtm.id);
        } else if (tools.gtag && tools.gtag.id) {
            loadGtag(tools.gtag.id);
        }

        if (tools.clarity && tools.clarity.id) {
            loadClarity(tools.clarity.id);
        }
    }

    function createBanner() {
        if (document.getElementById('webnu-cookie-banner')) {
            return;
        }

        var banner = document.createElement('div');
        banner.id = 'webnu-cookie-banner';
        banner.setAttribute('role', 'dialog');
        banner.setAttribute('aria-live', 'polite');
        banner.style.cssText = [
            'position:fixed',
            'bottom:0',
            'left:0',
            'right:0',
            'z-index:99999',
            'background:#141B2B',
            'color:#fff',
            'padding:16px 20px',
            'display:flex',
            'flex-wrap:wrap',
            'gap:12px',
            'align-items:center',
            'justify-content:center',
            'box-shadow:0 -4px 24px rgba(0,0,0,.2)',
            'font-family:Inter,system-ui,sans-serif',
            'font-size:14px',
        ].join(';');

        var text = document.createElement('p');
        text.style.margin = '0';
        text.textContent = 'Usamos cookies de analítica para mejorar Webnu. Puedes aceptar o rechazar.';

        var acceptBtn = document.createElement('button');
        acceptBtn.type = 'button';
        acceptBtn.textContent = 'Aceptar';
        acceptBtn.style.cssText = 'background:#004ac6;color:#fff;border:0;border-radius:8px;padding:8px 16px;cursor:pointer;font-weight:600;';

        var rejectBtn = document.createElement('button');
        rejectBtn.type = 'button';
        rejectBtn.textContent = 'Rechazar';
        rejectBtn.style.cssText = 'background:transparent;color:#fff;border:1px solid rgba(255,255,255,.4);border-radius:8px;padding:8px 16px;cursor:pointer;';

        acceptBtn.addEventListener('click', function () {
            saveConsent('accepted');
            banner.remove();
            activateTools();
        });

        rejectBtn.addEventListener('click', function () {
            saveConsent('rejected');
            banner.remove();
        });

        banner.appendChild(text);
        banner.appendChild(acceptBtn);
        banner.appendChild(rejectBtn);
        document.body.appendChild(banner);
    }

    if (hasConsent()) {
        if (localStorage.getItem(STORAGE_KEY) === 'accepted') {
            activateTools();
        }
        return;
    }

    if (config.cookieBanner) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', createBanner);
        } else {
            createBanner();
        }
    } else {
        activateTools();
    }
})();
