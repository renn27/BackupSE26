const CACHE_NAME = 'asisten-se2026-v2';
const CORE_ASSETS = [
  '/',
  '/manifest.webmanifest',
  '/images/logo-bps.svg',
  '/images/logo-se2026-small.png',
  '/images/asisten-se2026-icon-192.png',
  '/images/asisten-se2026-icon-512.png',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => Promise.all(
        CORE_ASSETS.map((asset) => cache.add(asset).catch(() => null))
      ))
      .finally(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  if (request.method !== 'GET' || url.origin !== self.location.origin) {
    return;
  }

  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(() =>
        caches.match('/').then((cachedResponse) =>
          cachedResponse || new Response('ASISTEN SE2026 sedang offline.', {
            headers: { 'Content-Type': 'text/plain;charset=utf-8' },
          })
        )
      )
    );
    return;
  }

  if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/images/') || url.pathname === '/manifest.webmanifest') {
    event.respondWith(
      caches.match(request).then((cachedResponse) => {
        const fetchPromise = fetch(request).then((networkResponse) => {
          const responseClone = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
          return networkResponse;
        }).catch(() => cachedResponse);

        return cachedResponse || fetchPromise;
      })
    );
  }
});

self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});
