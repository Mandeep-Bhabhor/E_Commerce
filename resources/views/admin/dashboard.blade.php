@extends('layouts.admin')

@section('content')
    <style>
        .card {
            border-radius: 12px;
        }

        .card-header {
            font-size: 1.5rem;
            font-weight: 700;
            padding: 1rem 1.5rem;
        }
    </style>

    <div class="mx-auto" style="max-width: 900px;">
        <div class="card mt-4 border-0 shadow-sm">
            <h2 class="card-header bg-white fw-bold">Header & Footer Settings</h2>
            <div class="card-body p-4">

                <form method="POST" action="{{ route('settings.save') }}" enctype="multipart/form-data">
                    @csrf

                    @php
                        function decodeField($value)
                        {
                            if (is_null($value)) {
                                return [];
                            }
                            $decoded = is_string($value) ? json_decode($value, true) : $value;
                            return is_array($decoded) ? $decoded : [$value];
                        }

                        $emails = $settings ? decodeField($settings->email) : [];
                        $phones = $settings ? decodeField($settings->phone) : [];

                        $headerLogo = $settings->header_logo ?? null;
                        $footerLogo = $settings->footer_logo ?? null;
                    @endphp

                    <div class="mb-4">
                        <label class="form-label fw-bold">Emails</label>
                        <div id="emailRepeater">
                            @forelse ($emails as $email)
                                <div class="d-flex gap-2 mb-2 repeater-item">
                                    <input type="email" name="emails[]" class="form-control" value="{{ $email }}"
                                        placeholder="Enter email">
                                    <button type="button" class="btn btn-outline-danger remove-btn"><i
                                            class="fa fa-times"></i></button>
                                </div>
                            @empty
                                <div class="d-flex gap-2 mb-2 repeater-item">
                                    <input type="email" name="emails[]" class="form-control" placeholder="Enter email">
                                    <button type="button" class="btn btn-outline-danger remove-btn"><i
                                            class="fa fa-times"></i></button>
                                </div>
                            @endforelse
                        </div>
                        <button type="button" id="addEmail" class="btn btn-sm btn-primary mt-1">+ Add Email</button>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Phones</label>
                        <div id="phoneRepeater">
                            @forelse ($phones as $phone)
                                <div class="d-flex gap-2 mb-2 repeater-item">
                                    <input type="text" name="phones[]" class="form-control" value="{{ $phone }}"
                                        placeholder="Enter phone">
                                    <button type="button" class="btn btn-outline-danger remove-btn"><i
                                            class="fa fa-times"></i></button>
                                </div>
                            @empty
                                <div class="d-flex gap-2 mb-2 repeater-item">
                                    <input type="text" name="phones[]" class="form-control" placeholder="Enter phone">
                                    <button type="button" class="btn btn-outline-danger remove-btn"><i
                                            class="fa fa-times"></i></button>
                                </div>
                            @endforelse
                        </div>
                        <button type="button" id="addPhone" class="btn btn-sm btn-primary mt-1">+ Add Phone</button>
                    </div>

                    <hr class="my-4">

                    <div class="mb-4">
                        <label class="form-label fw-bold">Header Logo</label>
                        @if ($headerLogo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $headerLogo) }}" class="img-thumbnail"
                                    style="height: 80px; width: auto;">
                                <p class="text-muted small mt-1 mb-0">Current Logo</p>
                            </div>
                        @endif
                        <input type="file" name="header_logo" class="form-control">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Footer Logo</label>
                        @if ($footerLogo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $footerLogo) }}" class="img-thumbnail"
                                    style="height: 80px; width: auto;">
                                <p class="text-muted small mt-1 mb-0">Current Logo</p>
                            </div>
                        @endif
                        <input type="file" name="footer_logo" class="form-control">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Google Map Embed Code</label>
                        <textarea name="map_iframe" rows="3" class="form-control"
                            placeholder='Paste your <iframe...> tag from Google Maps here'>{{ $settings->map_iframe ?? '' }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function addRow(containerId) {
            let container = document.getElementById(containerId);
            let items = container.querySelectorAll('.repeater-item');
            let newRow = items[0].cloneNode(true);
            newRow.querySelectorAll('input').forEach(i => i.value = '');
            container.appendChild(newRow);
        }

        document.getElementById('addEmail').onclick = () => addRow('emailRepeater');
        document.getElementById('addPhone').onclick = () => addRow('phoneRepeater');

        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-btn')) {
                let repeater = e.target.closest('[id$="Repeater"]');
                if (repeater.querySelectorAll('.repeater-item').length > 1) {
                    e.target.closest('.repeater-item').remove();
                }
            }
        });
    </script>

    {{-- Firebase Push Notifications --}}
    <div class="mx-auto mt-4" style="max-width: 900px;">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">Push Notifications</h5>
                <p class="text-muted small mb-3">Enable browser notifications to be alerted when a new order is placed.</p>
                <button id="enableNotifications" class="btn btn-primary">
                    <i class="fa fa-bell me-2"></i>Enable Notifications
                </button>
                <p id="notif-status" class="mt-2 mb-0 small text-muted"></p>
            </div>
        </div>
    </div>
    <button id="getFcmToken" class="btn btn-dark">
        Get FCM Token
    </button>

    <script type="module">
        import {
            initializeApp
        } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
        import {
            getMessaging,
            getToken
        } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging.js";

        const firebaseConfig = {
            apiKey: "AIzaSyDRvObf7ftLozM3wAYAEwzLmUD198o-YqM",
            authDomain: "laravel-store-notifications.firebaseapp.com",
            projectId: "laravel-store-notifications",
            storageBucket: "laravel-store-notifications.firebasestorage.app",
            messagingSenderId: "1062386711607",
            appId: "1:1062386711607:web:e880891377a5435d1d13b2"
        };

        const app = initializeApp(firebaseConfig);
        const messaging = getMessaging(app);

        const btn = document.getElementById('enableNotifications');
        const statusEl = document.getElementById('notif-status');

        // Reflect current permission state on load
        if (Notification.permission === 'granted') {
            statusEl.textContent = '✅ Notifications already enabled';
            statusEl.className = 'mt-2 mb-0 small text-success';
            btn.disabled = true;
            btn.textContent = 'Already Enabled';
        } else if (Notification.permission === 'denied') {
            statusEl.textContent = '🚫 Notifications blocked — allow them in your browser settings';
            statusEl.className = 'mt-2 mb-0 small text-danger';
            btn.disabled = true;
        }

        btn.addEventListener('click', async () => {
            btn.disabled = true;
            statusEl.textContent = 'Requesting permission…';
            statusEl.className = 'mt-2 mb-0 small text-muted';

            try {
                const permission = await Notification.requestPermission();
                console.log('[FCM] Permission:', permission);

                if (permission !== 'granted') {
                    statusEl.textContent = '🚫 Permission denied.';
                    statusEl.className = 'mt-2 mb-0 small text-danger';
                    btn.disabled = false;
                    return;
                }

                // Register service worker and wait until it is active
                await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                console.log('[FCM] SW registered');

                // Wait for the service worker to become active before subscribing
                const registration = await navigator.serviceWorker.ready;
                console.log('[FCM] SW active and ready');

                const token = await getToken(messaging, {
                    vapidKey: "{{ env('FIREBASE_VAPID_KEY') }}",
                    serviceWorkerRegistration: registration
                });
                console.log('[FCM] Token:', token);

                if (!token) {
                    statusEl.textContent = '❌ No token returned. Check VAPID key.';
                    statusEl.className = 'mt-2 mb-0 small text-danger';
                    btn.disabled = false;
                    return;
                }

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const response = await fetch('/save-fcm-token', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        token: token
                    })
                });

                console.log('[FCM] Save response status:', response.status);

                if (!response.ok) {
                    const text = await response.text();
                    console.error('[FCM] Save failed:', text);
                    statusEl.textContent = '❌ Failed to save token (HTTP ' + response.status + ')';
                    statusEl.className = 'mt-2 mb-0 small text-danger';
                    btn.disabled = false;
                    return;
                }

                statusEl.textContent = '✅ Notifications enabled! You will be alerted on new orders.';
                statusEl.className = 'mt-2 mb-0 small text-success';
                btn.textContent = 'Enabled';

            } catch (err) {
                console.error('[FCM] Error:', err);
                statusEl.textContent = '❌ Error: ' + err.message;
                statusEl.className = 'mt-2 mb-0 small text-danger';
                btn.disabled = false;
            }
        });


        const tokenBtn = document.getElementById('getFcmToken');

        tokenBtn.addEventListener('click', async () => {

            try {

                const permission = await Notification.requestPermission();

                if (permission !== 'granted') {
                    console.log('Permission denied');
                    return;
                }

                await navigator.serviceWorker.register('/firebase-messaging-sw.js');

                const registration = await navigator.serviceWorker.ready;

                const token = await getToken(messaging, {
                    vapidKey: "{{ env('FIREBASE_VAPID_KEY') }}",
                    serviceWorkerRegistration: registration
                });

                console.log('FCM TOKEN:');
                console.log(token);

            } catch (error) {

                console.error(error);

            }

        });
    </script>
@endsection
