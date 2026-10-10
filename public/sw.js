// Disi Support service worker.
// Static assets: cache-first. Pages: network-first with an offline fallback.
// Only GET requests on this origin are handled; API/auth/websocket traffic and
// authenticated HTML are never cached.
const VERSION = 'v1';
const STATIC_CACHE = `disi-static-${VERSION}`;
const OFFLINE_URL = '/offline.html';
const PRECACHE = [OFFLINE_URL, '/disi-logo.avif', '/icons/icon-192.png', '/icons/icon-512.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== STATIC_CACHE).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;
    if (url.pathname.startsWith('/broadcasting') || url.pathname.startsWith('/livewire')) return;

    // Page navigations: network first, offline page as the fallback.
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
        return;
    }

    // Built/static assets: cache first.
    const isStatic = url.pathname.startsWith('/build/')
        || url.pathname.startsWith('/icons/')
        || url.pathname.startsWith('/fonts/')
        || url.pathname === '/disi-logo.avif';
    if (!isStatic) return;

    event.respondWith(
        caches.match(request).then((cached) => cached || fetch(request).then((response) => {
            if (response.ok) {
                const copy = response.clone();
                caches.open(STATIC_CACHE).then((cache) => cache.put(request, copy));
            }
            return response;
        }))
    );
});
