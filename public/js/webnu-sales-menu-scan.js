(function () {
    'use strict';

    var fileInput = document.getElementById('menu-scan-files');
    var fileList = document.getElementById('menu-scan-file-list');
    var submitBtn = document.getElementById('menu-scan-submit');
    var uploadForm = document.getElementById('menu-scan-upload-form');
    var spinner = document.getElementById('menu-scan-spinner');
    var dropzone = document.getElementById('menu-scan-dropzone');
    var startScanBtn = document.getElementById('menu-scan-start');
    var pickFilesBtn = document.getElementById('menu-scan-pick-files');
    var cameraNativeInput = document.getElementById('menu-scan-camera-native');
    var processingEl = document.getElementById('menu-scan-processing');

    var selectedFiles = [];

    if (!fileInput || !uploadForm) {
        return;
    }

    function syncInputFiles() {
        var dt = new DataTransfer();
        selectedFiles.forEach(function (f) {
            dt.items.add(f);
        });
        fileInput.files = dt.files;
    }

    function renderFileList() {
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
            li.textContent = file.name;
            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-outline-danger';
            remove.textContent = 'Quitar';
            remove.addEventListener('click', function () {
                selectedFiles.splice(index, 1);
                syncInputFiles();
                renderFileList();
            });
            li.appendChild(remove);
            fileList.appendChild(li);
        });
        if (submitBtn) {
            submitBtn.disabled = false;
        }
    }

    function addFiles(files) {
        Array.prototype.forEach.call(files, function (file) {
            selectedFiles.push(file);
        });
        syncInputFiles();
        renderFileList();
    }

    if (pickFilesBtn) {
        pickFilesBtn.addEventListener('click', function () {
            fileInput.click();
        });
    }

    if (startScanBtn && cameraNativeInput) {
        startScanBtn.addEventListener('click', function () {
            cameraNativeInput.click();
        });
    }

    fileInput.addEventListener('change', function () {
        if (fileInput.files && fileInput.files.length) {
            addFiles(fileInput.files);
        }
    });

    if (cameraNativeInput) {
        cameraNativeInput.addEventListener('change', function () {
            if (cameraNativeInput.files && cameraNativeInput.files.length) {
                addFiles(cameraNativeInput.files);
            }
            cameraNativeInput.value = '';
        });
    }

    if (dropzone) {
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

    uploadForm.addEventListener('submit', function () {
        if (selectedFiles.length === 0) {
            return;
        }
        if (submitBtn) {
            submitBtn.disabled = true;
        }
        if (spinner) {
            spinner.classList.remove('d-none');
        }
        if (processingEl) {
            processingEl.hidden = false;
            processingEl.setAttribute('aria-hidden', 'false');
        }
    });

    renderFileList();

    if (window.location.hash === '#upload' && pickFilesBtn) {
        setTimeout(function () {
            fileInput.click();
        }, 100);
    }
})();
