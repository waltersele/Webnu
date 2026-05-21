/**
 * Webnu TV — rotación de secciones y carruseles (TVPik iframe 16:9)
 */
(function (global) {
    'use strict';

    function parseInterval(el, fallback) {
        var raw = el && el.getAttribute('data-tv-interval');
        var n = parseInt(raw, 10);
        return isNaN(n) || n < 4 ? fallback : n;
    }

    function initRotate() {
        var root = document.querySelector('[data-tv-rotate]');
        if (!root) return;
        var slides = root.querySelectorAll('[data-tv-slide]');
        if (slides.length < 2) return;
        var interval = parseInterval(root, 12) * 1000;
        var i = 0;
        slides.forEach(function (s, idx) {
            s.style.display = idx === 0 ? 'block' : 'none';
            s.classList.toggle('is-active', idx === 0);
        });
        setInterval(function () {
            slides[i].style.display = 'none';
            slides[i].classList.remove('is-active');
            i = (i + 1) % slides.length;
            slides[i].style.display = 'block';
            slides[i].classList.add('is-active');
        }, interval);
    }

    function buildDots(container, count, activeIndex) {
        if (!container || count < 2) return;
        container.innerHTML = '';
        for (var d = 0; d < count; d++) {
            var dot = document.createElement('span');
            dot.className = 'wn-tv-dot' + (d === activeIndex ? ' is-active' : '');
            container.appendChild(dot);
        }
    }

    function updateDots(container, activeIndex) {
        if (!container) return;
        var dots = container.querySelectorAll('.wn-tv-dot');
        dots.forEach(function (dot, n) {
            dot.classList.toggle('is-active', n === activeIndex);
        });
    }

    function loadLazyVideo(video) {
        if (!video || video.getAttribute('data-src-loaded')) {
            return;
        }
        var src = video.getAttribute('data-src');
        if (!src) {
            return;
        }
        video.src = src;
        video.setAttribute('data-src-loaded', '1');
        video.load();
    }

    function initCarousel(options) {
        options = options || {};
        var root = document.querySelector('[data-tv-carousel]');
        if (!root) return;
        var slides = root.querySelectorAll('[data-tv-carousel-slide]');
        if (!slides.length) {
            return;
        }
        var interval = parseInterval(root, options.video ? 15 : 8) * 1000;
        var dotsRoot = root.querySelector('[data-tv-carousel-dots]');
        var i = 0;

        if (slides.length > 1) {
            buildDots(dotsRoot, slides.length, 0);
        }

        function show(idx) {
            slides.forEach(function (s, n) {
                var active = n === idx;
                s.classList.toggle('is-active', active);
                if (options.video) {
                    var v = s.querySelector('video');
                    if (!v) {
                        return;
                    }
                    if (active) {
                        if (options.lazyVideo) {
                            loadLazyVideo(v);
                        }
                        v.play().catch(function () {});
                    } else {
                        v.pause();
                    }
                }
            });
            updateDots(dotsRoot, idx);
        }

        show(0);

        if (slides.length < 2) {
            return;
        }

        setInterval(function () {
            i = (i + 1) % slides.length;
            show(i);
        }, interval);
    }

    global.WebnuTv = {
        initRotate: initRotate,
        initCarousel: initCarousel,
    };
})(typeof window !== 'undefined' ? window : this);
