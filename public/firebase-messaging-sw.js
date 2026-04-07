// public/firebase-messaging-sw.js

// 1. Import Firebase Background Libraries
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging-compat.js');

// 2. PASTE YOUR CONFIG HERE
const firebaseConfig = {
  apiKey: "AIzaSyDRvObf7ftLozM3wAYAEwzLmUD198o-YqM",
  authDomain: "laravel-store-notifications.firebaseapp.com",
  projectId: "laravel-store-notifications",
  storageBucket: "laravel-store-notifications.firebasestorage.app",
  messagingSenderId: "1062386711607",
  appId: "1:1062386711607:web:e880891377a5435d1d13b2",
  measurementId: "G-T1DQTMWF1N"
};

// 3. Initialize Firebase
firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

// 4. Handle Background Notifications
messaging.onBackgroundMessage((payload) => {
  console.log('Received background message: ', payload);

  const notificationTitle = payload.notification.title;
  const notificationOptions = {
    body: payload.notification.body,
    icon: '/favicon.ico' // You can change this to your store's logo later!
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});