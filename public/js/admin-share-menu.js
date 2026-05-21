(function () {
  function encodeUriComponentSafe(value) {
    return encodeURIComponent(String(value || ''));
  }

  function initShareMenu(root) {
    var url = root.getAttribute('data-share-url') || '';
    var title = root.getAttribute('data-share-title') || 'Mi carta';
    if (!url) {
      return;
    }

    var input = root.querySelector('[data-share-input]');
    var copyBtn = root.querySelector('[data-share-copy]');
    var nativeBtn = root.querySelector('[data-share-native]');
    var feedback = root.querySelector('[data-share-feedback]');

    if (input && !input.value) {
      input.value = url;
    }

    function showFeedback(message) {
      if (!feedback) {
        return;
      }
      feedback.textContent = message;
      feedback.classList.remove('d-none');
      window.setTimeout(function () {
        feedback.classList.add('d-none');
      }, 2200);
    }

    function copyUrl() {
      var text = input ? input.value : url;
      if (navigator.clipboard && navigator.clipboard.writeText) {
        return navigator.clipboard.writeText(text).then(function () {
          showFeedback('Enlace copiado');
        }).catch(fallbackCopy);
      }
      return fallbackCopy();
    }

    function fallbackCopy() {
      if (!input) {
        return Promise.reject();
      }
      input.focus();
      input.select();
      try {
        document.execCommand('copy');
        showFeedback('Enlace copiado');
        return Promise.resolve();
      } catch (e) {
        showFeedback('No se pudo copiar. Selecciona el enlace manualmente.');
        return Promise.reject(e);
      }
    }

    if (copyBtn) {
      copyBtn.addEventListener('click', function () {
        copyUrl();
      });
    }

    if (nativeBtn && navigator.share) {
      nativeBtn.classList.remove('d-none');
      nativeBtn.addEventListener('click', function () {
        navigator.share({
          title: title,
          text: 'Mira nuestra carta digital:',
          url: url,
        }).catch(function () {});
      });
    }

    root.querySelectorAll('[data-share-channel]').forEach(function (link) {
      link.addEventListener('click', function (event) {
        var channel = link.getAttribute('data-share-channel');
        var shareLink = '';
        var text = 'Mira la carta de ' + title + ':';

        if (channel === 'whatsapp') {
          shareLink = 'https://wa.me/?text=' + encodeUriComponentSafe(text + ' ' + url);
        } else if (channel === 'facebook') {
          shareLink = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeUriComponentSafe(url);
        } else if (channel === 'twitter') {
          shareLink = 'https://twitter.com/intent/tweet?url=' + encodeUriComponentSafe(url) + '&text=' + encodeUriComponentSafe(text);
        } else if (channel === 'email') {
          shareLink = 'mailto:?subject=' + encodeUriComponentSafe('Carta de ' + title) + '&body=' + encodeUriComponentSafe(text + '\n\n' + url);
        }

        if (!shareLink) {
          return;
        }

        if (channel === 'email') {
          link.setAttribute('href', shareLink);
          return;
        }

        event.preventDefault();
        window.open(shareLink, '_blank', 'noopener,noreferrer,width=600,height=520');
      });
    });
  }

  document.querySelectorAll('[data-share-menu]').forEach(initShareMenu);
})();
