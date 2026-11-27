/* Kolporte — service worker PWA.
 * Coquille en stale-while-revalidate ; jamais de cache pour les POST,
 * les pages connectées ni les réponses contenant des données de compte. */
'use strict';

const CACHE = 'kolporte-v1';
const HORS_LIGNE = '/hors-ligne';

// Ressources statiques éligibles au cache (coquille applicative)
function estEligible(url) {
    if (url.origin !== self.location.origin) {
        // Polices Google Fonts uniquement
        return url.hostname === 'fonts.googleapis.com' || url.hostname === 'fonts.gstatic.com';
    }
    return (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname === '/manifest.webmanifest' ||
        url.pathname === HORS_LIGNE
    );
}

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE).then((cache) => cache.add(HORS_LIGNE)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((cles) => Promise.all(cles.filter((c) => c !== CACHE && c.startsWith('kolporte-')).map((c) => caches.delete(c))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Jamais de cache pour les écritures
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // Navigation (page HTML) : réseau d'abord, repli hors-ligne si coupé.
    // Les pages connectées ne sont jamais mises en cache.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(HORS_LIGNE).then((reponse) => reponse || Response.error()))
        );
        return;
    }

    // Coquille : stale-while-revalidate sur les ressources éligibles uniquement
    if (estEligible(url)) {
        event.respondWith(
            caches.open(CACHE).then((cache) =>
                cache.match(request).then((cachee) => {
                    const reseau = fetch(request)
                        .then((reponse) => {
                            if (reponse && reponse.ok) {
                                cache.put(request, reponse.clone());
                            }
                            return reponse;
                        })
                        .catch(() => cachee);
                    return cachee || reseau;
                })
            )
        );
    }
});
