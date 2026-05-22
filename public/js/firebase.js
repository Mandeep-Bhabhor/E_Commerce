import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";

import {
    getFirestore,
    collection,
    addDoc,
    query,
    orderBy,
    onSnapshot,
    setDoc,
    doc,
    getDoc,
    where,
    getDocs,
    updateDoc,
} from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

import {
    getStorage,
    ref, // ← add
    uploadBytes, // ← add
    getDownloadURL, // ← add
} from "https://www.gstatic.com/firebasejs/11.6.1/firebase-storage.js";

const firebaseConfig = {
    apiKey: "AIzaSyDRvObf7ftLozM3wAYAEwzLmUD198o-YqM",

    authDomain: "laravel-store-notifications.firebaseapp.com",

    projectId: "laravel-store-notifications",

    storageBucket: "laravel-store-notifications.firebasestorage.app",

    messagingSenderId: "1062386711607",

    appId: "1:1062386711607:web:e880891377a5435d1d13b2",
};

const app = initializeApp(firebaseConfig);

/*
|--------------------------------------------------------------------------
| FIRESTORE
|--------------------------------------------------------------------------
*/

const db = getFirestore(app);

/*
|--------------------------------------------------------------------------
| STORAGE
|--------------------------------------------------------------------------
*/

const storage = getStorage(app);

/*
|--------------------------------------------------------------------------
| EXPORTS
|--------------------------------------------------------------------------
*/

export {
    db,
    storage,
    collection,
    addDoc,
    query,
    orderBy,
    onSnapshot,
    setDoc,
    doc,
    getDoc,
    where,
    getDocs,
    updateDoc,

    // Storage
    ref,
    uploadBytes,
    getDownloadURL,
};
