// Clear Service Worker Cache Script
// Run this in browser console to clear all caches

console.log('Clearing GoalDocs caches...');

if ('caches' in window) {
  caches.keys().then((cacheNames) => {
    console.log('Found caches:', cacheNames);
    
    cacheNames.forEach((cacheName) => {
      if (cacheName.includes('goaldocs')) {
        console.log('Deleting cache:', cacheName);
        caches.delete(cacheName);
      }
    });
    
    console.log('Cache clearing complete!');
    console.log('Please refresh the page to see changes.');
  });
} else {
  console.log('Cache API not supported');
}

// Also clear service worker registration
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.getRegistrations().then((registrations) => {
    registrations.forEach((registration) => {
      console.log('Unregistering service worker:', registration.scope);
      registration.unregister();
    });
    console.log('Service worker unregistered. Please refresh the page.');
  });
}
