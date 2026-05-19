<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script>
$(function () {
    var $header = $('.wn-modern-header');
    var $nav = $('#sticker');

    function measureLayout() {
        var headerH = $header.length ? $header.outerHeight() : 0;
        var navH = $nav.outerHeight() || 52;
        document.documentElement.style.setProperty('--wn-header-height', headerH + 'px');
        document.documentElement.style.setProperty('--wn-nav-height', navH + 'px');
        document.documentElement.style.setProperty('--wn-scroll-offset', (headerH + navH + 8) + 'px');
    }

    function updateNavPin() {
        if (!$nav.length) {
            return;
        }
        var pinAt = $header.length ? $header.offset().top + $header.outerHeight() + 4 : 8;
        $nav.toggleClass('is-pinned', window.scrollY >= pinAt);
    }

    measureLayout();
    updateNavPin();
    $(window).on('resize orientationchange', function () {
        measureLayout();
        updateNavPin();
    });
    $(window).on('scroll', updateNavPin);

    $('a.linkTo').on('click', function (e) {
        e.preventDefault();
        var sectionId = this.id;
        $('.wn-menu-chip').removeClass('is-active');
        $(this).addClass('is-active');
        var $target = $('#section-' + sectionId);
        if ($target.length) {
            var offset = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--wn-scroll-offset')) || 110;
            $('html, body').animate({ scrollTop: $target.offset().top - offset }, 420);
        }
    });

    $('#wn-info-toggle').on('click', function () {
        var $footer = $('#footer');
        if ($footer.length) {
            $('html, body').animate({ scrollTop: $footer.offset().top - 8 }, 420);
        }
    });

    $('.wn-modern-card--interactive, .wn-card-overlay, .wn-card-temporada, .wn-card-catalogo').on('click', function (e) {
        if ($(e.target).closest('a, button, .wn-allergens, .modal, .wn-card-reel__open, video').length) {
            return;
        }
        var target = $(this).find('[data-target^="#wnDish"]').first().attr('data-target');
        if (target) {
            $(target).modal('show');
        }
    });

    if ('IntersectionObserver' in window && $nav.length) {
        var sections = document.querySelectorAll('.wn-menu-section[id]');
        var chips = document.querySelectorAll('.wn-menu-chip.linkTo');
        var scrollOffset = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--wn-scroll-offset')) || 110;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }
                var id = entry.target.id.replace('section-', '');
                chips.forEach(function (chip) {
                    chip.classList.toggle('is-active', chip.id === id);
                });
            });
        }, {
            root: null,
            rootMargin: '-' + scrollOffset + 'px 0px -55% 0px',
            threshold: 0
        });

        sections.forEach(function (section) {
            observer.observe(section);
        });
    }
});
</script>
