(function ($) {
  'use strict';

  var cfg = window.WebnuCompanyStudio || {};
  var steps = cfg.steps || ['identity', 'contact', 'design', 'publish'];
  var currentStep = cfg.activeStep || 'identity';
  var templateLabels = cfg.templateLabels || {};

  function showStep(step) {
    if (steps.indexOf(step) < 0) {
      step = 'identity';
    }
    currentStep = step;
    $('#studio-step-input').val(step);

    $('.wn-studio-step').addClass('d-none');
    $('.wn-studio-step[data-step="' + step + '"]').removeClass('d-none');

    $('.wn-studio-nav .nav-link').removeClass('active');
    $('.wn-studio-nav .nav-link[data-wn-step="' + step + '"]').addClass('active');

    $('.wn-studio-stepper__item').removeClass('is-active').attr('aria-current', 'false');
    $('.wn-studio-stepper__item[data-wn-step="' + step + '"]')
      .addClass('is-active')
      .attr('aria-current', 'step');

    var idx = steps.indexOf(step);
    $('#wn-step-prev').prop('disabled', idx <= 0);
    $('#wn-step-next').toggle(idx < steps.length - 1);
  }

  function buildPreviewUrl() {
    var base = (cfg.previewUrl || '').split('?')[0];
    var params = new URLSearchParams();
    params.set('studio_preview', '1');
    params.set('t', String(Date.now()));

    var template = $('#company-template').val();
    if (template) {
      params.set('preview_template', template);
    }

    $('.theme-hex-input').each(function () {
      if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
        params.set(this.name, this.value);
      }
    });

    $('.theme-font-select').each(function () {
      if (this.name && this.value) {
        params.set(this.name, this.value);
      }
    });

    return base + '?' + params.toString();
  }

  function setPreviewLoading(loading) {
    var $frame = $('.wn-phone-frame');
    if (loading) {
      $frame.addClass('is-loading');
    } else {
      $frame.removeClass('is-loading');
    }
  }

  function updatePreviewBadge() {
    var template = $('#company-template').val();
    var label = templateLabels[template] || template || '—';
    $('#wn-preview-template-label').text(label);
  }

  function refreshPreview() {
    var iframe = document.getElementById('wn-carta-preview');
    if (!iframe) {
      return;
    }
    setPreviewLoading(true);
    updatePreviewBadge();
    var url = buildPreviewUrl();
    iframe.src = url;
    $('#wn-preview-open').attr('href', url);
  }

  var previewReloadDebounce;
  function debouncedRefreshPreview() {
    clearTimeout(previewReloadDebounce);
    previewReloadDebounce = setTimeout(refreshPreview, 280);
  }

  function bindTemplatePicker() {
    $(document).on('click', '.wn-template-card', function (e) {
      e.preventDefault();
      var key = $(this).data('template');
      $('#company-template').val(key);
      $('.wn-template-card').removeClass('is-selected');
      $('.wn-template-card__check').remove();
      $(this).addClass('is-selected');
      $(this).append('<span class="wn-template-card__check"><i class="ri-check-line"></i></span>');
      updatePresetChips(key);
      var presets = cfg.themePresets[key] || {};
      var first = Object.keys(presets)[0];
      if (first) {
        applyPreset(presets[first], false);
        $('.wn-preset-btn').removeClass('active').first().addClass('active');
      }
      debouncedRefreshPreview();
    });
  }

  function updatePresetChips(templateKey) {
    var presets = cfg.themePresets[templateKey] || {};
    var $wrap = $('#wn-preset-chips');
    $wrap.empty();
    var names = Object.keys(presets);
    if (!names.length) {
      $('#wn-preset-empty').removeClass('d-none');
      return;
    }
    $('#wn-preset-empty').addClass('d-none');
    names.forEach(function (name) {
      $wrap.append(
        $('<button type="button" class="btn btn-sm btn-outline-secondary wn-preset-btn"></button>')
          .text(name)
          .attr('data-colors', JSON.stringify(presets[name]))
      );
    });
  }

  function applyPreset(colors, reload) {
    if (!colors) {
      return;
    }
    Object.keys(colors).forEach(function (key) {
      var hex = colors[key];
      $('#theme_' + key).val(hex);
      var picker = document.getElementById('theme_' + key + '_picker');
      if (picker) {
        picker.value = hex;
        syncColorSwatch(picker);
      }
    });
    if (reload !== false) {
      debouncedRefreshPreview();
    }
  }

  function bindPresets() {
    $(document).on('click', '.wn-preset-btn', function () {
      $('.wn-preset-btn').removeClass('active');
      $(this).addClass('active');
      var raw = $(this).attr('data-colors');
      var colors = raw ? JSON.parse(raw) : null;
      applyPreset(colors);
    });
  }

  function syncColorSwatch(pickerEl) {
    if (!pickerEl || !pickerEl.id) {
      return;
    }
    var hex = pickerEl.value;
    var hexId = pickerEl.id.replace('_picker', '');
    $('#' + hexId).val(hex);
    $(pickerEl).closest('.wn-color-swatch').find('.wn-color-swatch__chip').css('background', hex);
  }

  function bindColorSync() {
    $(document).on('input', '.wn-color-swatch__input, input[type="color"][id$="_picker"], .theme-hex-input', function () {
      if (this.classList.contains('wn-color-swatch__input') || (this.id && this.id.indexOf('_picker') !== -1)) {
        syncColorSwatch(this);
      } else if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
        $('#' + this.id + '_picker').val(this.value);
        syncColorSwatch(document.getElementById(this.id + '_picker'));
      }
      debouncedRefreshPreview();
    });
  }

  function bindFontSelects() {
    $(document).on('change', '.theme-font-select', debouncedRefreshPreview);
  }

  function initBrandUpload(el) {
    if (!el || el.dataset.bound === '1') {
      return;
    }
    el.dataset.bound = '1';

    var input = el.querySelector('.wn-brand-upload__input');
    var empty = el.querySelector('.wn-brand-upload__empty');
    var preview = el.querySelector('.wn-brand-upload__preview');
    var previewImg = preview ? preview.querySelector('img') : null;
    var removeBtn = el.querySelector('.wn-brand-upload__remove');
    var status = el.querySelector('.wn-brand-upload__status');
    var uploading = false;

    function setStatus(text, isError) {
      if (!status) {
        return;
      }
      status.textContent = text || '';
      status.classList.toggle('d-none', !text);
      status.classList.toggle('text-danger', !!isError);
      status.classList.toggle('text-muted', !isError);
    }

    function showPreview(url) {
      if (!preview) {
        return;
      }
      if (!previewImg) {
        previewImg = document.createElement('img');
        preview.insertBefore(previewImg, preview.firstChild);
      }
      previewImg.src = url + (url.indexOf('?') >= 0 ? '&' : '?') + 't=' + Date.now();
      preview.classList.remove('d-none');
      if (empty) {
        empty.classList.add('d-none');
      }
      el.classList.add('has-image');
    }

    function clearPreview() {
      if (preview) {
        preview.classList.add('d-none');
      }
      if (previewImg) {
        previewImg.removeAttribute('src');
      }
      if (empty) {
        empty.classList.remove('d-none');
      }
      el.classList.remove('has-image');
      if (input) {
        input.value = '';
      }
    }

    function uploadFile(file) {
      if (!file || !/^image\//.test(file.type)) {
        window.alert('Selecciona una imagen válida (JPG, PNG, GIF o WebP).');
        return;
      }
      if (file.size > 5 * 1024 * 1024) {
        window.alert('La imagen no puede superar 5 MB. Prueba con otra más pequeña.');
        return;
      }

      var fd = new FormData();
      fd.append(el.dataset.param, file);
      uploading = true;
      el.classList.add('is-uploading');
      setStatus('Subiendo…', false);

      fetch(el.dataset.uploadUrl, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': cfg.csrf,
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: fd,
      })
        .then(function (res) {
          return res.json().then(function (data) {
            if (!res.ok) {
              var msg = (data && data.message) || 'No se pudo subir la imagen.';
              if (data && data.errors) {
                var firstKey = Object.keys(data.errors)[0];
                if (firstKey && data.errors[firstKey][0]) {
                  msg = data.errors[firstKey][0];
                }
              }
              throw new Error(msg);
            }
            return data;
          });
        })
        .then(function (data) {
          if (data.url) {
            showPreview(data.url);
            if (el.id === 'wn-upload-logo') {
              cfg.logoUrl = data.url;
            }
            if (el.id === 'wn-upload-header') {
              cfg.headerUrl = data.url;
            }
          }
          setStatus('Imagen guardada', false);
          setTimeout(function () {
            setStatus('');
          }, 2000);
          refreshPreview();
        })
        .catch(function (err) {
          setStatus(err.message || 'Error al subir', true);
          window.alert(err.message || 'No se pudo subir la imagen.');
        })
        .finally(function () {
          uploading = false;
          el.classList.remove('is-uploading');
          if (input) {
            input.value = '';
          }
        });
    }

    if (el.dataset.existingUrl) {
      showPreview(el.dataset.existingUrl);
    }

    el.addEventListener('click', function (e) {
      if (e.target.closest('.wn-brand-upload__remove') || uploading) {
        return;
      }
      input.click();
    });

    input.addEventListener('change', function () {
      if (input.files && input.files[0]) {
        uploadFile(input.files[0]);
      }
    });

    el.addEventListener('dragover', function (e) {
      e.preventDefault();
      el.classList.add('is-dragover');
    });

    el.addEventListener('dragleave', function () {
      el.classList.remove('is-dragover');
    });

    el.addEventListener('drop', function (e) {
      e.preventDefault();
      el.classList.remove('is-dragover');
      if (uploading) {
        return;
      }
      var file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
      if (file) {
        uploadFile(file);
      }
    });

    if (removeBtn) {
      removeBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (uploading) {
          return;
        }
        if (!window.confirm('¿Quitar esta imagen?')) {
          return;
        }
        setStatus('Eliminando…', false);
        fetch(el.dataset.deleteUrl, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': cfg.csrf,
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
        })
          .then(function (res) {
            if (!res.ok) {
              throw new Error('No se pudo eliminar la imagen.');
            }
            clearPreview();
            setStatus('');
            if (el.id === 'wn-upload-logo') {
              cfg.logoUrl = null;
            }
            if (el.id === 'wn-upload-header') {
              cfg.headerUrl = null;
            }
            refreshPreview();
          })
          .catch(function (err) {
            setStatus(err.message || 'Error al eliminar', true);
          });
      });
    }

    el._wnIsUploading = function () {
      return uploading;
    };
  }

  function initBrandUploads() {
    document.querySelectorAll('.wn-brand-upload').forEach(initBrandUpload);
  }

  $(function () {
    showStep(currentStep);

    $('.wn-studio-stepper__item, .wn-studio-nav .nav-link').on('click', function () {
      showStep($(this).data('wn-step'));
    });

    $('#wn-step-prev').on('click', function () {
      var idx = steps.indexOf(currentStep);
      if (idx > 0) {
        showStep(steps[idx - 1]);
      }
    });

    $('#wn-step-next').on('click', function () {
      var idx = steps.indexOf(currentStep);
      if (currentStep === 'identity') {
        var name = $('#company-name').val();
        if (!name || !String(name).trim()) {
          $('#company-name').addClass('is-invalid').focus();
          return;
        }
        $('#company-name').removeClass('is-invalid');
      }
      if (idx < steps.length - 1) {
        showStep(steps[idx + 1]);
      }
    });

    $('#wn-preview-refresh').on('click', refreshPreview);

    $('#wn-carta-preview').on('load', function () {
      setPreviewLoading(false);
    });

    bindTemplatePicker();
    bindPresets();
    bindColorSync();
    bindFontSelects();
    initBrandUploads();

    $('#company-studio-form').on('submit', function (e) {
      var busy = document.querySelector('.wn-brand-upload.is-uploading');
      if (busy) {
        e.preventDefault();
        window.alert('Espera a que termine de subir la imagen antes de guardar.');
      }
    });

    var tpl = $('#company-template').val();
    if (tpl) {
      updatePresetChips(tpl);
    }
    updatePreviewBadge();
    refreshPreview();
  });
})(jQuery);
