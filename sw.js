/**
 * Pronto Saúde - Service Worker de Alta Performance (PWA)
 * Cache Inteligente, Suporte Offline e Aceleração de Navegação
 */
const CACHE_NAME = 'prontosaude-pwa-v1';
const STATIC_ASSETS = [
    './',
    './assets/css/bootstrap.min.css',
    './assets/css/toastr.min.css',
    './assets/css/style.css',
    './assets/css/pronto-modern-frontend.css',
    './assets/js/jquery-2.2.4.min.js',
    './assets/js/bootstrap.min.js',
    './assets/js/pronto-global.js',
    './assets/js/pronto-drawer.js',
    './assets/uploads/logo.png',
    './manifest.webmanifest.php'
];

// Instalação do Service Worker
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(STATIC_ASSETS).catch(err => {
                console.warn('[PWA] Aviso no pré-cache:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// Ativação e Limpeza de Caches Antigos
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            );
        }).then(() => self.clients.claim())
    );
});

// Interceptação de Requisições de Rede (Fetch)
self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);

    // Ignora requisições que não sejam GET ou que sejam para o painel administrativo
    if (request.method !== 'GET' || url.pathname.includes('/admin/')) {
        return;
    }

    // 1. Páginas de Navegação (HTML): Estratégia Network First com Fallback para Cache
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then(response => {
                    if (response && response.status === 200) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(request, copy));
                    }
                    return response;
                })
                .catch(() => {
                    return caches.match(request).then(cached => {
                        return cached || caches.match('./');
                    });
                })
        );
        return;
    }

    // 2. Recursos Estáticos (CSS, JS, Imagens, Fontes): Estratégia Stale-While-Revalidate
    if (
        request.destination === 'style' ||
        request.destination === 'script' ||
        request.destination === 'image' ||
        request.destination === 'font'
    ) {
        event.respondWith(
            caches.match(request).then(cached => {
                const fetchPromise = fetch(request).then(networkResponse => {
                    if (networkResponse && networkResponse.status === 200) {
                        const copy = networkResponse.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(request, copy));
                    }
                    return networkResponse;
                }).catch(() => null);

                return cached || fetchPromise;
            })
        );
        return;
    }

    // Fallback padrão
    event.respondWith(
        caches.match(request).then(cached => {
            return cached || fetch(request);
        })
    );
});
