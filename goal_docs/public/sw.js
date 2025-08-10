const CACHE_NAME = 'goaldocs-v1.0.3';
const STATIC_CACHE_NAME = 'goaldocs-static-v1.0.3';
const DYNAMIC_CACHE_NAME = 'goaldocs-dynamic-v1.0.3';

// Static assets to cache
const STATIC_ASSETS = [
  '/',
  '/files',
  '/search',
  '/manifest.json',
  '/assets/vendor/css/rtl/core.css',
  '/assets/vendor/css/rtl/theme-default.css',
  '/assets/css/demo.css',
  '/assets/vendor/fonts/tabler-icons.css',
  '/assets/vendor/fonts/fontawesome.css',
  '/assets/vendor/js/bootstrap.js',
  '/assets/vendor/libs/jquery/jquery.js',
  '/assets/js/main.js',
  '/assets/img/favicon/favicon.ico'
];

// API endpoints to cache (GET requests only)
const CACHEABLE_APIS = [
  '/api/files',
  '/api/folders',
  '/comments'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  console.log('Service Worker: Installing...');
  
  event.waitUntil(
    caches.open(STATIC_CACHE_NAME)
      .then((cache) => {
        console.log('Service Worker: Caching static assets');
        return cache.addAll(STATIC_ASSETS);
      })
      .then(() => {
        console.log('Service Worker: Static assets cached');
        return self.skipWaiting();
      })
      .catch((error) => {
        console.error('Service Worker: Error caching static assets:', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('Service Worker: Activating...');
  
  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            if (cacheName !== STATIC_CACHE_NAME && cacheName !== DYNAMIC_CACHE_NAME) {
              console.log('Service Worker: Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('Service Worker: Activated');
        return self.clients.claim();
      })
  );
});

// Fetch event - serve from cache or network
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }
  
  // Skip cross-origin requests
  if (url.origin !== location.origin) {
    return;
  }

  event.respondWith(
    caches.match(request)
      .then((cachedResponse) => {
        // Return cached version if available
        if (cachedResponse) {
          console.log('Service Worker: Serving from cache:', request.url);
          return cachedResponse;
        }

        // Network first strategy for API calls
        if (isApiRequest(request.url)) {
          return networkFirstStrategy(request);
        }

        // Cache first strategy for static assets
        if (isStaticAsset(request.url)) {
          return cacheFirstStrategy(request);
        }

        // Network first for pages
        return networkFirstStrategy(request);
      })
      .catch((error) => {
        console.error('Service Worker: Fetch error:', error);
        
        // Return offline page for navigation requests
        if (request.mode === 'navigate') {
          return caches.match('/offline.html') || new Response('Offline - GoalDocs', {
            status: 200,
            headers: { 'Content-Type': 'text/html' }
          });
        }
        
        return new Response('Network error', { status: 408 });
      })
  );
});

// Network first strategy with cache fallback
function networkFirstStrategy(request) {
  return fetch(request)
    .then((response) => {
      // Clone the response
      const responseClone = response.clone();
      
      // Cache successful responses
      if (response.status === 200) {
        caches.open(DYNAMIC_CACHE_NAME)
          .then((cache) => {
            cache.put(request, responseClone);
          });
      }
      
      return response;
    })
    .catch(() => {
      // Return cached version if network fails
      return caches.match(request);
    });
}

// Cache first strategy with network fallback
function cacheFirstStrategy(request) {
  return caches.match(request)
    .then((cachedResponse) => {
      if (cachedResponse) {
        return cachedResponse;
      }
      
      return fetch(request)
        .then((response) => {
          const responseClone = response.clone();
          
          if (response.status === 200) {
            caches.open(DYNAMIC_CACHE_NAME)
              .then((cache) => {
                cache.put(request, responseClone);
              });
          }
          
          return response;
        });
    });
}

// Check if request is for API
function isApiRequest(url) {
  return CACHEABLE_APIS.some(api => url.includes(api));
}

// Check if request is for static asset
function isStaticAsset(url) {
  return url.includes('/assets/') || 
         url.includes('/favicon') || 
         url.includes('manifest.json');
}

// Background sync for offline actions
self.addEventListener('sync', (event) => {
  console.log('Service Worker: Background sync triggered:', event.tag);
  
  if (event.tag === 'file-upload') {
    event.waitUntil(syncFileUploads());
  }
  
  if (event.tag === 'comment-post') {
    event.waitUntil(syncComments());
  }
});

// Sync file uploads when back online
async function syncFileUploads() {
  try {
    // Get stored upload data from IndexedDB
    const uploads = await getStoredUploads();
    
    for (const upload of uploads) {
      try {
        await fetch('/files/upload', {
          method: 'POST',
          body: upload.formData,
          headers: upload.headers
        });
        
        // Remove from storage after successful upload
        await removeStoredUpload(upload.id);
        
        // Notify user
        self.registration.showNotification('File uploaded successfully', {
          body: `${upload.fileName} has been uploaded`,
          icon: '/assets/img/favicon/android-chrome-192x192.png',
          badge: '/assets/img/favicon/favicon.ico'
        });
        
      } catch (error) {
        console.error('Failed to sync upload:', error);
      }
    }
  } catch (error) {
    console.error('Error syncing uploads:', error);
  }
}

// Sync comments when back online
async function syncComments() {
  try {
    const comments = await getStoredComments();
    
    for (const comment of comments) {
      try {
        await fetch('/comments', {
          method: 'POST',
          body: JSON.stringify(comment.data),
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': comment.csrfToken
          }
        });
        
        await removeStoredComment(comment.id);
        
      } catch (error) {
        console.error('Failed to sync comment:', error);
      }
    }
  } catch (error) {
    console.error('Error syncing comments:', error);
  }
}

// Placeholder functions for IndexedDB operations
async function getStoredUploads() {
  // Implementation would use IndexedDB
  return [];
}

async function removeStoredUpload(id) {
  // Implementation would use IndexedDB
}

async function getStoredComments() {
  // Implementation would use IndexedDB
  return [];
}

async function removeStoredComment(id) {
  // Implementation would use IndexedDB
}

// Push notification handling
self.addEventListener('push', (event) => {
  console.log('Service Worker: Push notification received');
  
  const options = {
    body: event.data ? event.data.text() : 'New activity in GoalDocs',
    icon: '/assets/img/favicon/android-chrome-192x192.png',
    badge: '/assets/img/favicon/favicon.ico',
    vibrate: [200, 100, 200],
    data: {
      url: '/files'
    },
    actions: [
      {
        action: 'view',
        title: 'View',
        icon: '/assets/img/favicon/favicon.ico'
      },
      {
        action: 'dismiss',
        title: 'Dismiss'
      }
    ]
  };
  
  event.waitUntil(
    self.registration.showNotification('GoalDocs', options)
  );
});

// Notification click handling
self.addEventListener('notificationclick', (event) => {
  console.log('Service Worker: Notification clicked');
  
  event.notification.close();
  
  if (event.action === 'view') {
    event.waitUntil(
      clients.openWindow(event.notification.data.url || '/files')
    );
  }
}); 