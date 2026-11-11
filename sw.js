// ============================================================
//  MusicMad – Service Worker
//  sw.js
//
//  Habilita instalación como PWA y un caché básico de los
//  recursos estáticos (CSS, JS, íconos) para que la interfaz
//  cargue más rápido y funcione sin conexión. Las páginas PHP
//  dependen de sesión/base de datos, así que siempre se piden
//  primero a la red y solo se sirve la copia guardada si el
//  dispositivo está sin conexión.
// ============================================================

const CACHE_NAME = 'musicmad-cache-v1';

const ASSETS_ESTATICOS = [
  'manifest.json',
  'img/icon-192.png',
  'img/icon-512.png',
  'img/home.png',
  'img/imgPer.png',
  'img/carrito.png',
  'img/imgMe.png',
  'img/imgMod.png',
  'img/imgNosotros.png',
  'CSS/styles.css',
  'CSS/Styke.css',
  'CSS/catalogo.css',
  'CSS/carrito.css',
  'CSS/checkout.css',
  'CSS/form-login.css',
  'CSS/form-registro.css',
  'CSS/form-modificar.css',
  'CSS/form-perfil.css',
  'js/controler.js',
  'js/perfil.js',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      // addAll falla completo si un solo recurso no existe,
      // por eso los agregamos de a uno y seguimos si alguno falla.
      return Promise.all(
        ASSETS_ESTATICOS.map((url) =>
          cache.add(url).catch(() => null)
        )
      );
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((nombres) =>
      Promise.all(
        nombres
          .filter((nombre) => nombre !== CACHE_NAME)
          .map((nombre) => caches.delete(nombre))
      )
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const { request } = event;

  if (request.method !== 'GET') {
    return;
  }

  const url = new URL(request.url);
  const esPHP = url.pathname.endsWith('.php') || url.pathname.endsWith('/');

  if (esPHP) {
    // Páginas dinámicas: red primero, caché solo como respaldo offline.
    event.respondWith(
      fetch(request)
        .then((respuesta) => {
          const copia = respuesta.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(request, copia));
          return respuesta;
        })
        .catch(() => caches.match(request))
    );
    return;
  }

  // Recursos estáticos: caché primero, red como respaldo.
  event.respondWith(
    caches.match(request).then((cacheada) => {
      if (cacheada) return cacheada;
      return fetch(request).then((respuesta) => {
        const copia = respuesta.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(request, copia));
        return respuesta;
      });
    })
  );
});
