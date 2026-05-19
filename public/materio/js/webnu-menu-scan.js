(function () {
    'use strict';

    var fileInput = document.getElementById('menu-scan-files');
    var fileList = document.getElementById('menu-scan-file-list');
    var submitBtn = document.getElementById('menu-scan-submit');
    var uploadForm = document.getElementById('menu-scan-upload-form');
    var spinner = document.getElementById('menu-scan-spinner');
    var dropzone = document.getElementById('menu-scan-dropzone');
    var openCameraBtn = document.getElementById('menu-scan-open-camera');
    var pickFilesBtn = document.getElementById('menu-scan-pick-files');
    var cameraNativeInput = document.getElementById('menu-scan-camera-native');
    var cameraModalEl = document.getElementById('menu-scan-camera-modal');
    var videoEl = document.getElementById('menu-scan-video');
    var canvasEl = document.getElementById('menu-scan-canvas');
    var captureBtn = document.getElementById('menu-scan-capture-btn');

    var selectedFiles = [];
    var cameraStream = null;
    var cameraModal = null;

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
            addFiles([file]);
            if (cameraModal) {
                cameraModal.hide();
            }
        }, 'image/jpeg', 0.92);
    }

    if (openCameraBtn) {
        openCameraBtn.addEventListener('click', openCameraModal);
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
            addFiles(cameraNativeInput.files);
            cameraNativeInput.value = '';
        });
    }
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            addFiles(fileInput.files);
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
                addFiles(e.dataTransfer.files);
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
