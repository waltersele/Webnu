(function ($) {
    'use strict';

    var config = window.WebnuProductMedia || {};
    var maxSeconds = config.maxVideoSeconds || 30;
    var baseUrl = config.baseUrl || '';

    function formatTime(seconds) {
        var m = Math.floor(seconds / 60);
        var s = seconds % 60;
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    function initImagePreview($block) {
        var mode = $block.data('media-mode');
        var idPrefix = mode === 'add' ? 'product-add' : 'product-modify';
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
        var mode = $block.data('media-mode');
        var idPrefix = mode === 'add' ? 'product-add' : 'product-modify';
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
            $block.data('recorded-blob', null);
        });
    }

    function initRecorder($block) {
        if (!navigator.mediaDevices || !window.MediaRecorder) {
            $block.find('.product-recorder-unsupported').show();
            $block.find('.product-recorder-ui').hide();
            $block.find('.product-video-record-tab-item').hide();
            return;
        }

        var mode = $block.data('media-mode');
        var idPrefix = mode === 'add' ? 'product-add' : 'product-modify';
        var $preview = $block.find('.product-recorder-preview');
        var $start = $block.find('.product-recorder-start');
        var $stop = $block.find('.product-recorder-stop');
        var $timer = $block.find('.product-recorder-timer');
        var $fileInput = $('#' + idPrefix + '-video');

        var mediaRecorder = null;
        var stream = null;
        var chunks = [];
        var timerInterval = null;
        var elapsed = 0;

        function stopStream() {
            if (stream) {
                stream.getTracks().forEach(function (track) {
                    track.stop();
                });
                stream = null;
            }
        }

        function resetTimer() {
            clearInterval(timerInterval);
            timerInterval = null;
            elapsed = 0;
            $timer.text('0:00 / ' + formatTime(maxSeconds));
        }

        function attachBlobToInput(blob) {
            var ext = blob.type.indexOf('mp4') >= 0 ? 'mp4' : 'webm';
            var file = new File([blob], 'grabacion-plato.' + ext, { type: blob.type || 'video/webm' });
            var dt = new DataTransfer();
            dt.items.add(file);
            $fileInput[0].files = dt.files;
            $block.data('recorded-blob', blob);

            var $previewWrap = $('#' + idPrefix + '-video-preview');
            var $previewVideo = $previewWrap.find('video');
            var url = URL.createObjectURL(blob);
            $previewVideo.attr('src', url);
            $previewWrap.show();
        }

        $start.on('click', function () {
            navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                .then(function (mediaStream) {
                    stream = mediaStream;
                    $preview[0].srcObject = stream;
                    $preview.show();

                    var options = { mimeType: 'video/webm' };
                    if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                        options = { mimeType: 'video/mp4' };
                    }
                    if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                        options = {};
                    }

                    chunks = [];
                    mediaRecorder = new MediaRecorder(stream, options);
                    mediaRecorder.ondataavailable = function (e) {
                        if (e.data && e.data.size > 0) {
                            chunks.push(e.data);
                        }
                    };
                    mediaRecorder.onstop = function () {
                        stopStream();
                        $preview.hide();
                        $preview[0].srcObject = null;

                        if (chunks.length) {
                            var blob = new Blob(chunks, { type: mediaRecorder.mimeType || 'video/webm' });
                            attachBlobToInput(blob);
                        }
                        $start.prop('disabled', false);
                        $stop.prop('disabled', true);
                        resetTimer();
                    };

                    mediaRecorder.start(200);
                    $start.prop('disabled', true);
                    $stop.prop('disabled', false);
                    resetTimer();
                    timerInterval = setInterval(function () {
                        elapsed += 1;
                        $timer.text(formatTime(elapsed) + ' / ' + formatTime(maxSeconds));
                        if (elapsed >= maxSeconds) {
                            if (mediaRecorder && mediaRecorder.state === 'recording') {
                                mediaRecorder.stop();
                            }
                        }
                    }, 1000);
                })
                .catch(function () {
                    alert('No se pudo acceder a la cámara. Comprueba los permisos del navegador.');
                });
        });

        $stop.on('click', function () {
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                mediaRecorder.stop();
            }
        });
    }

    function resetMediaBlock($block) {
        var mode = $block.data('media-mode');
        var idPrefix = mode === 'add' ? 'product-add' : 'product-modify';
        $block.find('input[type="file"]').val('');
        $('#' + idPrefix + '-image-preview').hide();
        $('#' + idPrefix + '-video-preview').hide().find('video').attr('src', '');
        $block.data('recorded-blob', null);
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
            var $col = $('#product-modify-video').closest('.col-md-6');
            var $videoUi = $col.find('.btn-group, .product-video-panel, .webnu-file-drop, .product-video-upload-preview');

            if (videoPath) {
                $existing.attr('src', baseUrl + '/img/' + videoPath);
                $wrap.show();
                $videoUi.hide();
            } else {
                $existing.attr('src', '');
                $wrap.hide();
                $videoUi.show();
            }
        },
        loadModifyImage: function (imagePath) {
            var $wrap = $('#product-modify-image-existing');
            var $existing = $('#product-modify-image-ok');
            var $fileDrop = $('#product-modify-image').closest('.webnu-file-drop');
            var $preview = $('#product-modify-image-preview');

            if (imagePath) {
                $existing.attr('src', baseUrl + '/img/' + imagePath);
                $wrap.show();
                $fileDrop.hide();
                $preview.hide();
            } else {
                $existing.attr('src', '');
                $wrap.hide();
                $fileDrop.show();
            }
        }
    };

    function initVideoModeToggle($block) {
        var mode = $block.data('media-mode');
        var idPrefix = mode === 'add' ? 'product-add' : 'product-modify';

        $block.find('.product-video-mode-radio').on('change', function () {
            var isRecord = $block.find('.product-video-mode-radio:checked').val() === 'record';
            $('#' + idPrefix + '-video-upload-panel').toggleClass('hidden', isRecord);
            $('#' + idPrefix + '-video-record-panel').toggleClass('hidden', !isRecord);
        });
    }

    $(function () {
        $('.product-media-block').each(function () {
            var $block = $(this);
            initImagePreview($block);
            initVideoFilePreview($block);
            initRecorder($block);
            initVideoModeToggle($block);
        });

        $('#modal-add-product').on('hidden.bs.modal', function () {
            window.WebnuProductMediaUI.resetAdd();
        });
    });
})(jQuery);
