(function () {
    'use strict';

    var fileInput = document.getElementById('menu-scan-files');
    var fileList = document.getElementById('menu-scan-file-list');
    var submitBtn = document.getElementById('menu-scan-submit');
    var uploadForm = document.getElementById('menu-scan-upload-form');
    var spinner = document.getElementById('menu-scan-spinner');
    var dropzone = document.getElementById('menu-scan-dropzone');
    var startScanBtn = document.getElementById('menu-scan-start');
    var guideModalEl = document.getElementById('menu-scan-guide-modal');
    var guideContinueBtn = document.getElementById('menu-scan-guide-continue');
    var guideModal = null;
    var guideCarouselApi = null;
    var pickFilesBtn = document.getElementById('menu-scan-pick-files');
    var cameraNativeInput = document.getElementById('menu-scan-camera-native');
    var cameraModalEl = document.getElementById('menu-scan-camera-modal');
    var videoEl = document.getElementById('menu-scan-video');
    var canvasEl = document.getElementById('menu-scan-canvas');
    var captureBtn = document.getElementById('menu-scan-capture-btn');

    var selectedFiles = [];
    var cameraStream = null;
    var cameraModal = null;
    var previewModalEl = document.getElementById('menu-scan-preview-modal');
    var previewModal = null;
    var previewImg = document.getElementById('menu-scan-preview-img');
    var previewPdf = document.getElementById('menu-scan-preview-pdf');
    var previewPdfName = document.getElementById('menu-scan-preview-pdf-name');
    var previewTitle = document.getElementById('menu-scan-preview-title');
    var previewBadge = document.getElementById('menu-scan-preview-badge');
    var previewMessage = document.getElementById('menu-scan-preview-message');
    var previewRetakeBtn = document.getElementById('menu-scan-preview-retake');
    var previewAcceptBtn = document.getElementById('menu-scan-preview-accept');
    var previewForceBtn = document.getElementById('menu-scan-preview-force');
    var pendingPreviewFile = null;
    var pendingRetakeSource = 'camera';
    var previewQueue = [];
    var SHARP_CLEAR = 120;
    var SHARP_SOFT = 80;
    var guideNextBtn = document.getElementById('menu-scan-guide-next');
    var guideStepNumEl = document.getElementById('menu-scan-guide-step-num');
    var guideStorageKey = 'webnu_scan_guide_done';
    var guideSlideCount = 3;

    if (guideModalEl && guideModalEl.getAttribute('data-guide-user-id')) {
        guideStorageKey += '_' + guideModalEl.getAttribute('data-guide-user-id');
    }

    function hasCompletedScanGuide() {
        try {
            return window.localStorage.getItem(guideStorageKey) === '1';
        } catch (e) {
            return false;
        }
    }

    function markScanGuideCompleted() {
        try {
            window.localStorage.setItem(guideStorageKey, '1');
        } catch (e) {
            /* ignore */
        }
    }

    function updateGuideFooter(stepIndex) {
        if (typeof stepIndex !== 'number') {
            return;
        }
        guideSlideCount = 3;
        var isLast = stepIndex >= guideSlideCount - 1;
        var returning = hasCompletedScanGuide();

        if (guideStepNumEl) {
            guideStepNumEl.textContent = String(stepIndex + 1);
        }

        if (guideNextBtn) {
            guideNextBtn.classList.toggle('d-none', isLast || returning);
        }
        if (guideContinueBtn) {
            guideContinueBtn.classList.toggle('d-none', !isLast && !returning);
        }
    }

    function initGuideCarousel(root) {
        if (!root) {
            return null;
        }
        var slides = root.querySelectorAll('[data-guide-slide]');
        var index = 0;

        function goTo(nextIndex) {
            if (!slides.length) {
                return;
            }
            var prev = index;
            index = Math.max(0, Math.min(nextIndex, slides.length - 1));
            slides.forEach(function (slide, i) {
                slide.classList.remove('is-active', 'is-exit');
                if (i === prev && i !== index) {
                    slide.classList.add('is-exit');
                }
                if (i === index) {
                    slide.classList.add('is-active');
                }
            });
            if (typeof root.onStepChange === 'function') {
                root.onStepChange(index);
            }
        }

        function resetToFirst() {
            goTo(0);
        }

        function getIndex() {
            return index;
        }

        function advance() {
            if (index < slides.length - 1) {
                goTo(index + 1);
            }
        }

        return {
            resetToFirst: resetToFirst,
            getIndex: getIndex,
            advance: advance,
            goTo: goTo
        };
    }

    function openGuideModal() {
        if (!guideModalEl || typeof bootstrap === 'undefined') {
            openCameraModal();
            return;
        }
        var carouselRoot = document.getElementById('menu-scan-guide-carousel');
        if (!guideCarouselApi && carouselRoot) {
            carouselRoot.onStepChange = updateGuideFooter;
            guideCarouselApi = initGuideCarousel(carouselRoot);
        }
        guideModal = bootstrap.Modal.getOrCreateInstance(guideModalEl);
        if (!guideModalEl.dataset.guideBound) {
            guideModalEl.dataset.guideBound = '1';
        }
        guideModal.show();
        if (guideCarouselApi) {
            guideCarouselApi.resetToFirst();
        }
        updateGuideFooter(hasCompletedScanGuide() ? guideSlideCount - 1 : 0);
    }

    function openGuideThenCamera() {
        markScanGuideCompleted();
        if (!guideModalEl || typeof bootstrap === 'undefined') {
            openCameraModal();
            return;
        }
        guideModal = bootstrap.Modal.getOrCreateInstance(guideModalEl);
        guideModalEl.addEventListener('hidden.bs.modal', function onHiddenOnce() {
            guideModalEl.removeEventListener('hidden.bs.modal', onHiddenOnce);
            openCameraModal();
        }, { once: true });
        guideModal.hide();
    }

    function isPdfFile(file) {
        return file && (file.type === 'application/pdf' || /\.pdf$/i.test(file.name));
    }

    function isImageFile(file) {
        return file && /^image\//i.test(file.type);
    }

    function estimateSharpnessFromImage(img) {
        var maxW = 800;
        var scale = Math.min(1, maxW / Math.max(img.naturalWidth || img.width, 1));
        var w = Math.max(1, Math.round((img.naturalWidth || img.width) * scale));
        var h = Math.max(1, Math.round((img.naturalHeight || img.height) * scale));
        var canvas = document.createElement('canvas');
        canvas.width = w;
        canvas.height = h;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, w, h);
        var data = ctx.getImageData(0, 0, w, h).data;
        var gray = new Float32Array(w * h);
        var gi = 0;
        for (var i = 0; i < data.length; i += 4) {
            gray[gi++] = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
        }
        var lapSum = 0;
        var lapSq = 0;
        var count = 0;
        for (var y = 1; y < h - 1; y++) {
            for (var x = 1; x < w - 1; x++) {
                var idx = y * w + x;
                var lap = -4 * gray[idx]
                    + gray[idx - 1] + gray[idx + 1]
                    + gray[idx - w] + gray[idx + w];
                lapSum += lap;
                lapSq += lap * lap;
                count++;
            }
        }
        if (count === 0) {
            return { variance: 0, brightness: 128, width: w, height: h };
        }
        var mean = lapSum / count;
        var variance = lapSq / count - mean * mean;
        var brightSum = 0;
        for (var b = 0; b < gray.length; b++) {
            brightSum += gray[b];
        }
        return {
            variance: variance,
            brightness: brightSum / gray.length,
            width: img.naturalWidth || img.width,
            height: img.naturalHeight || img.height
        };
    }

    function analyzeImageFile(file, callback) {
        var url = URL.createObjectURL(file);
        var img = new Image();
        img.onload = function () {
            var stats = estimateSharpnessFromImage(img);
            URL.revokeObjectURL(url);
            var level = 'clear';
            var messages = [];
            if (stats.width < 600 || stats.height < 600) {
                messages.push('Resolución baja: acércate un poco más a la carta.');
                level = 'soft';
            }
            if (stats.brightness < 55) {
                messages.push('Parece poca luz. Busca un sitio más iluminado.');
                level = 'soft';
            } else if (stats.brightness > 220) {
                messages.push('Hay mucha luz o reflejo. Evita el flash directo.');
                level = 'soft';
            }
            if (stats.variance < SHARP_SOFT) {
                messages.push('La foto puede verse borrosa. El escaneo IA suele fallar.');
                level = 'blur';
            } else if (stats.variance < SHARP_CLEAR && level !== 'blur') {
                messages.push('La nitidez es regular. Si puedes, repite la foto.');
                level = 'soft';
            }
            if (level === 'clear' && messages.length === 0) {
                messages.push('La foto se ve nítida. Puedes enviarla a analizar.');
            }
            callback({ level: level, messages: messages, stats: stats });
        };
        img.onerror = function () {
            URL.revokeObjectURL(url);
            callback({ level: 'soft', messages: ['No se pudo analizar la imagen. Revisa que se vea bien.'], stats: null });
        };
        img.src = url;
    }

    function setPreviewUi(mode, analysis) {
        if (!previewBadge || !previewMessage || !previewAcceptBtn || !previewForceBtn || !previewRetakeBtn) {
            return;
        }
        previewBadge.classList.remove('d-none', 'text-bg-success', 'text-bg-warning', 'text-bg-danger');
        previewForceBtn.classList.add('d-none');
        previewAcceptBtn.classList.remove('btn-warning');
        previewAcceptBtn.classList.add('btn-primary');
        previewRetakeBtn.textContent = mode === 'pdf' ? 'Elegir otro archivo' : 'Otra foto';

        if (mode === 'pdf') {
            previewBadge.textContent = 'PDF';
            previewBadge.classList.add('text-bg-secondary');
            previewMessage.textContent = 'Comprueba que el texto del PDF sea legible antes de analizar.';
            previewAcceptBtn.textContent = 'Añadir PDF';
            return;
        }

        var level = analysis ? analysis.level : 'soft';
        if (level === 'clear') {
            previewBadge.textContent = 'Se ve nítida';
            previewBadge.classList.add('text-bg-success');
            previewAcceptBtn.textContent = 'Usar esta foto';
        } else if (level === 'blur') {
            previewBadge.textContent = 'Puede verse borrosa';
            previewBadge.classList.add('text-bg-danger');
            previewMessage.textContent = (analysis.messages || []).join(' ');
            previewForceBtn.classList.remove('d-none');
            previewAcceptBtn.textContent = 'Usar esta foto';
            previewRetakeBtn.classList.add('btn-primary');
            previewRetakeBtn.classList.remove('btn-outline-secondary');
        } else {
            previewBadge.textContent = 'Revisar nitidez';
            previewBadge.classList.add('text-bg-warning');
            previewForceBtn.classList.remove('d-none');
            previewAcceptBtn.textContent = 'Usar esta foto';
        }
        if (level !== 'blur') {
            previewRetakeBtn.classList.remove('btn-primary');
            previewRetakeBtn.classList.add('btn-outline-secondary');
        }
        previewMessage.textContent = (analysis && analysis.messages) ? analysis.messages.join(' ') : '';
    }

    function showPreviewModal(file, retakeSource) {
        if (!previewModalEl || typeof bootstrap === 'undefined') {
            addFiles([file]);
            return;
        }
        pendingPreviewFile = file;
        pendingRetakeSource = retakeSource || 'gallery';
        previewModal = bootstrap.Modal.getOrCreateInstance(previewModalEl);

        if (previewImg) {
            previewImg.classList.add('d-none');
            previewImg.removeAttribute('src');
        }
        if (previewPdf) {
            previewPdf.classList.add('d-none');
        }

        if (isPdfFile(file)) {
            if (previewTitle) {
                previewTitle.textContent = 'Revisar PDF';
            }
            if (previewPdf) {
                previewPdf.classList.remove('d-none');
            }
            if (previewPdfName) {
                previewPdfName.textContent = file.name;
            }
            setPreviewUi('pdf', null);
            previewModal.show();
            return;
        }

        if (previewTitle) {
            previewTitle.textContent = 'Revisar foto';
        }
        if (previewImg) {
            previewImg.src = URL.createObjectURL(file);
            previewImg.onload = function () {
                analyzeImageFile(file, function (analysis) {
                    setPreviewUi('image', analysis);
                });
            };
            previewImg.classList.remove('d-none');
        }
        setPreviewUi('image', { level: 'soft', messages: ['Analizando nitidez…'] });
        previewModal.show();
    }

    function processNextInPreviewQueue() {
        if (previewQueue.length === 0) {
            return;
        }
        var next = previewQueue.shift();
        showPreviewModal(next.file, next.source);
    }

    function queueFilesForPreview(fileListLike, source) {
        if (!fileListLike) {
            return;
        }
        for (var i = 0; i < fileListLike.length; i++) {
            if (selectedFiles.length + previewQueue.length >= 10) {
                break;
            }
            previewQueue.push({ file: fileListLike[i], source: source || 'gallery' });
        }
        if (pendingPreviewFile === null && previewModalEl && !previewModalEl.classList.contains('show')) {
            processNextInPreviewQueue();
        }
    }

    function acceptPendingFile() {
        if (!pendingPreviewFile) {
            return;
        }
        if (previewImg && previewImg.src && previewImg.src.indexOf('blob:') === 0) {
            URL.revokeObjectURL(previewImg.src);
        }
        addFiles([pendingPreviewFile]);
        pendingPreviewFile = null;
        if (previewModal) {
            previewModal.hide();
        }
    }

    function syncInputFiles() {
        if (!fileInput || typeof DataTransfer === 'undefined') {
            return;
        }
        var dt = new DataTransfer();
        selectedFiles.forEach(function (f) {
            dt.items.add(f);
        });
        fileInput.files = dt.files;
    }

    function refreshFileList() {
        if (!fileList) {
            return;
        }
        fileList.innerHTML = '';
        if (selectedFiles.length === 0) {
            fileList.classList.add('d-none');
            if (submitBtn) {
                submitBtn.disabled = true;
            }
            return;
        }
        fileList.classList.remove('d-none');
        selectedFiles.forEach(function (file, index) {
            var li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            var label = document.createElement('span');
            label.textContent = file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-link text-danger';
            remove.textContent = 'Quitar';
            remove.addEventListener('click', function () {
                selectedFiles.splice(index, 1);
                syncInputFiles();
                refreshFileList();
            });
            li.appendChild(label);
            li.appendChild(remove);
            fileList.appendChild(li);
        });
        if (submitBtn) {
            submitBtn.disabled = false;
        }
    }

    function addFiles(fileListLike) {
        if (!fileListLike) {
            return;
        }
        var max = 10;
        for (var i = 0; i < fileListLike.length; i++) {
            if (selectedFiles.length >= max) {
                break;
            }
            selectedFiles.push(fileListLike[i]);
        }
        syncInputFiles();
        refreshFileList();
    }

    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(function (t) {
                t.stop();
            });
            cameraStream = null;
        }
        if (videoEl) {
            videoEl.srcObject = null;
        }
    }

    function startCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            if (cameraNativeInput) {
                cameraNativeInput.click();
            }
            return;
        }
        navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: { ideal: 'environment' },
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            },
            audio: false
        }).then(function (stream) {
            cameraStream = stream;
            if (videoEl) {
                videoEl.srcObject = stream;
            }
        }).catch(function () {
            stopCamera();
            if (cameraModal) {
                cameraModal.hide();
            }
            if (cameraNativeInput) {
                cameraNativeInput.click();
            }
        });
    }

    function openCameraModal() {
        if (!cameraModalEl || typeof bootstrap === 'undefined') {
            if (cameraNativeInput) {
                cameraNativeInput.click();
            }
            return;
        }
        cameraModal = bootstrap.Modal.getOrCreateInstance(cameraModalEl);
        cameraModalEl.addEventListener('shown.bs.modal', function onShown() {
            cameraModalEl.removeEventListener('shown.bs.modal', onShown);
            startCamera();
        }, { once: true });
        cameraModalEl.addEventListener('hidden.bs.modal', function onHidden() {
            stopCamera();
        }, { once: false });
        cameraModal.show();
    }

    function capturePhoto() {
        if (!videoEl || !canvasEl || !videoEl.videoWidth) {
            return;
        }
        canvasEl.width = videoEl.videoWidth;
        canvasEl.height = videoEl.videoHeight;
        var ctx = canvasEl.getContext('2d');
        ctx.drawImage(videoEl, 0, 0);
        canvasEl.toBlob(function (blob) {
            if (!blob) {
                return;
            }
            var name = 'carta-' + new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19) + '.jpg';
            var file = new File([blob], name, { type: 'image/jpeg' });
            if (cameraModal) {
                cameraModal.hide();
            }
            showPreviewModal(file, 'camera');
        }, 'image/jpeg', 0.92);
    }

    if (previewModalEl) {
        previewModalEl.addEventListener('hidden.bs.modal', function () {
            if (previewImg && previewImg.src && previewImg.src.indexOf('blob:') === 0) {
                URL.revokeObjectURL(previewImg.src);
                previewImg.removeAttribute('src');
            }
            pendingPreviewFile = null;
            if (previewQueue.length > 0) {
                processNextInPreviewQueue();
            }
        });
    }
    if (previewAcceptBtn) {
        previewAcceptBtn.addEventListener('click', acceptPendingFile);
    }
    if (previewForceBtn) {
        previewForceBtn.addEventListener('click', acceptPendingFile);
    }
    if (previewRetakeBtn) {
        previewRetakeBtn.addEventListener('click', function () {
            if (previewImg && previewImg.src && previewImg.src.indexOf('blob:') === 0) {
                URL.revokeObjectURL(previewImg.src);
            }
            pendingPreviewFile = null;
            if (previewModal) {
                previewModal.hide();
            }
            if (pendingRetakeSource === 'camera') {
                openCameraModal();
            } else if (fileInput) {
                fileInput.click();
            }
        });
    }

    if (startScanBtn) {
        startScanBtn.addEventListener('click', openGuideModal);
    }
    if (guideNextBtn) {
        guideNextBtn.addEventListener('click', function () {
            if (!guideCarouselApi) {
                var carouselRoot = document.getElementById('menu-scan-guide-carousel');
                if (carouselRoot) {
                    carouselRoot.onStepChange = updateGuideFooter;
                    guideCarouselApi = initGuideCarousel(carouselRoot);
                }
            }
            if (guideCarouselApi) {
                guideCarouselApi.advance();
            }
        });
    }
    if (guideContinueBtn) {
        guideContinueBtn.addEventListener('click', openGuideThenCamera);
    }
    if (captureBtn) {
        captureBtn.addEventListener('click', capturePhoto);
    }
    if (pickFilesBtn && fileInput) {
        pickFilesBtn.addEventListener('click', function () {
            fileInput.click();
        });
    }
    if (cameraNativeInput) {
        cameraNativeInput.addEventListener('change', function () {
            queueFilesForPreview(cameraNativeInput.files, 'camera');
            cameraNativeInput.value = '';
        });
    }
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            queueFilesForPreview(fileInput.files, 'gallery');
            fileInput.value = '';
        });
    }

    if (dropzone && fileInput) {
        ['dragenter', 'dragover'].forEach(function (ev) {
            dropzone.addEventListener(ev, function (e) {
                e.preventDefault();
                dropzone.classList.add('is-dragover');
            });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
            dropzone.addEventListener(ev, function (e) {
                e.preventDefault();
                dropzone.classList.remove('is-dragover');
            });
        });
        dropzone.addEventListener('drop', function (e) {
            if (e.dataTransfer && e.dataTransfer.files) {
                queueFilesForPreview(e.dataTransfer.files, 'gallery');
            }
        });
        dropzone.addEventListener('click', function () {
            fileInput.click();
        });
    }

    var processingOverlay = document.getElementById('menu-scan-processing');
    var processingStatus = document.getElementById('menu-scan-processing-status');
    var processingSteps = document.getElementById('menu-scan-processing-steps');
    var processingPhaseTimer = null;

    var processingPhases = [
        { step: 'upload', text: 'Subiendo imágenes al servidor…' },
        { step: 'read', text: 'La IA está leyendo tu carta…' },
        { step: 'read', text: 'Reconociendo texto y estructura…' },
        { step: 'sections', text: 'Detectando secciones y platos…' },
        { step: 'sections', text: 'Organizando categorías del menú…' },
        { step: 'prices', text: 'Extrayendo precios y descripciones…' },
        { step: 'prices', text: 'Buscando alérgenos en la leyenda…' },
        { step: 'done', text: 'Últimos retoques, casi listo…' }
    ];

    function setProcessingStep(stepId) {
        if (!processingSteps) {
            return;
        }
        var order = ['upload', 'read', 'sections', 'prices', 'done'];
        var activeIndex = order.indexOf(stepId);
        processingSteps.querySelectorAll('li').forEach(function (li) {
            var step = li.getAttribute('data-step');
            var idx = order.indexOf(step);
            li.classList.remove('is-active', 'is-done');
            if (idx < activeIndex) {
                li.classList.add('is-done');
            } else if (idx === activeIndex) {
                li.classList.add('is-active');
            }
        });
    }

    function showProcessingOverlay() {
        if (!processingOverlay) {
            return;
        }
        processingOverlay.hidden = false;
        processingOverlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('wn-scan-processing-open');

        var phaseIndex = 0;
        setProcessingStep(processingPhases[0].step);
        if (processingStatus) {
            processingStatus.textContent = processingPhases[0].text;
        }

        if (processingPhaseTimer) {
            clearInterval(processingPhaseTimer);
        }
        processingPhaseTimer = setInterval(function () {
            phaseIndex = Math.min(phaseIndex + 1, processingPhases.length - 1);
            var phase = processingPhases[phaseIndex];
            setProcessingStep(phase.step);
            if (processingStatus) {
                processingStatus.classList.add('is-fading');
                setTimeout(function () {
                    processingStatus.textContent = phase.text;
                    processingStatus.classList.remove('is-fading');
                }, 180);
            }
        }, 4200);
    }

    if (uploadForm) {
        uploadForm.addEventListener('submit', function () {
            syncInputFiles();
            if (submitBtn) {
                submitBtn.disabled = true;
            }
            if (spinner) {
                spinner.classList.remove('d-none');
            }
            showProcessingOverlay();
        });
    }

    if (!window.webnuMenuScanReview) {
        return;
    }

    var sectionsRoot = document.getElementById('menu-scan-sections');
    var sectionTemplate = document.getElementById('wn-section-template');
    var productRowTemplate = document.getElementById('wn-product-row-template');
    var addSectionBtn = document.getElementById('wn-add-section');
    var importReplace = document.getElementById('import-replace');
    var replaceWrap = document.getElementById('replace-confirm-wrap');

    function reindexSections() {
        if (!sectionsRoot) {
            return;
        }
        var sections = sectionsRoot.querySelectorAll('.wn-menu-scan-section');
        sections.forEach(function (section, si) {
            section.setAttribute('data-section-index', si);
            var sectionName = section.querySelector('.wn-section-name, [data-name="section_name"]');
            if (sectionName) {
                sectionName.name = 'sections[' + si + '][name]';
            }
            var rows = section.querySelectorAll('.wn-product-row');
            rows.forEach(function (row, pi) {
                row.querySelectorAll('[data-name]').forEach(function (input) {
                    var field = input.getAttribute('data-name');
                    input.name = 'sections[' + si + '][products][' + pi + '][' + field + ']';
                });
                row.querySelectorAll('input[name]').forEach(function (input) {
                    if (input.getAttribute('data-name')) {
                        return;
                    }
                    var match = input.name.match(/\[products\]\[\d+\]\[(\w+)\]/);
                    if (match) {
                        input.name = 'sections[' + si + '][products][' + pi + '][' + match[1] + ']';
                    }
                });
            });
        });
    }

    function bindSection(sectionEl) {
        var removeSec = sectionEl.querySelector('.wn-remove-section');
        if (removeSec) {
            removeSec.addEventListener('click', function () {
                sectionEl.remove();
                reindexSections();
            });
        }
        sectionEl.querySelectorAll('.wn-add-product').forEach(function (btn) {
            btn.addEventListener('click', function () {
                addProductRow(sectionEl.querySelector('tbody'));
                reindexSections();
            });
        });
        sectionEl.querySelectorAll('.wn-remove-product').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var row = btn.closest('tr');
                if (row) {
                    row.remove();
                }
                reindexSections();
            });
        });
    }

    function addProductRow(tbody) {
        if (!productRowTemplate || !tbody) {
            return;
        }
        var clone = productRowTemplate.content.cloneNode(true);
        tbody.appendChild(clone);
        var row = tbody.lastElementChild;
        var removeBtn = row.querySelector('.wn-remove-product');
        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                row.remove();
                reindexSections();
            });
        }
    }

    if (sectionsRoot) {
        sectionsRoot.querySelectorAll('.wn-menu-scan-section').forEach(bindSection);
    }

    if (addSectionBtn && sectionTemplate && sectionsRoot) {
        addSectionBtn.addEventListener('click', function () {
            var clone = sectionTemplate.content.cloneNode(true);
            var section = clone.querySelector('.wn-menu-scan-section');
            sectionsRoot.appendChild(section);
            bindSection(section);
            addProductRow(section.querySelector('tbody'));
            reindexSections();
        });
    }

    if (importReplace && replaceWrap) {
        function toggleReplaceConfirm() {
            if (importReplace.checked) {
                replaceWrap.classList.remove('d-none');
            } else {
                replaceWrap.classList.add('d-none');
            }
        }
        document.querySelectorAll('input[name="import_mode"]').forEach(function (radio) {
            radio.addEventListener('change', toggleReplaceConfirm);
        });
        toggleReplaceConfirm();
    }

    reindexSections();
})();
