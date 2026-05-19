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

    function idPrefixForBlock($block) {
        return $block.data('media-mode') === 'add' ? 'product-add' : 'product-modify';
    }

    function attachFileToInput($fileInput, file) {
        var dt = new DataTransfer();
        dt.items.add(file);
        $fileInput[0].files = dt.files;
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
                $block.find('.webnu-media-controls').show();
            };
            reader.readAsDataURL(file);
        });
    }

    function initImageModeToggle($block) {
        var idPrefix = idPrefixForBlock($block);

        $block.find('.product-image-mode-radio').on('change', function () {
            var isCamera = $block.find('.product-image-mode-radio:checked').val() === 'camera';
            $('#' + idPrefix + '-image-upload-panel').toggleClass('hidden', isCamera);
            $('#' + idPrefix + '-image-camera-panel').toggleClass('hidden', !isCamera);
            if (isCamera) {
                startPhotoStream($block);
            } else {
                stopPhotoStream($block);
            }
        });
    }

    var photoStreams = new WeakMap();

    function stopPhotoStream($block) {
        var stream = photoStreams.get($block[0]);
        if (stream) {
            stream.getTracks().forEach(function (track) {
                track.stop();
            });
            photoStreams.delete($block[0]);
        }
        var $preview = $block.find('.product-photo-preview');
        $preview.hide();
        if ($preview[0]) {
            $preview[0].srcObject = null;
        }
    }

    function startPhotoStream($block) {
        if (!navigator.mediaDevices) {
            $block.find('.product-photo-unsupported').show();
            $block.find('.product-image-camera-tab-item').hide();
            return;
        }

        stopPhotoStream($block);
        var $preview = $block.find('.product-photo-preview');
        navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' } },
            audio: false
        }).then(function (stream) {
            photoStreams.set($block[0], stream);
            $preview[0].srcObject = stream;
            $preview.show();
        }).catch(function () {
            $block.find('.product-photo-unsupported').show();
        });
    }

    function initPhotoCapture($block) {
        if (!navigator.mediaDevices) {
            $block.find('.product-photo-unsupported').show();
            $block.find('.product-image-camera-tab-item').hide();
            return;
        }

        var idPrefix = idPrefixForBlock($block);
        var $fileInput = $('#' + idPrefix + '-image');
        var $previewWrap = $('#' + idPrefix + '-image-preview');
        var $previewImg = $previewWrap.find('img');
        var $capture = $block.find('.product-photo-capture');
        var $retake = $block.find('.product-photo-retake');
        var $video = $block.find('.product-photo-preview');

        $capture.on('click', function () {
            var video = $video[0];
            if (!video || !video.videoWidth) {
                alert('Espera a que la cámara esté lista.');
                return;
            }
            var canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);
            canvas.toBlob(function (blob) {
                if (!blob) {
                    return;
                }
                var file = new File([blob], 'foto-plato.jpg', { type: 'image/jpeg' });
                attachFileToInput($fileInput, file);
                $previewImg.attr('src', URL.createObjectURL(blob));
                $previewWrap.show();
                $retake.show();
                stopPhotoStream($block);
                $block.find('.product-image-mode-radio[value="upload"]').prop('checked', true).trigger('change');
            }, 'image/jpeg', 0.92);
        });

        $retake.on('click', function () {
            $fileInput.val('');
            $previewWrap.hide();
            $retake.hide();
            $block.find('.product-image-mode-radio[value="camera"]').prop('checked', true).trigger('change');
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
            $block.data('recorded-blob', null);
        });
    }

    function initRecorder($block) {
        if (!navigator.mediaDevices || !window.MediaRecorder) {
            $block.find('.product-recorder-unsupported').show();
            $block.find('.product-video-record-tab-item').hide();
            return;
        }

        var idPrefix = idPrefixForBlock($block);
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
            attachFileToInput($fileInput, file);
            $block.data('recorded-blob', blob);

            var $previewWrap = $('#' + idPrefix + '-video-preview');
            var $previewVideo = $previewWrap.find('video');
            $previewVideo.attr('src', URL.createObjectURL(blob));
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
                        if (elapsed >= maxSeconds && mediaRecorder && mediaRecorder.state === 'recording') {
                            mediaRecorder.stop();
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
        var idPrefix = idPrefixForBlock($block);
        stopPhotoStream($block);
        $block.find('input[type="file"]').val('');
        $('#' + idPrefix + '-image-preview').hide();
        $('#' + idPrefix + '-video-preview').hide().find('video').attr('src', '');
        $block.find('.product-photo-retake').hide();
        $block.find('.product-image-mode-radio[value="upload"]').prop('checked', true);
        $block.find('.product-video-mode-radio[value="upload"]').prop('checked', true);
        $('#' + idPrefix + '-image-upload-panel, #' + idPrefix + '-video-upload-panel').removeClass('hidden');
        $('#' + idPrefix + '-image-camera-panel, #' + idPrefix + '-video-record-panel').addClass('hidden');
        $block.data('recorded-blob', null);
    }

    function mediaControls($panel) {
        return $panel.find('.webnu-media-controls');
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
            var $controls = $wrap.closest('.webnu-media-panel').find('.webnu-media-controls');

            if (videoPath) {
                $existing.attr('src', baseUrl + '/img/' + videoPath);
                $wrap.show();
                $controls.hide();
            } else {
                $existing.attr('src', '');
                $wrap.hide();
                $controls.show();
            }
        },
        loadModifyImage: function (imagePath) {
            var $wrap = $('#product-modify-image-existing');
            var $existing = $('#product-modify-image-ok');
            var $controls = $wrap.closest('.webnu-media-panel').find('.webnu-media-controls');
            var $preview = $('#product-modify-image-preview');

            if (imagePath) {
                $existing.attr('src', baseUrl + '/img/' + imagePath);
                $wrap.show();
                $controls.hide();
                $preview.hide();
            } else {
                $existing.attr('src', '');
                $wrap.hide();
                $controls.show();
            }
        }
    };

    function initVideoModeToggle($block) {
        var idPrefix = idPrefixForBlock($block);

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
            initImageModeToggle($block);
            initPhotoCapture($block);
            initVideoFilePreview($block);
            initRecorder($block);
            initVideoModeToggle($block);
        });

        $('#modal-add-product').on('hidden.bs.modal', function () {
            window.WebnuProductMediaUI.resetAdd();
        });

        $('.webnu-product-modal').on('hidden.bs.modal', function () {
            $('.product-media-block').each(function () {
                stopPhotoStream($(this));
            });
        });
    });
})(jQuery);
