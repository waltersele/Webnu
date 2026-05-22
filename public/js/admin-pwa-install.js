/**
 * @deprecated Usar webnu-pwa-install.js
 */
(function () {
  var s = document.createElement('script');
  s.src = '/js/webnu-pwa-install.js';
  s.async = false;
  document.currentScript.parentNode.insertBefore(s, document.currentScript.nextSibling);
})();
