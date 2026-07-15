const CACHE_VERSION = '1.0.4';
const CACHE_NAME = `antrian-cache-${CACHE_VERSION}`;
const RUNTIME_CACHE = `antrian-runtime-${CACHE_VERSION}`;

const PRECACHE_ASSETS = [
  '/client/display',
  '/assets/frameworks/bootstrap/css/bootstrap.min.css',
  '/assets/frameworks/font-awesome/css/font-awesome.min.css',
  '/assets/frameworks/ionicons/css/ionicons.min.css',
  '/assets/frameworks/adminlte/css/adminlte.min.css',
  '/assets/frameworks/adminlte/css/skins/skin-blue.min.css',
  '/assets/frameworks/jquery/jquery.min.js',
  '/assets/frameworks/bootstrap/js/bootstrap.min.js',
  '/assets/frameworks/adminlte/js/adminlte.min.js',
  '/assets/frameworks/domprojects/css/client.min.css',
  '/assets/frameworks/domprojects/js/client.min.js',
  '/assets/frameworks/domprojects/js/tts.js',
  '/assets/audio/simple_notification.wav',
  '/assets/audio/antrian.wav',
  '/assets/audio/nomor-urut.wav',
  '/assets/audio/loket.wav',
  '/offline.html'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(PRECACHE_ASSETS))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames
          .filter(name => name.startsWith('antrian-') && name !== CACHE_NAME && name !== RUNTIME_CACHE)
          .map(name => caches.delete(name))
      );
    }).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Network first untuk Socket.io dan API
  if (url.pathname.includes('/socket.io/') || 
      url.pathname.includes('/api/') ||
      url.pathname.includes('/health')) {
    event.respondWith(
      fetch(request)
        .catch(() => caches.match('/offline.html'))
    );
    return;
  }

  // Cache first untuk static assets
  if (request.destination === 'style' || 
      request.destination === 'script' || 
      request.destination === 'font' ||
      request.destination === 'audio') {
    event.respondWith(
      caches.match(request)
        .then(response => response || fetch(request).then(fetchResponse => {
          return caches.open(RUNTIME_CACHE).then(cache => {
            cache.put(request, fetchResponse.clone());
            return fetchResponse;
          });
        }))
        .catch(() => caches.match('/offline.html'))
    );
    return;
  }

  // Stale-while-revalidate untuk halaman
  event.respondWith(
    caches.match(request)
      .then(cachedResponse => {
        const fetchPromise = fetch(request).then(networkResponse => {
          caches.open(CACHE_NAME).then(cache => {
            cache.put(request, networkResponse.clone());
          });
          return networkResponse;
        });
        return cachedResponse || fetchPromise;
      })
      .catch(() => caches.match('/offline.html'))
  );
});

self.addEventListener('message', event => {
  if (event.data.action === 'skipWaiting') {
    self.skipWaiting();
  }
});
