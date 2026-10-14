(function () {
    function cardsOf(rail) {
        return Array.prototype.slice.call(rail.querySelectorAll('.wn-swipe-card'));
    }

    function railOriginX(rail) {
        var style = window.getComputedStyle(rail);
        var paddingLeft = parseFloat(style.paddingLeft) || 0;
        return rail.getBoundingClientRect().left + paddingLeft;
    }

    function scrollLeftForCard(rail, card) {
        var delta = card.getBoundingClientRect().left - railOriginX(rail);
        return Math.max(0, Math.round(rail.scrollLeft + delta));
    }

    function closestCardIndex(rail) {
        var cards = cardsOf(rail);
        if (!cards.length) return 0;

        var origin = railOriginX(rail);
        var best = 0;
        var bestDist = Infinity;

        for (var i = 0; i < cards.length; i++) {
            var dist = Math.abs(cards[i].getBoundingClientRect().left - origin);
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

    function scrollToIndex(rail, cards, index) {
        if (!cards.length) return;
        index = Math.max(0, Math.min(index, cards.length - 1));

        // El primer plato siempre ancla a 0 (padding del rail no debe desplazar el target).
        var left = index === 0 ? 0 : scrollLeftForCard(rail, cards[index]);
        rail.scrollTo({ left: left, behavior: 'smooth' });
    }

    function initRail(rail) {
        var section = rail.closest('.wn-swipe-section');
        var dotsRoot = section ? section.querySelector('[data-swipe-dots]') : null;
        var cards = cardsOf(rail);
        if (cards.length < 2) return;

        var ticking = false;
        rail.addEventListener('scroll', function () {
            if (ticking) return;
            ticking = true;
            window.requestAnimationFrame(function () {
                setActiveDot(dotsRoot, closestCardIndex(rail));
                ticking = false;
            });
        }, { passive: true });

        if (dotsRoot) {
            dotsRoot.querySelectorAll('[data-swipe-dot]').forEach(function (dot) {
                dot.addEventListener('click', function (e) {
                    e.preventDefault();
                    var index = parseInt(dot.getAttribute('data-swipe-dot'), 10);
                    if (isNaN(index)) index = 0;
                    setActiveDot(dotsRoot, index);
                    scrollToIndex(rail, cards, index);
                });
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-swipe-rail]').forEach(initRail);
    });
})();
