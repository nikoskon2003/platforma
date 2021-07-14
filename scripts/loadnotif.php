<?php
  header('Content-Type: application/javascript');
  require_once  '../includes/config.php';
?>
//<script>
let once = false;
document.addEventListener('click', (e) => { if(!once){once = true; push_subscribe();} });
document.addEventListener('DOMContentLoaded', () => {
  const applicationServerKey = '<?= $publicKey; ?>';
  let isPushEnabled = false;

  push_subscribe();

  if (!('serviceWorker' in navigator)) return;
  if (!('PushManager' in window)) return;
  if (!('showNotification' in ServiceWorkerRegistration.prototype)) return;
  if (Notification.permission === 'denied') {
    console.warn('Notifications are denied by the user');
    return;
  }

  navigator.serviceWorker.register('serviceWorker.php').then(() => {push_updateSubscription();}, e => {console.error('[SW] Service worker registration failed', e);});

  function urlBase64ToUint8Array(s) {
    const p = '='.repeat((4 - (s.length % 4)) % 4);
    const b = (s + p).replace(/\-/g, '+').replace(/_/g, '/');
    const r = window.atob(b);
    const o = new Uint8Array(r.length);
    for (let i = 0; i < r.length; ++i) o[i] = r.charCodeAt(i);
    return o;
  }

  function checkNotificationPermission() {
    return new Promise((resolve, reject) => {
      if (Notification.permission === 'denied') 
        return reject(new Error('Push messages are blocked'));
      if (Notification.permission === 'granted') 
        return resolve();
      else /*(Notification.permission === 'default')*/ 
        return Notification.requestPermission().then(result => {
          if (result !== 'granted') 
            reject(new Error('Bad permission result'));
          resolve();
        });
    });
  }

  function push_subscribe() {
    return checkNotificationPermission()
      .then(() => navigator.serviceWorker.ready)
      .then(serviceWorkerRegistration =>
        serviceWorkerRegistration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: urlBase64ToUint8Array(applicationServerKey),
        })
      )
      .then(subscription => {
        return sendSubscriptionData(subscription, 'POST');
      })
      .then(subscription => subscription)
      .catch(e => {
        if (Notification.permission === 'denied') 
          console.warn('Notifications are denied by the user.');
        else 
          console.error('Impossible to subscribe to push notifications', e);
      });
  }

  function push_updateSubscription() {
    navigator.serviceWorker.ready
      .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.getSubscription())
      .then(subscription => {
        if (!subscription) 
          return;
        return sendSubscriptionData(subscription, 'PUT');
      })
      .then(subscription => subscription)
      .catch(e => {
        console.error('Error when updating the subscription', e);
      });
  }

  function push_unsubscribe() {
    navigator.serviceWorker.ready
      .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.getSubscription())
      .then(subscription => {
        if (!subscription)
          return;
        return sendSubscriptionData(subscription, 'DELETE');
      })
      .then(subscription => subscription.unsubscribe())
      .catch(e => {
        console.error('Error when unsubscribing the user', e);
      });
  }

  function sendSubscriptionData(subscription, method) {
    const key = subscription.getKey('p256dh');
    const token = subscription.getKey('auth');
    const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
    return fetch('includes/push_subscription.inc.php', {
      method,
      body: JSON.stringify({
        endpoint: subscription.endpoint,
        publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
        authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
        contentEncoding,
      }),
    }).then(() => subscription);
  }
});