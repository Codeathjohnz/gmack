// G-Mack POS — minimal service worker.
//
// This only exists to make the site an installable PWA (Chrome/Android
// requires a registered service worker with a fetch handler before it will
// offer "Add to Home Screen" / "Install App"). It intentionally does NOT
// cache dynamic pages (dashboard, sales, statistics, etc.) — those always
// need fresh data from the server. It only caches the static app shell
// (logo/icons/manifest) so the install prompt and offline icon work.

const CACHE_NAME = 'gmack-pos-shell-v1';
const SHELL_ASSETS = [
    '/manifest.json',
    '/assets/images/logo.png',
    '/assets/images/icon-192.png',
    '/assets/images/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(SHELL_ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Only handle GET requests for our own static shell assets — cache-first
    // with a network fallback. Everything else (pages, POST, APIs) goes
    // straight to the network untouched so POS data is always live.
    const isShellAsset = SHELL_ASSETS.includes(url.pathname);

    if (event.request.method === 'GET' && isShellAsset) {
        event.respondWith(
            caches.match(event.request).then((cached) => cached || fetch(event.request))
        );
    }
});
