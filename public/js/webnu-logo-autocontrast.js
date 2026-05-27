/*!
 * Webnu logo auto-contrast.
 * Detects the logo luminance/transparency at render time using a canvas and
 * applies the matching .wn-menu-hero__logo-chip--bg-{light|dark|glass} class
 * on the wrapper. Only runs on chips marked data-logo-autocontrast="on" -- so
 * logos analysed server-side keep their precomputed variant.
 */
(function () {
    'use strict';

    if (typeof window === 'undefined' || !window.document) return;

    var SAMPLE_SIZE = 32;

    function classify(imgEl) {
        var wrapper = imgEl.closest('[data-logo-autocontrast]');
        if (!wrapper) return;

        try {
            var canvas = document.createElement('canvas');
            canvas.width = SAMPLE_SIZE;
            canvas.height = SAMPLE_SIZE;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(imgEl, 0, 0, SAMPLE_SIZE, SAMPLE_SIZE);

            var data = ctx.getImageData(0, 0, SAMPLE_SIZE, SAMPLE_SIZE).data;
            var lumSum = 0;
            var opaque = 0;
            var transparent = 0;

            for (var i = 0; i < data.length; i += 4) {
                var r = data[i];
                var g = data[i + 1];
                var b = data[i + 2];
                var a = data[i + 3];

                if (a < 32) {
                    transparent++;
                    continue;
                }

                opaque++;
                lumSum += (0.2126 * r + 0.7152 * g + 0.0722 * b) / 255;
            }

            if (opaque === 0) return;

            var luminance = lumSum / opaque;
            var transparencyRatio = transparent / (opaque + transparent);
            var hasSolidBg = transparencyRatio < 0.05;

            var variant = 'glass';
            if (!hasSolidBg) {
                if (luminance < 0.45) variant = 'light';
                else if (luminance > 0.70) variant = 'dark';
            }

            /* Replace any *--bg-{light|dark|glass} class on the wrapper,
             * regardless of its block prefix. This keeps the JS reusable
             * for the public hero chip and the admin company avatar. */
            var classes = (wrapper.className || '').split(/\s+/);
            var remaining = [];
            var prefixes = {};
            for (var k = 0; k < classes.length; k++) {
                var name = classes[k];
                if (!name) continue;
                var m = name.match(/^(.+)--bg-(light|dark|glass)$/);
                if (m) {
                    prefixes[m[1]] = true;
                } else {
                    remaining.push(name);
                }
            }
            for (var prefix in prefixes) {
                if (Object.prototype.hasOwnProperty.call(prefixes, prefix)) {
                    remaining.push(prefix + '--bg-' + variant);
                }
            }
            wrapper.className = remaining.join(' ');
            wrapper.setAttribute('data-logo-autocontrast', 'done');
            wrapper.setAttribute('data-logo-variant', variant);
        } catch (err) {
            /* Cross-origin or other failures: keep the default variant. */
        }
    }

    function process(imgEl) {
        if (!imgEl) return;
        if (imgEl.complete && imgEl.naturalWidth > 0) {
            classify(imgEl);
        } else {
            imgEl.addEventListener('load', function () { classify(imgEl); }, { once: true });
        }
    }

    function scan() {
        var nodes = document.querySelectorAll('[data-logo-autocontrast="on"] img');
        for (var i = 0; i < nodes.length; i++) {
            process(nodes[i]);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scan);
    } else {
        scan();
    }
})();
