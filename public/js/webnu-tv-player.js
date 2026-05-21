/**
 * Modo reproductor TV — sincroniza con Webnu mientras emites por HDMI o Cast.
 */
(function (global) {
    'use strict';

    function init(options) {
        options = options || {};
        var syncUrl = options.syncUrl;
        var version = options.syncVersion || '';
        var layout = options.layout || '';
        var pollMs = Math.max(15000, (options.pollSeconds || 30) * 1000);
        var hud = document.getElementById('wn-tv-player-hud');
        var hudText = document.querySelector('[data-wn-tv-hud-status]');

        document.documentElement.classList.add('wn-tv-player-active');

        if (global.screen && global.screen.wakeLock && typeof global.screen.wakeLock.request === 'function') {
            global.screen.wakeLock.request('screen').catch(function () {});
        }

        function setHud(msg) {
            if (hudText) {
                hudText.textContent = msg;
            }
        }

        function hideHudSoon() {
            if (!hud) {
                return;
            }
            hud.classList.add('is-visible');
            setTimeout(function () {
                hud.classList.add('is-faded');
            }, 6000);
        }

        hideHudSoon();

        if (!syncUrl) {
            return;
        }

        function checkSync() {
            var url = syncUrl + (syncUrl.indexOf('?') >= 0 ? '&' : '?') + 'layout=' + encodeURIComponent(layout) + '&v=' + encodeURIComponent(version);
            fetch(url, {
                headers: { 'X-Sync-Version': version, Accept: 'application/json' },
                cache: 'no-store',
            })
                .then(function (res) {
                    if (res.status === 304) {
                        return null;
                    }
                    if (!res.ok) {
                        throw new Error('sync');
                    }
                    return res.json();
                })
                .then(function (data) {
                    if (!data || !data.sync_version || data.sync_version === version) {
                        return;
                    }
                    setHud('Actualizando carta…');
                    global.location.reload();
                })
                .catch(function () {
                    setHud('Sin conexión · reintentando…');
                });
        }

        setInterval(checkSync, pollMs);
    }

    global.WebnuTvPlayer = { init: init };
})(typeof window !== 'undefined' ? window : this);
