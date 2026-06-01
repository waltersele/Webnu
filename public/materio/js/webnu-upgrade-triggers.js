(function ($) {
    'use strict';

    var cfg = window.WebnuUpgradeTriggers || {};
    var copy = cfg.copy || {};
    var billingUrl = cfg.billing_url || '';
    var modalEl = document.getElementById('wn-upgrade-trigger-modal');
    var modal = modalEl && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(modalEl) : null;

    var triggerIcons = {
        video: 'ri-movie-2-line',
        videos: 'ri-movie-2-line',
        translation: 'ri-global-line',
        templates: 'ri-layout-grid-line',
        menu_scan: 'ri-scan-line',
        product_photos: 'ri-image-line',
        pdf_menu: 'ri-file-pdf-line',
        tvpik: 'ri-tv-line',
    };

    function tierLabel(tier) {
        return tier === 'plus' ? 'Plus' : 'Pro';
    }

    function tierVariant(tier) {
        return tier === 'plus' ? 'plus' : 'pro';
    }

    function renderPerks(perks) {
        var $list = $('#wn-upgrade-trigger-modal-perks');
        $list.empty();
        if (!perks || !perks.length) {
            $list.addClass('d-none');
            return;
        }
        perks.forEach(function (item) {
            $list.append(
                $('<li></li>').append(
                    $('<i class="ri-check-line" aria-hidden="true"></i>'),
                    document.createTextNode(' ' + item)
                )
            );
        });
        $list.removeClass('d-none');
    }

    function showUpgradeTrigger(key, fallbackHref) {
        var block = copy[key];
        if (!block || !modal) {
            if (fallbackHref) {
                window.location.href = fallbackHref;
            }
            return;
        }

        if (modalEl) {
            modalEl.setAttribute('data-trigger', key);
        }

        var iconClass = triggerIcons[key] || 'ri-vip-crown-line';
        $('#wn-upgrade-trigger-modal-icon i').attr('class', 'ri ' + iconClass);

        var tier = block.tier || 'pro';
        var $tier = $('#wn-upgrade-trigger-modal-tier');
        $tier.text(tierLabel(tier));
        $tier.attr('class', 'wn-plan-pro-badge wn-plan-pro-badge--xs wn-plan-pro-badge--' + tierVariant(tier));

        var $price = $('#wn-upgrade-trigger-modal-price');
        if (block.price_label) {
            $price.text(block.price_label).show();
        } else {
            $price.hide();
        }

        $('#wn-upgrade-trigger-modal-title').text(block.title || '');
        $('#wn-upgrade-trigger-modal-body').text(block.body || '');

        var $statWrap = $('#wn-upgrade-trigger-modal-stat-wrap');
        if (block.stat) {
            $('#wn-upgrade-trigger-modal-stat').text(block.stat);
            $('#wn-upgrade-trigger-modal-stat-caption').text(block.stat_caption || '');
            $statWrap.removeClass('d-none');
        } else {
            $statWrap.addClass('d-none');
        }

        renderPerks(block.perks);

        var $cta = $('#wn-upgrade-trigger-modal-cta');
        $cta.text(block.cta || 'Ver planes');
        if (billingUrl) {
            $cta.attr('href', billingUrl);
        }

        var $fallback = $('#wn-upgrade-trigger-modal-fallback');
        if (fallbackHref) {
            $fallback.attr('href', fallbackHref).removeClass('d-none');
        } else {
            $fallback.addClass('d-none');
        }

        modal.show();
    }

    $(document).on('click', '[data-upgrade-trigger]', function (e) {
        var $el = $(this);
        var key = $el.data('upgrade-trigger');
        if (!key || !copy[key]) {
            return;
        }
        e.preventDefault();
        e.stopPropagation();
        var fallback = $el.data('upgradeFallbackHref') || $el.attr('href');
        showUpgradeTrigger(key, fallback);
    });

    $(document).on('click', '.webnu-media-go-photo', function (e) {
        e.preventDefault();
        var $block = $(this).closest('.product-media-block');
        if (!$block.length) {
            return;
        }
        var idPrefix = $block.data('media-mode') === 'add' ? 'product-add' : 'product-modify';
        $('#' + idPrefix + '-image-upload').trigger('click');
    });

    $(document).on('click', '.webnu-media-go-video', function (e) {
        e.preventDefault();
        var $block = $(this).closest('.product-media-block');
        if (!$block.length) {
            return;
        }
        var idPrefix = $block.data('media-mode') === 'add' ? 'product-add' : 'product-modify';
        $('#' + idPrefix + '-video-upload').trigger('click');
    });

    window.WebnuUpgradeTriggersUI = {
        show: showUpgradeTrigger,
    };
})(jQuery);
