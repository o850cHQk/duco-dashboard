// Installing service worker
const CACHE_NAME  = 'duco-dashboard';

/* Add relative URL of all the static content you want to store in
 * cache storage (this will help us use our app offline)*/
let resourcesToCache = [
    "/css/fontawesome-all.min.css",
    "/css/normalize.css",
    "/css/skeleton.css",
    "/images/duco512.png", 
    "/js/chart.js", 
    "/js/jquery-3.6.1.min.js",
    "/webfonts/fa-brands-400.ttf",
    "/webfonts/fa-brands-400.woff2",
    "/webfonts/fa-regular-400.ttf",
    "/webfonts/fa-regular-400.woff2",
    "/webfonts/fa-solid-900.ttf",
    "/webfonts/fa-solid-900.woff2",
    "/webfonts/fa-v4compatibility.ttf",
    "/webfonts/fa-v4compatibility.woff2"
];

self.addEventListener("install", e=>{
    e.waitUntil(
        caches.open(CACHE_NAME).then(cache =>{
            return cache.addAll(resourcesToCache);
        })
    );
});

// Cache and return requests
self.addEventListener("fetch", e=>{
    e.respondWith(
        caches.match(e.request).then(response=>{
            return response || fetch(e.request);
        })
    );
});

// Update a service worker
const cacheWhitelist = ['duco-dashboard'];
self.addEventListener('activate', event => {
    event.waitUntil(
      caches.keys().then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (cacheWhitelist.indexOf(cacheName) === -1) {
              return caches.delete(cacheName);
            }
          })
        );
      })
    );
  });