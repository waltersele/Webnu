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

    function portalDishModal($modal) {
        if (!$modal.length || $modal.data('portalBody')) {
            return;
        }
        document.body.appendChild($modal[0]);
        $modal.data('portalBody', true);
    }

    $('.wn-dish-modal').each(function () {
        portalDishModal($(this));
    });

    function openDishModal(selector) {
        if (!selector) {
            return;
        }
        var $modal = $(selector);
        if ($modal.length) {
            portalDishModal($modal);
            $modal.modal('show');
        }
    }

    $(document).on('click', '[data-target^="#wnDish"]', function (e) {
        var target = $(this).attr('data-target');
        if (!target) {
            return;
        }
        e.preventDefault();
        e.stopPropagation();
        openDishModal(target);
    });

    $('.wn-modern-card--interactive, .wn-card-overlay, .wn-card-temporada, .wn-card-catalogo').on('click', function (e) {
        if ($(e.target).closest('.wn-fav-btn, [data-fav-toggle], .modal, a[href]:not([data-target])').length) {
            return;
        }
        var $trigger = $(this).find('[data-target^="#wnDish"]').not('.wn-card-reel__open').first();
        if (!$trigger.length) {
            $trigger = $(this).find('[data-target^="#wnDish"]').first();
        }
        var target = $trigger.length ? $trigger.attr('data-target') : null;
        if (!target) {
            var productId = $(this).data('product-id');
            if (productId) {
                target = '#wnDish' + productId;
            }
        }
        openDishModal(target);
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
@if(!empty($favoritesEnabled))
<script src="{{ asset('js/webnu-menu-favorites.js') }}" defer></script>
@endif
<script src="{{ asset('js/webnu-card-reels.js') }}" defer></script>
<script src="{{ asset('js/webnu-logo-autocontrast.js') }}" defer></script>
