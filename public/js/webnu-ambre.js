(function () {
    function initSpotlight(root) {
        var slides = Array.prototype.slice.call(root.querySelectorAll('[data-ambre-slide]'));
        if (slides.length < 2) return;

        var dots = Array.prototype.slice.call(root.querySelectorAll('[data-ambre-dot]'));
        var index = slides.findIndex(function (el) {
            return el.classList.contains('is-active');
        });
        if (index < 0) index = 0;

        function show(next) {
            index = (next + slides.length) % slides.length;
            slides.forEach(function (slide, i) {
                var active = i === index;
                slide.classList.toggle('is-active', active);
                if (active) {
                    slide.removeAttribute('hidden');
                } else {
                    slide.setAttribute('hidden', 'hidden');
                }
            });
            dots.forEach(function (dot, i) {
                var active = i === index;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-selected', active ? 'true' : 'false');
            });
        }

        root.querySelectorAll('[data-ambre-prev]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                show(index - 1);
            });
        });
        root.querySelectorAll('[data-ambre-next]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                show(index + 1);
            });
        });
        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                var next = parseInt(dot.getAttribute('data-ambre-dot'), 10) || 0;
                show(next);
            });
        });

        var startX = null;
        root.addEventListener('touchstart', function (e) {
            if (!e.changedTouches || !e.changedTouches[0]) return;
            startX = e.changedTouches[0].clientX;
        }, { passive: true });
        root.addEventListener('touchend', function (e) {
            if (startX === null || !e.changedTouches || !e.changedTouches[0]) return;
            var dx = e.changedTouches[0].clientX - startX;
            startX = null;
            if (Math.abs(dx) < 40) return;
            show(dx < 0 ? index + 1 : index - 1);
        }, { passive: true });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-ambre-spotlight]').forEach(initSpotlight);
    });
})();
