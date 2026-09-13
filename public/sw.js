/* FocusedTube service worker — V1 */

const VERSION = 'ft-v1-2025-01';
const STATIC_CACHE = `${VERSION}-static`;
const RUNTIME_CACHE = `${VERSION}-runtime`;

const PRECACHE_URLS = [
    '/offline',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => cache.addAll(PRECACHE_URLS)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((k) => !k.startsWith(VERSION))
                    .map((k) => caches.delete(k))
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') return;

    const url = new URL(request.url);

    // Same-origin only.
    if (url.origin !== self.location.origin) return;

    // Never cache admin, auth, private pages, or tracking endpoints.
    const neverCache = [
        '/admin', '/profile', '/watchlist', '/history',
        '/login', '/register', '/forgot-password', '/reset-password',
        '/verify-email', '/confirm-password',
        '/started', '/progress',
    ];
    if (neverCache.some((p) => url.pathname.startsWith(p))) return;

    // Static assets: cache-first.
    if (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/icons/') ||
        /\.(css|js|woff2?|png|jpg|jpeg|svg|webp|ico)$/.test(url.pathname)
    ) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) return cached;
                return fetch(request).then((response) => {
                    if (response.ok && response.type === 'basic') {
                        const copy = response.clone();
                        caches.open(RUNTIME_CACHE).then((c) => c.put(request, copy));
                    }
                    return response;
                });
            })
        );
        return;
    }

    // HTML: network-first, fall back to offline page.
    if (request.mode === 'navigate' || (request.headers.get('accept') || '').includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Do not cache responses that redirect to login.
                    if (response.redirected && new URL(response.url).pathname === '/login') {
                        return response;
                    }
                    const copy = response.clone();
                    caches.open(RUNTIME_CACHE).then((c) => c.put(request, copy));
                    return response;
                })
                .catch(() => caches.match(request).then((c) => c || caches.match('/offline')))
        );
    }
});