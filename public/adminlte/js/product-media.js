(function ($) {
    'use strict';

    var config = window.WebnuProductMedia || {};
    var baseUrl = (config.baseUrl || '').replace(/\/$/, '');

    function idPrefixForBlock($block) {
        return $block.data('media-mode') === 'add' ? 'product-add' : 'product-modify';
    }

    function $panel($block, kind) {
        return $block.find('[data-media-kind="' + kind + '"]');
    }

    function setPhotoState($block, state, src) {
        var $photo = $panel($block, 'photo');
        var $empty = $photo.find('[data-photo-empty]');
        var $filled = $photo.find('[data-photo-filled]');
        var $badge = $photo.find('[data-photo-status]');
        var $img = $photo.find('[data-photo-preview-img]');

        if (state === 'empty') {
            $empty.prop('hidden', false);
            $filled.prop('hidden', true);
            $badge.prop('hidden', true);
            $img.attr('src', '');
            $('#' + idPrefixForBlock($block) + '-image-preview').hide();
            return;
        }

        $empty.prop('hidden', true);
        $filled.prop('hidden', false);
        $badge.prop('hidden', false);
        if (src) {
            $img.attr('src', src);
        }
        $('#' + idPrefixForBlock($block) + '-image-preview').hide();
    }

    function setVideoState($block, state, src) {
        var $video = $panel($block, 'video');
        if (!$video.length) {
            return;
        }

        var $empty = $video.find('[data-video-empty]');
        var $filled = $video.find('[data-video-filled]');
        var $badge = $video.find('[data-video-status]');
        var $el = $video.find('[data-video-preview]');

        if (state === 'empty') {
            $empty.prop('hidden', false);
            $filled.prop('hidden', true);
            $badge.prop('hidden', true);
            $el.attr('src', '');
            $('#' + idPrefixForBlock($block) + '-video-preview').hide().find('video').attr('src', '');
            return;
        }

        $empty.prop('hidden', true);
        $filled.prop('hidden', false);
        $badge.prop('hidden', false);
        if (src) {
            $el.attr('src', src);
        }
        $('#' + idPrefixForBlock($block) + '-video-preview').hide();
    }

    function initImagePreview($block) {
        var $block = $($block);
        var idPrefix = idPrefixForBlock($block);
        var $input = $block.find('.product-image-input');

        $input.on('change', function () {
            var file = this.files && this.files[0];
            if (!file) {
                return;
            }
            var reader = new FileReader();
            reader.onload = function (e) {
                setPhotoState($block, 'filled', e.target.result);
                var $legacy = $('#' + idPrefix + '-image-preview');
                $legacy.find('img').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        });
    }

    function initVideoFilePreview($block) {
        var $block = $($block);
        var idPrefix = idPrefixForBlock($block);
        var $input = $block.find('.product-video-file-input');

        $input.on('change', function () {
            var file = this.files && this.files[0];
            if (!file) {
                return;
            }
            var url = URL.createObjectURL(file);
            setVideoState($block, 'filled', url);
            $('#' + idPrefix + '-video-preview').find('video').attr('src', url);
        });
    }

    function resetMediaBlock($block) {
        var $block = $($block);
        setPhotoState($block, 'empty');
        setVideoState($block, 'empty');
        $block.find('input[type="file"]').val('');
    }

    function modifyBlock() {
        return $('.product-media-block[data-media-mode="modify"]').first();
    }

    window.WebnuProductMediaUI = {
        resetAdd: function () {
            $('.product-media-block[data-media-mode="add"]').each(function () {
                resetMediaBlock(this);
            });
        },
        loadModifyVideo: function (videoPath) {
            var $block = modifyBlock();
            if (!$block.length) {
                return;
            }
            if (videoPath) {
                setVideoState($block, 'filled', baseUrl + '/img/' + videoPath);
            } else {
                setVideoState($block, 'empty');
            }
        },
        loadModifyImage: function (imagePath) {
            var $block = modifyBlock();
            if (!$block.length) {
                return;
            }
            if (imagePath) {
                setPhotoState($block, 'filled', baseUrl + '/img/' + imagePath);
            } else {
                setPhotoState($block, 'empty');
            }
        }
    };

    $(function () {
        $('.product-media-block').each(function () {
            initImagePreview(this);
            initVideoFilePreview(this);
        });

        $('#modal-add-product').on('hidden.bs.modal', function () {
            window.WebnuProductMediaUI.resetAdd();
        });
    });
})(jQuery);
