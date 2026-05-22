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
        installedEl.classList.remove('d-none', 'hidden');
      }
      if (instructionsEl) {
        instructionsEl.classList.add('d-none', 'hidden');
      }
      if (actionsEl) {
        actionsEl.classList.add('d-none', 'hidden');
      }
      installBtns.forEach(function (btn) {
        btn.classList.add('d-none', 'hidden');
      });
      return;
    }

    installBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        runInstallPrompt();
      });
    });
  }

  function runInstallPrompt() {
    if (!deferredPrompt) {
      return;
    }
    deferredPrompt.prompt();
    deferredPrompt.userChoice.finally(function () {
      deferredPrompt = null;
      document.querySelectorAll('[data-pwa-install]').forEach(function (btn) {
        btn.classList.add('d-none', 'hidden');
        btn.setAttribute('aria-hidden', 'true');
      });
      document.querySelectorAll('[data-pwa-install-badge]').forEach(function (el) {
        el.classList.remove('is-ready', 'wn-pwa-installable');
      });
    });
  }

  function showInstallUi() {
    document.querySelectorAll('[data-pwa-install]').forEach(function (btn) {
      btn.classList.remove('d-none', 'hidden');
      btn.removeAttribute('aria-hidden');
      btn.classList.add('is-ready', 'wn-pwa-installable');
    });
    document.querySelectorAll('[data-pwa-install-badge]').forEach(function (el) {
      el.classList.remove('d-none', 'hidden');
      el.classList.add('is-ready', 'wn-pwa-installable');
      el.removeAttribute('aria-hidden');
    });
    document.querySelectorAll('[data-pwa-topbar-wrap]').forEach(function (wrap) {
      wrap.classList.remove('d-none', 'hidden');
      wrap.classList.add('wn-pwa-installable');
    });
  }

  function hideInstallUiIfStandalone() {
    if (!isStandalone) {
      return;
    }
    document.querySelectorAll('[data-pwa-install-badge], [data-pwa-topbar-wrap]').forEach(function (el) {
      el.classList.add('d-none', 'hidden');
    });
  }

  function initBadges() {
    if (isStandalone) {
      hideInstallUiIfStandalone();
      return;
    }
    document.querySelectorAll('[data-pwa-install-badge]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (deferredPrompt) {
          runInstallPrompt();
        }
      });
    });
    document.querySelectorAll('[data-pwa-topbar-wrap] .wn-shell-topbar__pwa-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (deferredPrompt && !btn.getAttribute('data-bs-toggle')) {
          runInstallPrompt();
        }
      });
    });
  }

  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    deferredPrompt = e;
    showInstallUi();
  });

  window.addEventListener('appinstalled', function () {
    deferredPrompt = null;
    hideInstallUiIfStandalone();
  });

  document.querySelectorAll('[data-pwa-card]').forEach(initCard);
  initBadges();

  if (!isStandalone) {
    document.querySelectorAll('[data-pwa-topbar-wrap]').forEach(function (wrap) {
      wrap.classList.remove('d-none');
    });
  }
})();
