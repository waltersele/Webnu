/**
 * @deprecated Use measurement.js — kept as a one-release stub for cached pages.
 */
(function () {
  var s = document.createElement('script');
  s.src = (document.currentScript && document.currentScript.src
    ? document.currentScript.src.replace(/webnu-measurement\.js.*/, 'measurement.js')
    : '/js/measurement.js');
  s.defer = true;
  document.head.appendChild(s);
})();
