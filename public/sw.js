/**
 * RUANG UJIAN - PWA Service Worker
 * Version: 1.0.0
 */

const CACHE_NAME = 'ruang-ujian-pwa-v1.1.0';
const STATIC_ASSETS = [
    './assets/css/app.css',
    './assets/js/app.js',
    './assets/images/icons/icon-192.png',
    './assets/images/icons/icon-512.png',
    './assets/images/icons/icon-maskable.png',
    './assets/images/icons/icon-maskable-192.png',
    './assets/images/icons/apple-touch-icon.png',
    './assets/images/icons/icon.svg',
    './manifest.json',
    './offline.html'
];

// Install: pre-cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(STATIC_ASSETS).catch(err => {
                console.warn('[SW] Some assets failed to cache:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// Activate: clean old caches & claim clients
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch: Strategy
// 1. Navigation (HTML pages): Network-first with offline.html fallback
// 2. Static assets (CSS/JS/images/fonts): Cache-first with network fallback
self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);

    // Only handle GET requests and same-origin / static assets
    if (request.method !== 'GET') {
        return;
    }

    // Navigation requests (HTML pages)
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then(response => {
                    // If network responds ok, return it
                    return response;
                })
                .catch(async () => {
                    // If network fails (offline), return offline fallback page
                    const cache = await caches.open(CACHE_NAME);
                    const cachedOffline = await cache.match('./offline.html');
                    return cachedOffline || new Response('Sedang offline', {
                        headers: { 'Content-Type': 'text/plain; charset=utf-8' }
                    });
                })
        );
        return;
    }

    // Static assets: cache-first
    if (
        url.pathname.includes('/assets/') ||
        url.pathname.endsWith('.css') ||
        url.pathname.endsWith('.js') ||
        url.pathname.endsWith('.png') ||
        url.pathname.endsWith('.svg') ||
        url.pathname.endsWith('.woff2')
    ) {
        event.respondWith(
            caches.match(request).then(cachedResponse => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                return fetch(request).then(networkResponse => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then(cache => {
                            cache.put(request, responseToCache);
                        });
                    }
                    return networkResponse;
                }).catch(() => {
                    // Silent catch for missing asset
                });
            })
        );
        return;
    }

    // Default network-first for everything else
    event.respondWith(
        fetch(request).catch(async () => {
            const cached = await caches.match(request);
            return cached;
        })
    );
});
