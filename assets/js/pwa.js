// assets/js/pwa.js - PWA registration & features
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/assets/js/sw.js').then(reg => {
      // Background sync, push, update notification
    });
  });
}
// Add to homescreen prompt
let deferredPrompt;
window.addEventListener('beforeinstallprompt', e => {
  e.preventDefault();
  deferredPrompt = e;
  document.getElementById('addToHomeBtn')?.classList.remove('d-none');
});
document.getElementById('addToHomeBtn')?.addEventListener('click', () => {
  if (deferredPrompt) {
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then(() => { deferredPrompt = null; });
  }
});
});
// IndexedDB for offline data (example)
// ...
