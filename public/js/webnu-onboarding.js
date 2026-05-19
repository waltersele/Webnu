(function () {
    var root = document.querySelector('.wn-onb');
    if (!root) {
        return;
    }

    var step = parseInt(root.getAttribute('data-step') || '1', 10);
    if (step === 1) {
        setTimeout(function () {
            document.querySelectorAll('.wn-onb__progress-seg').forEach(function (seg, i) {
                setTimeout(function () {
                    seg.classList.add('is-done');
                }, i * 120);
            });
        }, 400);
    }
})();
