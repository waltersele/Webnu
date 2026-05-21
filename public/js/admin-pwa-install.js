(function () {
  'use strict';

  var deferredPrompt = null;

  var isStandalone = window.matchMedia('(display-mode: standalone)').matches
    || window.navigator.standalone === true;

  function initCard(card) {
    var installBtns = card.querySelectorAll('[data-pwa-install]');
    var installedEl = card.querySelector('[data-pwa-installed]');
    var instructionsEl = card.querySelector('[data-pwa-instructions]');
    var actionsEl = card.querySelector('[data-pwa-actions]');

    if (isStandalone) {
      if (installedEl) {
        installedEl.classList.remove('d-none');
      }
      if (instructionsEl) {
        instructionsEl.classList.add('d-none');
      }
      if (actionsEl) {
        actionsEl.classList.add('d-none');
      }
      installBtns.forEach(function (btn) {
        btn.classList.add('d-none');
      });
      return;
    }

    installBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!deferredPrompt) {
          return;
        }
        deferredPrompt.prompt();
        deferredPrompt.userChoice.finally(function () {
          deferredPrompt = null;
          installBtns.forEach(function (b) {
            b.classList.add('d-none');
          });
        });
      });
    });
  }

  function showInstallButtons() {
    if (!deferredPrompt) {
      return;
    }
    document.querySelectorAll('[data-pwa-install]').forEach(function (btn) {
      btn.classList.remove('d-none');
    });
  }

  function initTopbarTrigger() {
    var wrap = document.getElementById('wn-pwa-topbar-wrap');
    if (!wrap || isStandalone) {
      if (wrap) {
        wrap.classList.add('d-none');
      }
      return;
    }
    wrap.classList.remove('d-none');
  }

  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    deferredPrompt = e;
    showInstallButtons();
  });

  document.querySelectorAll('[data-pwa-card]').forEach(initCard);
  initTopbarTrigger();
})();
