@extends('layouts.admin')

@section('content')
    <style>
        .bg-light-green { background-color: #dcf8c6 !important; }

        /* Favourite star on user list */
        .fav-star {
            cursor: pointer;
            font-size: 16px;
            color: #ccc;
            transition: color 0.2s;
        }
        .fav-star.active {
            color: #ffc107;
        }
        .fav-star:hover {
            color: #ffca2c;
        }

        /* Star on messages */
        .msg-star {
            cursor: pointer;
            font-size: 12px;
            color: #ccc;
            opacity: 0;
            transition: opacity 0.2s, color 0.2s;
            margin-left: 6px;
        }
        .msg-star.active {
            color: #ffc107;
            opacity: 1;
        }
        .msg-bubble-wrap:hover .msg-star {
            opacity: 1;
        }

        /* Reply icon on messages */
        .msg-reply {
            cursor: pointer;
            font-size: 12px;
            color: #999;
            opacity: 0;
            transition: opacity 0.2s;
            margin-left: 4px;
        }
        .msg-bubble-wrap:hover .msg-reply {
            opacity: 1;
        }
        .msg-reply:hover {
            color: #0d6efd;
        }

        /* Reply preview bar */
        .reply-preview {
            background: #e3f2fd;
            border-left: 3px solid #0d6efd;
            padding: 6px 12px;
            font-size: 13px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        .reply-preview .reply-cancel {
            cursor: pointer;
            font-size: 18px;
            color: #999;
            line-height: 1;
        }
        .reply-preview .reply-cancel:hover {
            color: #333;
        }

        /* Reply quote inside bubble */
        .reply-quote {
            background: rgba(0,0,0,0.06);
            border-left: 3px solid #0d6efd;
            padding: 4px 8px;
            margin-bottom: 6px;
            border-radius: 4px;
            font-size: 12px;
            color: #555;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0; }
        }
    </style>
    <div class="container py-4">

        <div class="row">

            <!-- LEFT SIDE -->

            <div class="col-md-4">

                <div class="card shadow-sm">

                    <div class="card-header fw-bold p-0">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold" id="tab-all-chats" data-bs-toggle="tab" data-bs-target="#panel-all-chats" type="button" role="tab">
                                    All Chats
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold" id="tab-fav-users" data-bs-toggle="tab" data-bs-target="#panel-fav-users" type="button" role="tab">
                                    ★ Favourites
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content">
                        <!-- All Chats Tab -->
                        <div class="tab-pane fade show active" id="panel-all-chats" role="tabpanel">
                            <div class="list-group list-group-flush" id="chat-users">
                            </div>
                        </div>

                        <!-- Favourite Users Tab -->
                        <div class="tab-pane fade" id="panel-fav-users" role="tabpanel">
                            <div class="list-group list-group-flush" id="fav-users-list">
                                <div class="text-muted text-center py-4">Loading...</div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- RIGHT SIDE -->

            <div class="col-md-8">

                <div class="card shadow-sm">

                    <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                        Chat Messages
                        <button class="btn btn-sm btn-warning" id="admin-starred-btn" title="View starred messages">★ Starred</button>
                    </div>

                    <!-- CUSTOMER PROFILE SECTION — hidden until a chat is selected -->

                    <div id="customer-profile" class="d-none border-bottom px-3 py-2" style="background:#fff;">

                        <div class="d-flex align-items-center gap-3">

                            <!-- AVATAR -->

                            <div id="profile-avatar-wrap" style="flex-shrink:0;">

                                <!-- Filled with JS: either <img> or initials div -->

                            </div>

                            <!-- INFO -->

                            <div class="flex-grow-1 overflow-hidden">

                                <div id="profile-name" class="fw-semibold text-truncate">
                                    &nbsp;
                                </div>

                                <div id="profile-about"
                                    class="text-muted small"
                                    style="
                                        display: -webkit-box;
                                        -webkit-line-clamp: 2;
                                        line-clamp: 2;
                                        -webkit-box-orient: vertical;
                                        overflow: hidden;
                                        cursor: pointer;
                                    "
                                    title="Click to expand">
                                    &nbsp;
                                </div>

                            </div>

                            <!-- SPINNER shown while loading -->

                            <div id="profile-spinner" class="spinner-border spinner-border-sm text-secondary d-none" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>

                        </div>

                    </div>

                    <!-- MESSAGES -->

                    <div class="card-body" id="admin-messages"
                        style="
                        height:500px;
                        overflow-y:auto;
                        background:#f8f9fa;
                    ">

                    </div>

                    <!-- FOOTER -->

                    <div class="card-footer">

                        <!-- REPLY BAR -->

                        <div id="reply-bar" class="reply-preview d-none">
                            <span id="reply-bar-text" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Replying to: ...</span>
                            <span class="reply-cancel" id="reply-cancel">&times;</span>
                        </div>

                        <!-- PREVIEW -->

                        <div id="admin-preview" class="mb-2">

                        </div>

                        <!-- RECORDING INDICATOR -->
                        <div id="admin-rec-indicator" class="d-none mb-2 d-flex align-items-center gap-2" style="font-size:13px;color:#dc3545;">
                            <span style="width:10px;height:10px;border-radius:50%;background:#dc3545;display:inline-block;animation:blink 1s infinite;"></span>
                            Recording... <span id="admin-rec-timer">0s</span>
                        </div>

                        <!-- INPUT ROW: clip + mic + location + text + send -->

                        <div class="d-flex align-items-center gap-2">

                            <label for="admin-media" style="cursor:pointer;flex-shrink:0;margin:0;" title="Attach file">
                                <i class="fa fa-paperclip" style="font-size:20px;color:#0d6efd;"></i>
                            </label>

                            <input type="file" id="admin-media" class="d-none"
                                accept="image/*,video/*,.pdf,.doc,.docx">

                            <button id="admin-mic" type="button"
                                class="btn rounded-circle d-flex align-items-center justify-content-center"
                                style="width:36px;height:36px;flex-shrink:0;background:#f0f4ff;border:1px solid #c5d0f0;"
                                title="Click to start/stop recording">
                                <i class="fa fa-microphone" style="font-size:15px;color:#0d6efd;"></i>
                            </button>

                            <button id="admin-location" type="button"
                                class="btn rounded-circle d-flex align-items-center justify-content-center"
                                style="width:36px;height:36px;flex-shrink:0;background:#f0f4ff;border:1px solid #c5d0f0;"
                                title="Send location">
                                <i class="fa fa-location-dot" style="font-size:15px;color:#0d6efd;"></i>
                            </button>

                            <input type="text" id="admin-input" class="form-control" placeholder="Type message..." style="border-radius:20px;">

                            <button class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center" id="admin-send" style="width:38px;height:38px;flex-shrink:0;">
                                <i class="fa fa-paper-plane" style="font-size:14px;"></i>
                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- MEDIA PREVIEW MODAL -->

    <div class="modal fade" id="previewModal" tabindex="-1">

        <div class="modal-dialog modal-dialog-centered modal-lg">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">
                        Media Preview
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal">

                    </button>

                </div>

                <div class="modal-body text-center" id="previewModalBody">

                </div>

            </div>

        </div>

    </div>
    <!-- STARRED MESSAGES MODAL (Admin) -->
    <div class="modal fade" id="starredModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold">★ Starred Messages</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="starred-modal-body" style="min-height:200px;">
                    <p class="text-muted text-center mt-4">Loading...</p>
                </div>
            </div>
        </div>
    </div>
@endsection


<script type="module">
    import {

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
        deleteDoc,

        ref,
        uploadBytes,
        getDownloadURL

    } from "/js/firebase.js";


    /*
    |--------------------------------------------------------------------------
    | ELEMENTS
    |--------------------------------------------------------------------------
    */

    const usersDiv =
        document.getElementById('chat-users');

    const messagesDiv =
        document.getElementById('admin-messages');

    const adminInput =
        document.getElementById('admin-input');

    const adminMedia =
        document.getElementById('admin-media');

    const adminSend =
        document.getElementById('admin-send');

    const adminPreview =
        document.getElementById('admin-preview');

    const adminId =
        "{{ auth()->id() }}";

    const adminName =
        "{{ auth()->user()->name ?? 'Admin' }}";

    let selectedChatId = null;

    // Holds the unsubscribe function for the active message listener (Problem 4)
    let unsubscribeMessages = null;

    /*
    |--------------------------------------------------------------------------
    | FAVOURITES STATE
    |--------------------------------------------------------------------------
    */

    let favouriteUsers = new Set();    // Set of chatId strings
    let favouriteMessages = new Set(); // Set of messageId strings

    // Load favourite users from Firestore on page load
    async function loadFavouriteUsers() {
        try {
            const snap = await getDocs(
                collection(db, 'admin_favourites', adminId, 'users')
            );
            snap.forEach(d => favouriteUsers.add(d.id));
        } catch (e) { console.error('loadFavouriteUsers:', e); }
    }

    // Load favourite messages from Firestore on page load
    async function loadFavouriteMessages() {
        try {
            const snap = await getDocs(
                collection(db, 'admin_favourites', adminId, 'messages')
            );
            snap.forEach(d => favouriteMessages.add(d.id));
        } catch (e) { console.error('loadFavouriteMessages:', e); }
    }

    // Toggle favourite user
    async function toggleFavUser(chatId, customerName) {
        const docRef = doc(db, 'admin_favourites', adminId, 'users', chatId);
        if (favouriteUsers.has(chatId)) {
            favouriteUsers.delete(chatId); // update Set immediately
            await deleteDoc(docRef);
        } else {
            favouriteUsers.add(chatId); // update Set immediately
            await setDoc(docRef, {
                chat_id: chatId,
                customer_name: customerName,
                added_at: new Date()
            });
        }
    }

    // Toggle favourite message
    async function toggleFavMessage(msgId, chatId, sender, message, mediaUrl) {
        const docRef = doc(db, 'admin_favourites', adminId, 'messages', msgId);
        if (favouriteMessages.has(msgId)) {
            favouriteMessages.delete(msgId); // update Set immediately
            await deleteDoc(docRef);
        } else {
            favouriteMessages.add(msgId); // update Set immediately
            await setDoc(docRef, {
                chat_id: chatId,
                message_id: msgId,
                sender: sender,
                message: message || '',
                media_url: mediaUrl || null,
                starred_at: new Date()
            });
        }
    }

    /*
    |--------------------------------------------------------------------------
    | AUDIO RECORDING — Admin
    | Registered BEFORE the top-level awaits so the mic button is always
    | responsive regardless of how long Firestore takes to load favourites.
    |--------------------------------------------------------------------------
    */

    const adminMicBtn       = document.getElementById('admin-mic');
    const adminRecIndicator = document.getElementById('admin-rec-indicator');
    const adminRecTimer     = document.getElementById('admin-rec-timer');

    let adminMediaRecorder = null;
    let adminAudioChunks   = [];
    let adminRecInterval   = null;
    let adminRecSeconds    = 0;
    let adminIsRecording   = false;

    async function sendAudioMessage(audioBlob, chatId) {
        try {
            const fileName = 'admin_' + adminId + '_audio_' + Date.now() + '.webm';
            const storageRef = ref(storage, 'chat_media/' + fileName);
            await uploadBytes(storageRef, audioBlob);
            const audioUrl = await getDownloadURL(storageRef);
            await setDoc(
                doc(db, "support_chats", chatId),
                { admin_id: adminId, updated_at: new Date() },
                { merge: true }
            );
            await addDoc(
                collection(db, "support_chats", chatId, "messages"),
                {
                    sender: 'admin',
                    sender_name: adminName,
                    user_id: adminId,
                    message: '',
                    media_url: audioUrl,
                    media_type: 'audio/webm',
                    delivered: true,
                    read: false,
                    reply_to_id: replyingTo ? replyingTo.id : null,
                    reply_to_text: replyingTo ? replyingTo.text : null,
                    created_at: new Date()
                }
            );
            replyingTo = null;
            replyBar.classList.add('d-none');
        } catch (e) {
            console.error('Audio send failed:', e);
            alert('Failed to send audio');
        }
    }

    if (adminMicBtn) {
        adminMicBtn.addEventListener('click', async () => {
            if (!adminIsRecording) {
                // START
                if (!selectedChatId) { alert('Please select a customer first'); return; }
                try {
                    clearInterval(adminRecInterval);
                    adminIsRecording = true;
                    adminMicBtn.style.background = '#fde8e8';
                    adminMicBtn.querySelector('i').style.color = '#dc3545';
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    adminAudioChunks = [];
                    adminMediaRecorder = new MediaRecorder(stream);
                    adminMediaRecorder.ondataavailable = e => adminAudioChunks.push(e.data);
                    adminMediaRecorder.onstop = async () => {
                        stream.getTracks().forEach(t => t.stop());
                        adminIsRecording = false;
                        adminMicBtn.style.background = '#f0f4ff';
                        adminMicBtn.querySelector('i').style.color = '#0d6efd';
                        clearInterval(adminRecInterval);
                        adminRecInterval = null;
                        adminRecIndicator.classList.add('d-none');
                        if (adminRecSeconds < 1) return;
                        const blob = new Blob(adminAudioChunks, { type: 'audio/webm' });
                        await sendAudioMessage(blob, selectedChatId);
                    };
                    adminMediaRecorder.start();
                    adminRecSeconds = 0;
                    adminRecTimer.textContent = '0s';
                    adminRecIndicator.classList.remove('d-none');
                    adminRecInterval = setInterval(() => {
                        adminRecSeconds++;
                        adminRecTimer.textContent = adminRecSeconds + 's';
                    }, 1000);
                } catch (e) {
                    adminIsRecording = false;
                    adminMicBtn.style.background = '#f0f4ff';
                    adminMicBtn.querySelector('i').style.color = '#0d6efd';
                    clearInterval(adminRecInterval);
                    console.error('Mic error:', e);
                    alert('Microphone access denied');
                }
            } else {
                // STOP
                if (adminMediaRecorder && adminMediaRecorder.state === 'recording') {
                    adminMediaRecorder.stop();
                }
            }
        });
    } // end if (adminMicBtn)


    /*
    |--------------------------------------------------------------------------
    | LOCATION — Admin
    |--------------------------------------------------------------------------
    */

    const adminLocationBtn = document.getElementById('admin-location');

    if (adminLocationBtn) {
        adminLocationBtn.addEventListener('click', () => {
            if (!selectedChatId) { alert('Please select a customer first'); return; }

            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser');
                return;
            }

            adminLocationBtn.disabled = true;
            adminLocationBtn.querySelector('i').style.color = '#ffc107';

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    const lat = position.coords.latitude.toFixed(6);
                    const lng = position.coords.longitude.toFixed(6);

                    try {
                        await setDoc(
                            doc(db, "support_chats", selectedChatId),
                            { admin_id: adminId, updated_at: new Date() },
                            { merge: true }
                        );

                        await addDoc(
                            collection(db, "support_chats", selectedChatId, "messages"),
                            {
                                sender: 'admin',
                                sender_name: adminName,
                                user_id: adminId,
                                message: JSON.stringify({ lat, lng, label: 'Admin Location' }),
                                media_url: null,
                                media_type: 'location',
                                delivered: true,
                                read: false,
                                reply_to_id: replyingTo ? replyingTo.id : null,
                                reply_to_text: replyingTo ? replyingTo.text : null,
                                created_at: new Date()
                            }
                        );

                        replyingTo = null;
                        replyBar.classList.add('d-none');
                    } catch (e) {
                        console.error('Location send failed:', e);
                        alert('Failed to send location');
                    }

                    adminLocationBtn.disabled = false;
                    adminLocationBtn.querySelector('i').style.color = '#0d6efd';
                },
                (err) => {
                    adminLocationBtn.disabled = false;
                    adminLocationBtn.querySelector('i').style.color = '#0d6efd';
                    alert('Could not get location: ' + err.message);
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });
    }


    // Load favourites on init
    try {
        await loadFavouriteUsers();
        await loadFavouriteMessages();
    } catch (e) {
        console.warn('Could not load favourites:', e);
    }

    /*
    |--------------------------------------------------------------------------
    | RENDER FAVOURITE USERS TAB
    |--------------------------------------------------------------------------
    */

    const favUsersListEl = document.getElementById('fav-users-list');

    function renderFavUsersTab() {
        if (favouriteUsers.size === 0) {
            favUsersListEl.innerHTML = '<div class="text-muted text-center py-4">No favourite users yet</div>';
            return;
        }
        favUsersListEl.innerHTML = '';
        favouriteUsers.forEach(chatId => {
            const btn = document.createElement('button');
            btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
            btn.innerHTML = `
                <div>
                    <div class="fw-bold">${chatId}</div>
                </div>
                <span class="text-warning">★</span>
            `;
            btn.addEventListener('click', () => {
                selectedChatId = chatId;
                loadProfile(chatId);
                loadMessages(chatId);
                // Switch to All Chats tab
                document.getElementById('tab-all-chats').click();
            });
            favUsersListEl.appendChild(btn);
        });
    }

    // Render on init
    renderFavUsersTab();

    // Re-render when tab is shown
    document.getElementById('tab-fav-users').addEventListener('shown.bs.tab', () => {
        renderFavUsersTab();
    });

    /*
    |--------------------------------------------------------------------------
    | REPLY STATE
    |--------------------------------------------------------------------------
    */

    let replyingTo = null; // { id, text, sender }

    const replyBar     = document.getElementById('reply-bar');
    const replyBarText = document.getElementById('reply-bar-text');
    const replyCancel  = document.getElementById('reply-cancel');

    replyCancel.addEventListener('click', () => {
        replyingTo = null;
        replyBar.classList.add('d-none');
    });

    function setReply(msgId, msgText, sender) {
        const preview = (msgText || 'Media').substring(0, 80);
        replyingTo = { id: msgId, text: preview, sender: sender };
        replyBarText.textContent = `↩ ${sender}: ${preview}`;
        replyBar.classList.remove('d-none');
        adminInput.focus();
    }

    /*
    |--------------------------------------------------------------------------
    | PROFILE ELEMENTS
    |--------------------------------------------------------------------------
    */

    const profileSection  = document.getElementById('customer-profile');
    const profileAvatar   = document.getElementById('profile-avatar-wrap');
    const profileName     = document.getElementById('profile-name');
    const profileAbout    = document.getElementById('profile-about');
    const profileSpinner  = document.getElementById('profile-spinner');

    // Expand/collapse about text on click
    let aboutExpanded = false;
    profileAbout.addEventListener('click', () => {
        aboutExpanded = !aboutExpanded;
        profileAbout.style.webkitLineClamp = aboutExpanded ? 'unset' : '2';
        profileAbout.style.lineClamp        = aboutExpanded ? 'unset' : '2';
    });

    /*
    |--------------------------------------------------------------------------
    | LOAD CUSTOMER PROFILE
    |--------------------------------------------------------------------------
    |
    | 1. Fetch name + profile picture from Laravel (MySQL) via session-auth.
    | 2. Fetch "about" from Firestore users_profile/{userId}.
    |
    */

    async function loadProfile(chatId) {

        // Extract numeric userId from "user_5" → "5"
        const userId = chatId.replace('user_', '');

        // Show section, reset state, show spinner
        profileSection.classList.remove('d-none');
        profileSpinner.classList.remove('d-none');
        profileName.textContent  = 'Loading…';
        profileAbout.textContent = '';
        profileAvatar.innerHTML  = '';
        aboutExpanded = false;
        profileAbout.style.webkitLineClamp = '2';
        profileAbout.style.lineClamp        = '2';

        /*
        |----------------------------------------------------------------------
        | 1. MYSQL — profile picture + name via Laravel web route
        |----------------------------------------------------------------------
        */

        try {

            const res = await fetch(
                `/admin/customer/${userId}/profile`,
                {
                    headers: {
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',   // send session cookie
                }
            );

            if (res.ok) {

                const data = await res.json();

                profileName.textContent = data.name || chatId;

                if (data.profile_picture) {

                    profileAvatar.innerHTML = `
                        <img
                            src="${data.profile_picture}"
                            alt="${data.name}"
                            style="
                                width:60px;
                                height:60px;
                                border-radius:50%;
                                object-fit:cover;
                                border:2px solid #dee2e6;
                            ">
                    `;

                } else {

                    // CSS-only initial avatar
                    const initial = (data.name || '?').charAt(0).toUpperCase();
                    profileAvatar.innerHTML = `
                        <div style="
                            width:60px;
                            height:60px;
                            border-radius:50%;
                            background:#6c757d;
                            color:#fff;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            font-size:24px;
                            font-weight:600;
                            flex-shrink:0;
                        ">${initial}</div>
                    `;

                }

            } else {

                profileName.textContent = chatId;
                profileAvatar.innerHTML = `
                    <div style="
                        width:60px;height:60px;border-radius:50%;
                        background:#6c757d;color:#fff;
                        display:flex;align-items:center;justify-content:center;
                        font-size:24px;font-weight:600;
                    ">?</div>
                `;

            }

        } catch (err) {

            console.error('Profile fetch error:', err);
            profileName.textContent = chatId;

        }

        /*
        |----------------------------------------------------------------------
        | 2. FIRESTORE — about field from users_profile/{userId}
        |----------------------------------------------------------------------
        */

        try {

            const aboutSnap = await getDoc(
                doc(db, 'users_profile', userId)
            );

            if (aboutSnap.exists() && aboutSnap.data().about) {
                profileAbout.textContent = aboutSnap.data().about;
            } else {
                profileAbout.textContent = 'No about info';
            }

        } catch (err) {

            console.error('Firestore about fetch error:', err);
            profileAbout.textContent = 'No about info';

        }

        profileSpinner.classList.add('d-none');

    }


    /*
    |--------------------------------------------------------------------------
    | LOAD CHATS
    |--------------------------------------------------------------------------
    */

    function loadChats() {

        const chatsRef =
            collection(db, "support_chats");

        onSnapshot(chatsRef, (snapshot) => {

            usersDiv.innerHTML = '';

            snapshot.forEach((docSnap) => {

                const data =
                    docSnap.data();

                const chatId =
                    docSnap.id;

                const button =
                    document.createElement('button');

                button.className =
                    'list-group-item list-group-item-action';

                const customerName =
                    data.customer_name ?
                    data.customer_name :
                    chatId;

                const isFav = favouriteUsers.has(chatId);

                button.innerHTML = `

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">${customerName}</div>
                        <small class="text-muted">${chatId}</small>
                    </div>
                    <span class="fav-star ${isFav ? 'active' : ''}" data-chat-id="${chatId}" data-name="${customerName}" title="Toggle favourite">
                        ${isFav ? '★' : '☆'}
                    </span>
                </div>

            `;

                // Star click handler (stop propagation so it doesn't select the chat)
                button.querySelector('.fav-star').addEventListener('click', async (e) => {
                    e.stopPropagation();
                    const star = e.currentTarget;
                    await toggleFavUser(chatId, customerName);
                    const nowFav = favouriteUsers.has(chatId);
                    star.className = 'fav-star ' + (nowFav ? 'active' : '');
                    star.textContent = nowFav ? '★' : '☆';
                });

                button.addEventListener('click', () => {

                    document
                        .querySelectorAll('#chat-users button')
                        .forEach((b) => {

                            b.classList.remove('active');

                        });

                    button.classList.add('active');

                    selectedChatId = chatId;

                    loadProfile(chatId);

                    loadMessages(chatId);

                });

                usersDiv.appendChild(button);

                /*
                |--------------------------------------------------------------------------
                | AUTO LOAD FIRST CHAT
                |--------------------------------------------------------------------------
                */

                if (!selectedChatId) {

                    selectedChatId = chatId;

                    loadProfile(chatId);

                    loadMessages(chatId);

                    button.classList.add('active');

                }

            });

        });

    }

    loadChats();


    /*
    |--------------------------------------------------------------------------
    | FILE PREVIEW — show directly inline
    |--------------------------------------------------------------------------
    */

    adminMedia.addEventListener('change', () => {

        const file = adminMedia.files[0];
        adminPreview.innerHTML = '';
        if (!file) return;

        if (file.type.startsWith('image/')) {
            adminPreview.innerHTML = `
                <div style="position:relative;display:inline-block;margin-bottom:4px;">
                    <img src="${URL.createObjectURL(file)}"
                         style="max-height:80px;max-width:150px;border-radius:8px;object-fit:cover;border:1px solid #ddd;">
                    <span class="preview-remove" style="position:absolute;top:-6px;right:-6px;background:#dc3545;color:#fff;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;cursor:pointer;">&times;</span>
                </div>`;
        } else if (file.type.startsWith('video/')) {
            adminPreview.innerHTML = `
                <div style="position:relative;display:inline-block;margin-bottom:4px;">
                    <video src="${URL.createObjectURL(file)}" controls style="max-height:80px;max-width:150px;border-radius:8px;border:1px solid #ddd;"></video>
                    <span class="preview-remove" style="position:absolute;top:-6px;right:-6px;background:#dc3545;color:#fff;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;cursor:pointer;">&times;</span>
                </div>`;
        } else {
            adminPreview.innerHTML = `
                <div style="display:inline-flex;align-items:center;gap:6px;background:#f0f0f0;padding:4px 10px;border-radius:8px;font-size:12px;margin-bottom:4px;">
                    <i class="fa fa-file" style="color:#666;"></i>
                    <span style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${file.name}</span>
                    <span class="preview-remove" style="cursor:pointer;color:#dc3545;font-size:14px;font-weight:bold;">&times;</span>
                </div>`;
        }

        const removeBtn = adminPreview.querySelector('.preview-remove');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                adminMedia.value = '';
                adminPreview.innerHTML = '';
            });
        }

    });


    /*
    |--------------------------------------------------------------------------
    | SEND MESSAGE
    |--------------------------------------------------------------------------
    */

    adminSend.addEventListener('click', async () => {

        if (!selectedChatId) {

            alert('Please select customer chat');

            return;

        }

        const message =
            adminInput.value.trim();

        const file =
            adminMedia.files[0];

        if (!message && !file) {
            return;
        }

        adminSend.disabled = true;

        adminSend.innerText = 'Sending...';

        try {

            /*
            |--------------------------------------------------------------------------
            | UPDATE CHAT DOCUMENT
            |--------------------------------------------------------------------------
            */

            await setDoc(

                doc(db, "support_chats", selectedChatId),

                {
                    admin_id: adminId,
                    updated_at: new Date()
                },

                {
                    merge: true
                }

            );

            /*
            |--------------------------------------------------------------------------
            | UPLOAD FILE
            |--------------------------------------------------------------------------
            */

            let mediaUrl = null;

            let mediaType = null;

            if (file) {

                const cleanName =
                    file.name.replace(/\s+/g, '_');

                const fileName =

                    'admin_' +
                    adminId +
                    '_' +
                    Date.now() +
                    '_' +
                    cleanName;

                const storageRef = ref(

                    storage,

                    'chat_media/' + fileName

                );

                await uploadBytes(
                    storageRef,
                    file
                );

                mediaUrl =
                    await getDownloadURL(storageRef);

                mediaType =
                    file.type;

            }

            /*
            |--------------------------------------------------------------------------
            | SAVE MESSAGE
            |--------------------------------------------------------------------------
            */

            await addDoc(

                collection(
                    db,
                    "support_chats",
                    selectedChatId,
                    "messages"
                ),

                {
                    sender: 'admin',
                    sender_name: adminName,
                    user_id: adminId,
                    message: message,
                    media_url: mediaUrl,
                    media_type: mediaType,
                    delivered: true,
                    read: false,
                    reply_to_id: replyingTo ? replyingTo.id : null,
                    reply_to_text: replyingTo ? replyingTo.text : null,

                    /*
                    |--------------------------------------------------------------------------
                    | IMPORTANT — must be Firestore Timestamp (new Date()), not Date.now()
                    |--------------------------------------------------------------------------
                    */

                    created_at: new Date()
                }

            );

            /*
            |--------------------------------------------------------------------------
            | RESET
            |--------------------------------------------------------------------------
            */

            adminInput.value = '';

            adminMedia.value = '';

            adminPreview.innerHTML = '';

            // Reset reply state
            replyingTo = null;
            replyBar.classList.add('d-none');

        } catch (error) {

            console.error(error);

            alert('Failed to send');

        } finally {

            adminSend.disabled = false;

            adminSend.innerText = 'Send';

        }

    });


    /*
    |--------------------------------------------------------------------------
    | MARK MESSAGES AS READ
    |--------------------------------------------------------------------------
    | When admin opens a chat, mark all customer messages as read.
    | NOTE: Firestore requires a composite index on (sender, read) for this
    | query. If missing, the browser console will show a link to create it.
    */

    async function markMessagesAsRead(chatId) {
        try {
            const q = query(
                collection(db, "support_chats", chatId, "messages"),
                where("sender", "==", "customer"),
                where("read", "==", false)
            );
            const snapshot = await getDocs(q);
            snapshot.forEach(async (docSnap) => {
                await updateDoc(docSnap.ref, { read: true });
            });
        } catch (e) {
            console.error('markMessagesAsRead error:', e);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD MESSAGES
    |--------------------------------------------------------------------------
    */

    function loadMessages(chatId) {

        // Unsubscribe from the previous listener before creating a new one (Problem 4)
        if (unsubscribeMessages) {
            unsubscribeMessages();
            unsubscribeMessages = null;
        }

        // Mark customer messages as read when admin opens this chat
        markMessagesAsRead(chatId);

        const q = query(

            collection(
                db,
                "support_chats",
                chatId,
                "messages"
            ),

            orderBy("created_at")

        );

        unsubscribeMessages = onSnapshot(q, (snapshot) => {
            // Store snapshot for re-render after star toggle
            lastSnapshot = snapshot;
            renderMessages(snapshot);
        });

    }

    // Holds the last snapshot so we can re-render after star toggle
    let lastSnapshot = null;

    function renderMessages(snapshot) {

            messagesDiv.innerHTML = '';

            // Store message data for star handlers
            const messagesMap = new Map();

            snapshot.forEach((docSnap) => {

                const data =
                    docSnap.data();

                const msgId = docSnap.id;
                messagesMap.set(msgId, data);

                let mediaHtml = '';

                /*
                |--------------------------------------------------------------------------
                | IMAGE
                |--------------------------------------------------------------------------
                */

                if (

                    data.media_url &&
                    data.media_type &&
                    data.media_type.startsWith('image/')

                ) {

                    mediaHtml = `

                    <img
                        src="${data.media_url}"
                        class="img-fluid rounded mb-2 chat-image"
                        style="
                            max-width:220px;
                            cursor:pointer;
                        ">

                `;

                }

                /*
                |--------------------------------------------------------------------------
                | VIDEO
                |--------------------------------------------------------------------------
                */
                else if (

                    data.media_url &&
                    data.media_type &&
                    data.media_type.startsWith('video/')

                ) {

                    mediaHtml = `

                    <video
                        controls
                        class="rounded mb-2"
                        style="max-width:220px;">

                        <source src="${data.media_url}">

                    </video>

                `;

                }

                /*
                |--------------------------------------------------------------------------
                | FILE
                |--------------------------------------------------------------------------
                */
                else if (data.media_url && data.media_type && data.media_type.startsWith('audio/')) {

                    mediaHtml = `
                    <div style="
                        display:flex;
                        align-items:center;
                        gap:8px;
                        background:rgba(0,0,0,0.06);
                        border-radius:20px;
                        padding:6px 10px;
                        min-width:180px;
                        max-width:220px;
                    ">
                        <button onclick="
                            var a=this.parentElement.querySelector('audio');
                            if(a.paused){a.play();this.innerHTML='⏸';}
                            else{a.pause();this.innerHTML='▶';}
                        " style="
                            width:32px;height:32px;border-radius:50%;
                            background:#0d6efd;color:#fff;border:none;
                            flex-shrink:0;font-size:13px;cursor:pointer;
                            display:flex;align-items:center;justify-content:center;
                        ">▶</button>
                        <div style="flex:1;">
                            <input type="range" value="0" min="0" step="0.01"
                                style="width:100%;accent-color:#0d6efd;height:3px;cursor:pointer;"
                                oninput="this.parentElement.parentElement.querySelector('audio').currentTime=this.value;">
                            <div style="font-size:10px;color:#666;margin-top:2px;" class="audio-dur">0:00</div>
                        </div>
                        <audio src="${data.media_url}" style="display:none;"
                            ontimeupdate="
                                var r=this.parentElement.querySelector('input[type=range]');
                                r.value=this.currentTime;
                                var d=this.parentElement.querySelector('.audio-dur');
                                var m=Math.floor(this.currentTime/60);
                                var s=Math.floor(this.currentTime%60).toString().padStart(2,'0');
                                d.textContent=m+':'+s;
                            "
                            onloadedmetadata="
                                this.parentElement.querySelector('input[type=range]').max=this.duration;
                            "
                            onended="
                                this.parentElement.querySelector('button').innerHTML='▶';
                                this.parentElement.querySelector('input[type=range]').value=0;
                                this.parentElement.querySelector('.audio-dur').textContent='0:00';
                            ">
                        </audio>
                    </div>
                    `;

                }

                else if (data.media_url) {

                    mediaHtml = `

                    <a
                        href="${data.media_url}"
                        target="_blank"
                        class="btn btn-sm btn-outline-primary mb-2">

                        Open File

                    </a>

                `;

                }

                else if (data.media_type === 'location' && data.message) {

                    try {
                        const loc = JSON.parse(data.message);
                        const mapUrl = `https://www.google.com/maps?q=${loc.lat},${loc.lng}`;
                        const imgUrl = `https://maps.googleapis.com/maps/api/staticmap?center=${loc.lat},${loc.lng}&zoom=15&size=220x120&markers=color:red%7C${loc.lat},${loc.lng}&key=`;
                        mediaHtml = `
                        <a href="${mapUrl}" target="_blank" style="display:block;text-decoration:none;">
                            <div style="
                                background:#e8f5e9;
                                border:1px solid #c8e6c9;
                                border-radius:10px;
                                overflow:hidden;
                                width:220px;
                            ">
                                <iframe
                                    width="220" height="120"
                                    style="border:0;display:block;"
                                    loading="lazy"
                                    allowfullscreen
                                    src="https://maps.google.com/maps?q=${loc.lat},${loc.lng}&z=15&output=embed">
                                </iframe>
                                <div style="padding:5px 8px;font-size:12px;color:#2e7d32;display:flex;align-items:center;gap:5px;">
                                    <i class="fa fa-location-dot" style="color:#d32f2f;"></i>
                                    <span>${loc.label || 'Shared Location'}</span>
                                    <span style="margin-left:auto;color:#888;font-size:10px;">${loc.lat}, ${loc.lng}</span>
                                </div>
                            </div>
                        </a>
                        `;
                    } catch(e) {
                        mediaHtml = `<div class="text-muted small">📍 Location</div>`;
                    }

                }

                const alignClass =

                    data.sender === 'admin' ?
                    'text-end' :
                    'text-start';

                const bubbleClass =

                    data.sender === 'admin' ?
                    'bg-light-green text-dark' :
                    'bg-white';

                /*
                |--------------------------------------------------------------------------
                | TICK INDICATOR — only on admin's own messages
                |--------------------------------------------------------------------------
                */

                let tickHtml = '';

                if (data.sender === 'admin') {
                    if (data.read === true) {
                        tickHtml = '<div style="text-align:right;margin-top:2px;"><span style="color:#1976D2;font-size:13px;">✓✓</span></div>';
                    } else {
                        tickHtml = '<div style="text-align:right;margin-top:2px;"><span style="color:#9E9E9E;font-size:13px;">✓</span></div>';
                    }
                }

                const isMsgFav = favouriteMessages.has(msgId);

                // Reply quote (if this message is a reply to another)
                const replyQuoteHtml = data.reply_to_text
                    ? `<div class="reply-quote">${data.reply_to_text}</div>`
                    : '';

                const html = `

                <div class="${alignClass} mb-3 msg-bubble-wrap">

                    <div
                        class="d-inline-block p-2 rounded shadow-sm ${bubbleClass}"
                        style="max-width:75%;position:relative;">

                        ${replyQuoteHtml}

                        ${mediaHtml}

                        ${
                            data.message
                            ? `<div>${data.message}</div>`
                            : ''
                        }

                        ${tickHtml}

                        <span class="msg-reply" data-msg-id="${msgId}" title="Reply">↩</span>

                        <span class="msg-star ${isMsgFav ? 'active' : ''}"
                              data-msg-id="${msgId}"
                              title="Star message">
                            ${isMsgFav ? '★' : '☆'}
                        </span>

                    </div>

                </div>

            `;

                messagesDiv.innerHTML += html;

            });

            /*
            |--------------------------------------------------------------------------
            | IMAGE PREVIEW
            |--------------------------------------------------------------------------
            */

            document
                .querySelectorAll('.chat-image')
                .forEach((img) => {

                    img.addEventListener('click', () => {

                        openImagePreview(img.src);

                    });

                });

            /*
            |--------------------------------------------------------------------------
            | MESSAGE STAR CLICK HANDLERS
            |--------------------------------------------------------------------------
            */

            document.querySelectorAll('.msg-star').forEach((star) => {
                star.addEventListener('click', async (e) => {
                    const msgId = e.currentTarget.dataset.msgId;
                    const msgData = messagesMap.get(msgId);
                    if (!msgData) return;

                    await toggleFavMessage(
                        msgId,
                        selectedChatId,
                        msgData.sender,
                        msgData.message,
                        msgData.media_url
                    );

                    // Re-render messages to reflect the new star state
                    if (lastSnapshot) renderMessages(lastSnapshot);
                });
            });

            /*
            |--------------------------------------------------------------------------
            | REPLY CLICK HANDLERS
            |--------------------------------------------------------------------------
            */

            document.querySelectorAll('.msg-reply').forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    const msgId = e.currentTarget.dataset.msgId;
                    const msgData = messagesMap.get(msgId);
                    if (!msgData) return;
                    setReply(msgId, msgData.message, msgData.sender);
                });
            });

            /*
            |--------------------------------------------------------------------------
            | AUTO SCROLL
            |--------------------------------------------------------------------------
            */

            messagesDiv.scrollTop =
                messagesDiv.scrollHeight;

    }


    /*
    |--------------------------------------------------------------------------
    | IMAGE PREVIEW MODAL
    |--------------------------------------------------------------------------
    */

    function openImagePreview(imageUrl) {

        const modalBody =
            document.getElementById('previewModalBody');

        modalBody.innerHTML = `

        <img
            src="${imageUrl}"
            class="img-fluid rounded"

            style="
                max-height:80vh;
                object-fit:contain;
            ">

    `;

        const modal =

            new bootstrap.Modal(

                document.getElementById('previewModal')

            );

        modal.show();

    }


    /*
    |--------------------------------------------------------------------------
    | STARRED MESSAGES MODAL (Admin)
    |--------------------------------------------------------------------------
    */

    const starredBtn = document.getElementById('admin-starred-btn');
    const starredModalBody = document.getElementById('starred-modal-body');

    if (starredBtn) {
        starredBtn.addEventListener('click', async () => {
            if (!selectedChatId) {
                alert('Please select a customer first');
                return;
            }

            const modal = new bootstrap.Modal(document.getElementById('starredModal'));
            modal.show();
            starredModalBody.innerHTML = '<p class="text-muted text-center mt-4">Loading...</p>';

            try {
                const snap = await getDocs(
                    collection(db, 'admin_favourites', adminId, 'messages')
                );

                // Filter to only show starred messages from the selected chat
                const filtered = [];
                snap.forEach(docSnap => {
                    const d = docSnap.data();
                    if (d.chat_id === selectedChatId) {
                        filtered.push(d);
                    }
                });

                if (filtered.length === 0) {
                    starredModalBody.innerHTML = '<p class="text-muted text-center mt-4">No starred messages for this customer</p>';
                    return;
                }

                starredModalBody.innerHTML = '';
                filtered.forEach(d => {
                    const senderLabel = d.sender === 'admin' ? 'You' : (d.sender || 'Customer');
                    starredModalBody.innerHTML += `
                        <div class="p-3 mb-2 rounded border bg-white">
                            <div class="d-flex justify-content-between">
                                <small class="fw-semibold text-muted">${senderLabel}</small>
                                <small class="text-muted">${selectedChatId}</small>
                            </div>
                            <div class="mt-1">${d.message || '<i class="text-muted">Media attachment</i>'}</div>
                        </div>
                    `;
                });
            } catch (e) {
                console.error(e);
                starredModalBody.innerHTML = '<p class="text-danger text-center mt-4">Failed to load starred messages</p>';
            }
        });
    }
</script>
