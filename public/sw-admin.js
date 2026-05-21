const CACHE = 'webnu-admin-v1';
const ASSETS = [
  '/materio/vendor/css/core.css',
  '/materio/css/webnu-theme.css',
  '/materio/css/webnu-admin-shell.css',
  '/materio/css/webnu-admin.css',
  '/adminlte/img/isotipo-color.png',
];

self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(CACHE).then(function (cache) {
      return cache.addAll(ASSETS).catch(function () {});
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', function (event) {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', function (event) {
  if (event.request.method !== 'GET') {
    return;
  }
  var url = new URL(event.request.url);
  if (url.pathname.indexOf('/admin') !== 0) {
    return;
  }
  event.respondWith(
    fetch(event.request).catch(function () {
      return caches.match(event.request);
    })
  );
});
