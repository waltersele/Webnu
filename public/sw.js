/**
 * Service worker mínimo para instalabilidad PWA en la landing (scope /).
 */
const CACHE = 'webnu-public-v2';

self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(CACHE).then(function (cache) {
      return cache.addAll([
        '/img/pwa/icon-192.png',
        '/img/pwa/icon-512.png',
      ]).catch(function () {});
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
  event.respondWith(fetch(event.request));
});
