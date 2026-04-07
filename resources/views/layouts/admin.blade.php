<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Admin Panel') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-100 text-gray-800">

    <div class="min-h-screen flex flex-col">

        <header class="bg-white border-b px-6 h-16 flex items-center justify-between">

            <div class="flex items-center gap-3">
                <i class="fa fa-store text-indigo-600 text-xl"></i>
                <span class="font-bold text-indigo-600 text-lg">Admin Panel</span>
            </div>

            <nav class="flex items-center gap-6 text-sm font-medium">
                <a href="{{ route('admin.dashboard') }}"
                    class="{{ request()->routeIs('admin.dashboard') ? 'text-indigo-600' : 'text-gray-600 hover:text-indigo-600' }}">Dashboard</a>
                <a href="{{ route('products.index') }}"
                    class="{{ request()->routeIs('products*') ? 'text-indigo-600' : 'text-gray-600 hover:text-indigo-600' }}">Products</a>
                <a href="{{ route('admin.orders.index') }}"
                    class="{{ request()->routeIs('admin.orders*') ? 'text-indigo-600' : 'text-gray-600 hover:text-indigo-600' }}">Orders</a>
                <a href="{{ route('sizes.index') }}"
                    class="{{ request()->routeIs('sizes*') ? 'text-indigo-600' : 'text-gray-600 hover:text-indigo-600' }}">Sizes</a>
                <a href="{{ route('categories.index') }}"
                    class="{{ request()->routeIs('categories*') ? 'text-indigo-600' : 'text-gray-600 hover:text-indigo-600' }}">Categories</a>
                <a href="{{ route('colors.index') }}"
                    class="{{ request()->routeIs('colors*') ? 'text-indigo-600' : 'text-gray-600 hover:text-indigo-600' }}">Colors</a>
                <a href="{{ route('taxes.index') }}"
                    class="{{ request()->routeIs('taxes*') ? 'text-indigo-600' : 'text-gray-600 hover:text-indigo-600' }}">Tax</a>
                <a href="{{ route('discounts.index') }}"
                    class="{{ request()->routeIs('discounts*') ? 'text-indigo-600' : 'text-gray-600 hover:text-indigo-600' }}">Discount</a>
                <a href="{{ route('settings.view') }}"
                    class="'text-indigo-600' : 'text-gray-600 hover:text-indigo-600'">Settings</a>
            </nav>

            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-red-500 text-sm hover:text-red-600">Logout</button>
                </form>
            </div>

        </header>

        <main class="p-6 flex-1 overflow-y-auto">
            @yield('content')

            <div class="mt-8 text-center">
                <button id="enable-notifications-btn"
                    style="padding: 10px 20px; background: #4f46e5; color: white; border: none; border-radius: 5px; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <i class="fa fa-bell mr-2"></i> Enable Notifications
                </button>
            </div>
        </main>

        <footer class="bg-white border-t text-center py-3 text-sm text-gray-500">
            <span id="footerContent">
                © {{ date('Y') }} Admin Panel • Built with Laravel
            </span>
        </footer>

    </div>

    <script type="module">
        import {
            initializeApp
        } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
        import {
            getMessaging,
            getToken,
            onMessage
        } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging.js";

        // 🚨 DON'T FORGET TO PASTE YOUR ACTUAL FIREBASE KEYS HERE! 🚨
        const firebaseConfig = {
            apiKey: "AIzaSyDRvObf7ftLozM3wAYAEwzLmUD198o-YqM",
            authDomain: "laravel-store-notifications.firebaseapp.com",
            projectId: "laravel-store-notifications",
            storageBucket: "laravel-store-notifications.firebasestorage.app",
            messagingSenderId: "1062386711607",
            appId: "1:1062386711607:web:e880891377a5435d1d13b2",
            measurementId: "G-T1DQTMWF1N"
        };

        const app = initializeApp(firebaseConfig);
        const messaging = getMessaging(app);

        // 🚨 DON'T FORGET YOUR VAPID KEY HERE! 🚨
        const vapidKey = "BD5Jy0eDb6VG3EiD1JWHb1KBOxU4EjHTO4aTg35ajNGcpLedlSxJaj6hyMa51gUdrsJfUvEz0rIPHudsE7LtKx8";

        document.getElementById('enable-notifications-btn').addEventListener('click', async () => {
            try {
                console.log('Requesting permission...');
                const permission = await Notification.requestPermission();

                if (permission === 'granted') {
                    console.log('Permission granted! Fetching token...');
                    const token = await getToken(messaging, {
                        vapidKey: vapidKey
                    });

                    if (token) {
                        console.log('🎉 YOUR UNIQUE TOKEN IS:', token);
                        alert("Notifications Enabled! Check your browser console for the token.");

                        // 👇 ADD THIS NEW CODE RIGHT HERE 👇
                        fetch('/save-fcm-token', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .getAttribute('content')
                                },
                                body: JSON.stringify({
                                    token: token
                                })
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Laravel rejected the request (Status ' + response
                                        .status + ')');
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log('✅ SUCCESS: Token securely saved to Laravel database!');
                            })
                            .catch(error => {
                                console.error('❌ FAILED to save token to database:', error);
                            });
                        // 👆 END OF NEW CODE 👆
                    } else {
                        console.log('No registration token available.');
                    }
                } else {
                    console.log('User denied notification permission.');
                }
            } catch (error) {
                console.error('An error occurred while retrieving token:', error);
            }
        });

        onMessage(messaging, (payload) => {
            console.log('Message received while site is open: ', payload);
            alert(`New Message: ${payload.notification.title} - ${payload.notification.body}`);
        });
    </script>

</body>

</html>
