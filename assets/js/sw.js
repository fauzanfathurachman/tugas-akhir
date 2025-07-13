// assets/js/sw.js - Service Worker for PWA
const CACHE_NAME = 'psb-cache-v1';
const OFFLINE_URL = '/offline.html';
const STATIC_ASSETS = [
  '/',
  '/index.php',
  '/assets/css/style.css',
  '/assets/css/mobile.css',
  '/assets/js/main.js',
  '/assets/js/pwa.js',
  '/manifest.json',
  OFFLINE_URL
];
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS))
  );
  self.skipWaiting();
});
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))))
  );
  self.clients.claim();
});
self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;
  event.respondWith(
    caches.match(event.request).then(response => {
      return response || fetch(event.request).catch(() => caches.match(OFFLINE_URL));
    })
  );
});
// Background sync & push notification logic can be added here
self.addEventListener('push', event => {
  const data = event.data ? event.data.json() : {};
  self.registration.showNotification(data.title || 'PSB Online', {
    body: data.body || '',
    icon: '/assets/icons/icon-192x192.png',
    badge: '/assets/icons/icon-72x72.png'
  });
});
