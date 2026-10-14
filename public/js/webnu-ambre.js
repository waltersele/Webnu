(function () {
    function initSpotlight(root) {
        var slides = Array.prototype.slice.call(root.querySelectorAll('[data-ambre-slide]'));
        if (slides.length < 2) return;

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
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-ambre-spotlight]').forEach(initSpotlight);
    });
})();
