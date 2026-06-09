const CACHE_NAME = 'asisten-se2026-v3';
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

self.addEventListener('push', (event) => {
  let payload = {};

  if (event.data) {
    try {
      payload = event.data.json();
    } catch (error) {
      payload = { body: event.data.text() };
    }
  }

  const title = payload.title || 'ASISTEN SE2026';
  const options = {
    body: payload.body || 'Ada pengingat baru untuk Anda.',
    icon: payload.icon || '/images/asisten-se2026-icon-192.png',
    badge: payload.badge || '/images/asisten-se2026-icon-192.png',
    tag: payload.tag || 'asisten-se2026-reminder',
    data: {
      url: payload.url || '/',
    },
  };

  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const targetUrl = new URL(event.notification.data?.url || '/', self.location.origin).href;

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      for (const client of clientList) {
        if ('focus' in client && client.url === targetUrl) {
          return client.focus();
        }
      }

      if (self.clients.openWindow) {
        return self.clients.openWindow(targetUrl);
      }

      return null;
    })
  );
});
