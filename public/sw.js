/**
 * Sedekah.info — Service Worker
 *
 * Strategy:
 *   /build/*  → Cache-first  (immutable versioned assets)
 *   /storage/* → Cache-first (uploaded media, long-lived)
 *   HTML pages → Network-first with offline fallback
 *   /api/*    → Network only (never cache API responses)
 */

const CACHE_VERSION = 'v1';
const STATIC_CACHE  = `sedekah-static-${CACHE_VERSION}`;
const PAGES_CACHE   = `sedekah-pages-${CACHE_VERSION}`;

// ── Install ───────────────────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) =>
            cache.addAll(['/manifest.json', '/favicon.ico'])
        )
    );
    self.skipWaiting();
});

// ── Activate: clean up old cache versions ─────────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((k) => k !== STATIC_CACHE && k !== PAGES_CACHE)
                    .map((k) => caches.delete(k))
            )
        )
    );
    self.clients.claim();
});

// ── Fetch ─────────────────────────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Only handle GET requests on our own origin
    if (request.method !== 'GET' || url.origin !== self.location.origin) return;

    // Never intercept API or admin routes — always network
    if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/admin/')) return;

    // Cache-first: versioned build assets (JS/CSS bundles with hash in filename)
    if (url.pathname.startsWith('/build/')) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    // Cache-first: uploaded media (QR images, logos)
    if (url.pathname.startsWith('/storage/')) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    // Network-first: HTML pages (Inertia navigation)
    event.respondWith(networkFirst(request, PAGES_CACHE));
});

// ── Strategies ────────────────────────────────────────────────────────────────

async function cacheFirst(request, cacheName) {
    const cached = await caches.match(request);
    if (cached) return cached;

    const response = await fetch(request);
    if (response.ok) {
        const cache = await caches.open(cacheName);
        cache.put(request, response.clone());
    }
    return response;
}

async function networkFirst(request, cacheName) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(request);
        if (cached) return cached;

        // Offline fallback: return cached homepage if available
        const fallback = await caches.match('/');
        return fallback ?? new Response('Sedekah.info is offline. Please check your connection.', {
            status: 503,
            headers: { 'Content-Type': 'text/plain' },
        });
    }
}
