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
      $('#theme_' + key + '_picker').val(hex);
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

  function bindColorSync() {
    $(document).on('input', 'input[type="color"][id$="_picker"], .theme-hex-input', function () {
      if (this.id && this.id.indexOf('_picker') !== -1) {
        var hexId = this.id.replace('_picker', '');
        $('#' + hexId).val(this.value);
      } else if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
        $('#' + this.id + '_picker').val(this.value);
      }
      debouncedRefreshPreview();
    });
  }

  function initDropzones() {
    if (typeof Dropzone === 'undefined') {
      return;
    }
    Dropzone.autoDiscover = false;

    new Dropzone('.dropzone-logo', {
      url: '/admin/companies/' + cfg.companyId + '/logo',
      paramName: 'logo',
      maxFiles: 1,
      maxFilesize: 5,
      acceptedFiles: 'image/*',
      addRemoveLinks: true,
      headers: { 'X-CSRF-TOKEN': cfg.csrf },
      dictDefaultMessage: '<i class="ri-upload-cloud-2-line d-block fs-3 mb-1"></i> Arrastra el logo o haz clic',
      init: function () {
        if (cfg.logoUrl) {
          var mock = { name: 'logo', size: 2000 };
          this.emit('addedfile', mock);
          this.emit('thumbnail', mock, cfg.logoUrl);
          this.emit('complete', mock);
        }
      },
      removedfile: function (file) {
        $.ajax({
          headers: { 'X-CSRF-TOKEN': cfg.csrf },
          type: 'DELETE',
          url: '/admin/companies/' + cfg.companyId + '/deletelogo',
        });
        if (file.previewElement) {
          file.previewElement.parentNode.removeChild(file.previewElement);
        }
        refreshPreview();
      },
      success: function () {
        refreshPreview();
      },
    });

    new Dropzone('.dropzone-header', {
      url: '/admin/companies/' + cfg.companyId + '/header',
      paramName: 'header',
      maxFiles: 1,
      maxFilesize: 5,
      acceptedFiles: 'image/*',
      addRemoveLinks: true,
      headers: { 'X-CSRF-TOKEN': cfg.csrf },
      dictDefaultMessage: '<i class="ri-image-line d-block fs-3 mb-1"></i> Imagen de cabecera',
      init: function () {
        if (cfg.headerUrl) {
          var mock = { name: 'header', size: 2000 };
          this.emit('addedfile', mock);
          this.emit('thumbnail', mock, cfg.headerUrl);
          this.emit('complete', mock);
        }
      },
      removedfile: function (file) {
        $.ajax({
          headers: { 'X-CSRF-TOKEN': cfg.csrf },
          type: 'DELETE',
          url: '/admin/companies/' + cfg.companyId + '/deleteheader',
        });
        if (file.previewElement) {
          file.previewElement.parentNode.removeChild(file.previewElement);
        }
        refreshPreview();
      },
      success: function () {
        refreshPreview();
      },
    });
  }

  $(function () {
    showStep(currentStep);

    $('.wn-studio-nav .nav-link').on('click', function () {
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
    initDropzones();

    var tpl = $('#company-template').val();
    if (tpl) {
      updatePresetChips(tpl);
    }
    updatePreviewBadge();
    refreshPreview();
  });
})(jQuery);
