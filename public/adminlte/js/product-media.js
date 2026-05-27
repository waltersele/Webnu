(function ($) {
    'use strict';

    var config = window.WebnuProductMedia || {};
    var baseUrl = config.baseUrl || '';

    function idPrefixForBlock($block) {
        return $block.data('media-mode') === 'add' ? 'product-add' : 'product-modify';
    }

    function initImagePreview($block) {
        var idPrefix = idPrefixForBlock($block);
        var $input = $block.find('.product-image-input');
        var $previewWrap = $('#' + idPrefix + '-image-preview');
        var $previewImg = $previewWrap.find('img');

        $input.on('change', function () {
            var file = this.files && this.files[0];
            if (!file) {
                $previewWrap.hide();
                return;
            }
            var reader = new FileReader();
            reader.onload = function (e) {
                $previewImg.attr('src', e.target.result);
                $previewWrap.show();
            };
            reader.readAsDataURL(file);
        });
    }

    function initVideoFilePreview($block) {
        var idPrefix = idPrefixForBlock($block);
        var $input = $block.find('.product-video-file-input');
        var $previewWrap = $('#' + idPrefix + '-video-preview');
        var $previewVideo = $previewWrap.find('video');

        $input.on('change', function () {
            var file = this.files && this.files[0];
            if (!file) {
                $previewWrap.hide();
                return;
            }
            var url = URL.createObjectURL(file);
            $previewVideo.attr('src', url);
            $previewWrap.show();
        });
    }

    function resetMediaBlock($block) {
        var idPrefix = idPrefixForBlock($block);
        $block.find('input[type="file"]').val('');
        $('#' + idPrefix + '-image-preview').hide();
        $('#' + idPrefix + '-video-preview').hide().find('video').attr('src', '');
    }

    window.WebnuProductMediaUI = {
        resetAdd: function () {
            $('.product-media-block[data-media-mode="add"]').each(function () {
                resetMediaBlock($(this));
            });
        },
        loadModifyVideo: function (videoPath) {
            var $wrap = $('#product-modify-video-existing');
            var $existing = $('#product-modify-video-ok');

            if (videoPath) {
                $existing.attr('src', baseUrl + '/img/' + videoPath);
                $wrap.show();
            } else {
                $existing.attr('src', '');
                $wrap.hide();
            }
        },
        loadModifyImage: function (imagePath) {
            var $wrap = $('#product-modify-image-existing');
            var $existing = $('#product-modify-image-ok');
            var $preview = $('#product-modify-image-preview');

            if (imagePath) {
                $existing.attr('src', baseUrl + '/img/' + imagePath);
                $wrap.show();
                $preview.hide();
            } else {
                $existing.attr('src', '');
                $wrap.hide();
            }
        }
    };

    $(function () {
        $('.product-media-block').each(function () {
            var $block = $(this);
            initImagePreview($block);
            initVideoFilePreview($block);
        });

        $('#modal-add-product').on('hidden.bs.modal', function () {
            window.WebnuProductMediaUI.resetAdd();
        });
    });
})(jQuery);
