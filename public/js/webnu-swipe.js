(function () {
    function closestCard(rail, fromLeft) {
        var cards = rail.querySelectorAll('.wn-swipe-card');
        if (!cards.length) return 0;
        var best = 0;
        var bestDist = Infinity;
        for (var i = 0; i < cards.length; i++) {
            var dist = Math.abs(cards[i].offsetLeft - fromLeft);
            if (dist < bestDist) {
                bestDist = dist;
                best = i;
            }
        }
        return best;
    }

    function setActiveDot(dotsRoot, index) {
        if (!dotsRoot) return;
        var dots = dotsRoot.querySelectorAll('[data-swipe-dot]');
        dots.forEach(function (dot, i) {
            var active = i === index;
            dot.classList.toggle('is-active', active);
            dot.setAttribute('aria-selected', active ? 'true' : 'false');
        });
    }

    function initRail(rail) {
        var section = rail.closest('.wn-swipe-section');
        var dotsRoot = section ? section.querySelector('[data-swipe-dots]') : null;
        var cards = rail.querySelectorAll('.wn-swipe-card');
        if (cards.length < 2) return;

        var ticking = false;
        rail.addEventListener('scroll', function () {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(function () {
                setActiveDot(dotsRoot, closestCard(rail, rail.scrollLeft));
                ticking = false;
            });
        }, { passive: true });

        if (dotsRoot) {
            dotsRoot.querySelectorAll('[data-swipe-dot]').forEach(function (dot) {
                dot.addEventListener('click', function () {
                    var index = parseInt(dot.getAttribute('data-swipe-dot'), 10) || 0;
                    var target = cards[index];
                    if (!target) return;
                    rail.scrollTo({ left: target.offsetLeft, behavior: 'smooth' });
                    setActiveDot(dotsRoot, index);
                });
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-swipe-rail]').forEach(initRail);
    });
})();
