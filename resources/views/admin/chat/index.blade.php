@extends('layouts.admin')

@section('content')
    <style>
        .bg-light-green { background-color: #dcf8c6 !important; }
    </style>
    <div class="container py-4">

        <div class="row">

            <!-- LEFT SIDE -->

            <div class="col-md-4">

                <div class="card shadow-sm">

                    <div class="card-header fw-bold">
                        Customer Chats
                    </div>

                    <div class="list-group list-group-flush" id="chat-users">

                    </div>

                </div>

            </div>

            <!-- RIGHT SIDE -->

            <div class="col-md-8">

                <div class="card shadow-sm">

                    <div class="card-header fw-bold">
                        Chat Messages
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

                        <!-- FILE -->

                        <div class="mb-2">

                            <input type="file" id="admin-media" class="form-control"
                                accept="image/*,video/*,.pdf,.doc,.docx">

                        </div>

                        <!-- PREVIEW -->

                        <div id="admin-preview" class="mb-2">

                        </div>

                        <!-- INPUT -->

                        <div class="d-flex">

                            <input type="text" id="admin-input" class="form-control me-2" placeholder="Type message...">

                            <button class="btn btn-primary" id="admin-send">

                                Send

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

                button.innerHTML = `

                <div class="fw-bold">
                    ${customerName}
                </div>

                <small class="text-muted">
                    ${chatId}
                </small>

            `;

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
    | FILE PREVIEW
    |--------------------------------------------------------------------------
    */

    adminMedia.addEventListener('change', () => {

        const file =
            adminMedia.files[0];

        adminPreview.innerHTML = '';

        if (!file) return;

        if (file.type.startsWith('image/')) {

            adminPreview.innerHTML = `

            <img
                src="${URL.createObjectURL(file)}"
                class="img-thumbnail"
                style="max-height:100px;">

        `;

        } else {

            adminPreview.innerHTML = `

            <div class="alert alert-secondary p-2">

                ${file.name}

            </div>

        `;

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

            messagesDiv.innerHTML = '';

            snapshot.forEach((docSnap) => {

                const data =
                    docSnap.data();

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

                const html = `

                <div class="${alignClass} mb-3">

                    <div
                        class="d-inline-block p-2 rounded shadow-sm ${bubbleClass}"
                        style="max-width:75%;">

                        ${mediaHtml}

                        ${
                            data.message
                            ? `<div>${data.message}</div>`
                            : ''
                        }

                        ${tickHtml}

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
            | AUTO SCROLL
            |--------------------------------------------------------------------------
            */

            messagesDiv.scrollTop =
                messagesDiv.scrollHeight;

        });

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
</script>
