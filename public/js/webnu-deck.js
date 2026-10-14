(function () {
    function initDeck(root) {
        var track = root.querySelector('[data-deck-track]');
        var dots = root.querySelectorAll('[data-deck-dots] .wn-deck-dot');
        if (!track) return;

        var cards = track.querySelectorAll('.wn-deck-card');
        if (cards.length < 2 || !dots.length) return;

        function updateDots() {
            var scrollLeft = track.scrollLeft;
            var width = track.clientWidth || 1;
            var index = Math.round(scrollLeft / width);
            index = Math.max(0, Math.min(index, dots.length - 1));
            dots.forEach(function (dot, i) {
                dot.classList.toggle('is-active', i === index);
            });
        }

        track.addEventListener('scroll', function () {
            window.requestAnimationFrame(updateDots);
        }, { passive: true });

        updateDots();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-deck-carousel]').forEach(initDeck);
    });
})();
