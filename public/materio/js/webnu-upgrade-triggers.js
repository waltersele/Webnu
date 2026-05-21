(function ($) {
    'use strict';

    var cfg = window.WebnuUpgradeTriggers || {};
    var copy = cfg.copy || {};
    var billingUrl = cfg.billing_url || '';
    var modalEl = document.getElementById('wn-upgrade-trigger-modal');
    var modal = modalEl && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(modalEl) : null;

    function showUpgradeTrigger(key, fallbackHref) {
        var block = copy[key];
        if (!block || !modal) {
            if (fallbackHref) {
                window.location.href = fallbackHref;
            }
            return;
        }
        $('#wn-upgrade-trigger-modal-title').text(block.title || '');
        $('#wn-upgrade-trigger-modal-body').text(block.body || '');
        var $cta = $('#wn-upgrade-trigger-modal-cta');
        $cta.text(block.cta || 'Ver plan Plus');
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
