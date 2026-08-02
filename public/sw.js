/* Service Worker - Aplikasi Ketertiban v1 */
const CACHE = 'ketertiban-v1';
const APP_SHELL = ['/', '/login'];

self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open(CACHE)
      .then((c) => c.addAll(APP_SHELL))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (e) => {
  const req = e.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  if (url.origin !== location.origin) return;

  // API: selalu ambil dari jaringan (data harus segar)
  if (url.pathname.startsWith('/notifications') ||
      url.pathname.startsWith('/api/') ||
      url.pathname.startsWith('/calendar/')) {
    return;
  }

  // Navigasi halaman: network-first, fallback ke cache saat offline
  if (req.mode === 'navigate') {
    e.respondWith(
      fetch(req)
        .then((res) => {
          const copy = res.clone();
          caches.open(CACHE).then((c) => c.put('/offline-shell', copy));
          return res;
        })
        .catch(() =>
          caches.match('/offline-shell').then((r) => r || caches.match('/'))
        )
    );
    return;
  }

  // Asset statis (build, icons, font): cache-first
  e.respondWith(
    caches.match(req).then((cached) =>
      cached ||
      fetch(req).then((res) => {
        if (res.ok) {
          const copy = res.clone();
          caches.open(CACHE).then((c) => c.put(req, copy));
        }
        return res;
      })
    )
  );
});
