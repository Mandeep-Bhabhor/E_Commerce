<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'MyShop') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script src="//unpkg.com/alpinejs" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            background: #f4f7f6;
            /* Soft, friendly background */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* --- Colorful Header Theme --- */
        .navbar-custom {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            padding: 12px 0;
        }

        .navbar-custom .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            color: #fff !important;
        }

        .navbar-custom .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            transition: 0.2s;
        }

        .navbar-custom .nav-link:hover {
            color: #ffeaa7 !important;
            /* Yellow hover effect */
            transform: translateY(-1px);
        }

        /* --- Rounded Search Bar --- */
        .search-bar {
            border-radius: 20px 0 0 20px;
            border: none;
            padding-left: 18px;
            box-shadow: none !important;
        }

        .search-btn {
            border-radius: 0 20px 20px 0;
            background-color: #ffeaa7;
            color: #2d3436;
            border: none;
            font-weight: bold;
            transition: 0.2s;
        }

        .search-btn:hover {
            background-color: #fdcb6e;
        }

        /* --- Custom Buttons --- */
        .btn-accent {
            background-color: #ffeaa7;
            color: #2d3436;
            font-weight: bold;
            border-radius: 20px;
        }

        .btn-accent:hover {
            background-color: #fdcb6e;
        }

        /* --- Colorful Footer --- */
        footer {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: #fff;
            padding: 20px 0;
        }

        /* --- Force Google Maps to be responsive --- */
        .map-container iframe {
            width: 100% !important;
            height: 180px !important;
            /* Makes it nice, small, and compact */
            border: none !important;
        }

        /* CHAT BUTTON */

        #chat-toggle {
            position: fixed;
            bottom: 25px;
            right: 25px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #0d6efd;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            z-index: 9999;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* CHAT BOX */

        #chat-box {
            position: fixed;
            bottom: 100px;
            right: 25px;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            display: none;
            z-index: 9999;
        }

        /* HEADER */

        #chat-header {
            background: #0d6efd;
            color: white;
            padding: 10px 15px 8px;
            font-weight: bold;
        }

        #chat-header .chat-title {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.3px;
            margin-bottom: 8px;
        }

        #chat-header .chat-profile-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #chat-about-text {
            font-size: 12px;
            font-weight: 400;
            opacity: 0.88;
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 220px;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.5);
            padding-bottom: 1px;
        }

        #chat-about-text:hover {
            opacity: 1;
            border-bottom-color: #fff;
        }

        /* MESSAGES */

        #chat-messages {
            height: 300px;
            overflow-y: auto;
            padding: 15px;
            background: #f8f9fa;
        }

        /* Sender bubble — light green like WhatsApp */
        .bg-light-green {
            background-color: #dcf8c6 !important;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    {{-- Decode Emails and Phones from settings --}}
    @php
    $emails = isset($siteSettings->email) ? json_decode($siteSettings->email, true) : [];
    $phones = isset($siteSettings->phone) ? json_decode($siteSettings->phone, true) : [];
    if (!is_array($emails)) {
    $emails = [];
    }
    if (!is_array($phones)) {
    $phones = [];
    }
    @endphp



    {{-- NAVBAR --}}
    <nav class="navbar navbar-expand-lg navbar-custom shadow-sm sticky-top">
        <div class="container">

            {{-- DYNAMIC LOGO --}}
            <a class="navbar-brand d-flex align-items-center" href="/">
                @if (isset($siteSettings->header_logo) && $siteSettings->header_logo)
                <img src="{{ asset('storage/' . $siteSettings->header_logo) }}" alt="MyShop Logo" class="me-2"
                    style="height: 35px; width: auto; object-fit: contain;">
                @else
                {{-- Fallback SVG LOGO --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24"
                    fill="none" stroke="#ffeaa7" stroke-width="2.5" stroke-linecap="round"
                    stroke-linejoin="round" class="me-2">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
                @endif
                MyShop
            </a>

            <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#nav">
                <i class="fa fa-bars text-white fs-4"></i>
            </button>

            <div class="collapse navbar-collapse" id="nav">

                {{-- LEFT LINKS --}}
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <li class="nav-item"><a class="nav-link" href="/dashboard">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('products.list') }}">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('cart.index') }}">Cart</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('wishlist.index') }}">Wishlist</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('orders.index') }}">Orders</a></li>
                </ul>

                {{-- RIGHT LINKS --}}
                <ul class="navbar-nav align-items-center">
                    @auth
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="{{ route('customer.profile') }}">

                            @if (auth()->user()->pfp)
                            <img src="{{ asset('storage/' . auth()->user()->pfp) }}" alt="Profile"
                                class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                            @else
                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                                style="width: 32px; height: 32px; font-size: 14px;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            @endif

                            <span>
                                {{ auth()->user()->name }}
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button class="btn btn-accent btn-sm ms-lg-3 px-3">Logout</button>
                        </form>
                    </li>
                    @else
                    <li class="nav-item">
                        <a class="btn btn-accent btn-sm me-2 px-3" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm px-3" style="border-radius: 20px; border-width: 2px;"
                            href="{{ route('register') }}">Register</a>
                    </li>
                    @endauth
                </ul>

            </div>
        </div>
    </nav>

    {{-- MAIN CONTENT --}}
    <div class="container mt-4 mb-5 flex-grow-1">
        @yield('content')
    </div>

    {{-- DYNAMIC FOOTER --}}
    <footer class="mt-auto shadow-lg"
        style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: #fff; padding: 40px 0 20px 0;">
        <div class="container">
            <div class="row text-center text-md-start">

                {{-- Column 1: Logo & About --}}
                <div class="col-md-4 mb-4 mb-md-0">
                    @if (isset($siteSettings->footer_logo) && $siteSettings->footer_logo)
                    <img src="{{ asset('storage/' . $siteSettings->footer_logo) }}" alt="Footer Logo"
                        style="height: 50px; width: auto; object-fit: contain; margin-bottom: 15px;">
                    @else
                    <h3 class="fw-bold text-white mb-3">MyShop</h3>
                    @endif
                    <p style="color: rgba(255,255,255,0.9); font-size: 0.9rem;">
                        Your trusted store for the best products. We deliver quality and happiness right to your
                        doorstep.
                    </p>


                </div>

                {{-- Column 2: Contact Info --}}
                <div class="col-md-4 mb-4 mb-md-0" style="color: rgba(255,255,255,0.9);">
                    <h5 class="fw-bold text-white mb-3">Contact Us</h5>

                    @if (count($emails) > 0)
                    @foreach ($emails as $email)
                    <div class="mb-2">
                        <i class="fa fa-envelope me-2"></i>
                        <a href="mailto:{{ $email }}"
                            class="text-white text-decoration-none">{{ $email }}</a>
                    </div>
                    @endforeach
                    @endif

                    @if (count($phones) > 0)
                    @foreach ($phones as $phone)
                    <div class="mb-2">
                        <i class="fa fa-phone me-2"></i> {{ $phone }}
                    </div>
                    @endforeach
                    @endif
                </div>
                {{-- Quick Links / Legal Pages --}}
                <div class="col-md-3 mb-4 mb-md-0" style="color: rgba(255,255,255,0.9);">
                    <h5 class="fw-bold text-white mb-3">Quick Links</h5>

                    @if (isset($legalPages) && count($legalPages) > 0)
                    <ul class="list-unstyled">
                        @foreach ($legalPages as $page)
                        <li class="mb-2">
                            <a href="{{ route('customer.page', $page->slug) }}"
                                class="text-white text-decoration-none hover-link">
                                <i class="fa fa-angle-right me-2"
                                    style="font-size: 0.8rem;"></i>{{ $page->title }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p style="font-size: 0.9rem; opacity: 0.7;">No links available.</p>
                    @endif
                </div>
                {{-- Column 3: Google Map --}}
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="fw-bold text-white mb-3">Find Us</h5>
                    @if (isset($siteSettings->map_iframe) && $siteSettings->map_iframe)
                    <div class="map-container shadow-sm"
                        style="border-radius: 10px; overflow: hidden; border: 2px solid rgba(255,255,255,0.2);">
                        {!! $siteSettings->map_iframe !!}
                    </div>
                    @else
                    <p style="color: rgba(255,255,255,0.7); font-size: 0.9rem;">Map not configured yet.</p>
                    @endif
                </div>

            </div>

            <hr style="border-color: rgba(255,255,255,0.2); margin: 30px 0 20px 0;">

            {{-- Copyright --}}
            <div class="row align-items-center">
                <div class="col-12 text-center">
                    <p class="mb-1 fw-bold">© {{ date('Y') }} MyShop. All rights reserved.</p>
                    <small style="color: rgba(255,255,255,0.8);">Built with Laravel.</small>
                </div>
            </div>
        </div>
    </footer>
    <!-- HELP CHAT BUTTON -->

    <div id="chat-toggle">

        <i class="fa fa-comments"></i>

    </div>

    <!-- CHAT BOX -->

    <div id="chat-box" class="shadow">

        <div id="chat-header">

            {{-- Row 1: title --}}
            <div class="chat-title">Help Support</div>

            {{-- Row 2: profile picture + about --}}
            @auth
            <div class="chat-profile-row">

                {{-- Profile picture --}}
                @if (auth()->user()->pfp)
                <img src="{{ asset('storage/' . auth()->user()->pfp) }}"
                    alt="{{ auth()->user()->name }}"
                    style="
                             width: 38px;
                             height: 38px;
                             border-radius: 50%;
                             object-fit: cover;
                             border: 2px solid rgba(255,255,255,0.65);
                             flex-shrink: 0;
                         ">
                @else
                <div style="
                        width: 38px;
                        height: 38px;
                        border-radius: 50%;
                        background: rgba(255,255,255,0.22);
                        color: #fff;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: 700;
                        font-size: 16px;
                        flex-shrink: 0;
                        border: 2px solid rgba(255,255,255,0.4);
                    ">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                @endif

                {{-- About text — click to open edit modal --}}
                <span id="chat-about-text"
                    title="Click to update your about">
                    Loading…
                </span>

            </div>
            @endauth

        </div>

        <div id="chat-messages"></div>

        <div class="p-2 border-top">

            <!-- FILE INPUT -->

            <div class="mb-2">

                <input type="file" id="media-input" class="form-control">

            </div>

            <!-- PREVIEW -->

            <div id="media-preview" class="mb-2">
            </div>

            <!-- MESSAGE -->

            <div class="d-flex">

                <input type="text" id="chat-input" class="form-control me-2" placeholder="Type message...">

                <button id="send-message" class="btn btn-primary">

                    <i class="fa fa-paper-plane"></i>

                </button>

            </div>

        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module">
        /* FIREBASE INSTANCE — all functions from single module instance */

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



        /* ELEMENTS */

        const chatToggle =
            document.getElementById('chat-toggle');

        const chatBox =
            document.getElementById('chat-box');

        const sendBtn =
            document.getElementById('send-message');

        const chatInput =
            document.getElementById('chat-input');

        const mediaInput =
            document.getElementById('media-input');

        const mediaPreview =
            document.getElementById('media-preview');

        const messagesDiv =
            document.getElementById('chat-messages');



        /* CHAT TOGGLE */

        chatToggle.addEventListener('click', () => {

            if (chatBox.style.display === 'block') {

                chatBox.style.display = 'none';

            } else {

                chatBox.style.display = 'block';

            }

        });



        /* USER DATA */

        const userId =
            "{{ auth()->id() }}";

        const userName =
            "{{ auth()->user()->name ?? 'Guest' }}";

        const chatId =
            "user_" + userId;



        /* FILE PREVIEW */

        mediaInput.addEventListener('change', () => {

            const file = mediaInput.files[0];

            if (!file) return;

            mediaPreview.innerHTML = '';



            /* IMAGE */

            if (file.type.startsWith('image/')) {

                mediaPreview.innerHTML = `

                <button class="btn btn-sm btn-outline-primary"
                        id="open-preview">

                    <i class="fa fa-image"></i>
                    Preview Image

                </button>

            `;

            }

            /* VIDEO */
            else if (file.type.startsWith('video/')) {

                mediaPreview.innerHTML = `

                <button class="btn btn-sm btn-outline-primary"
                        id="open-preview">

                    <i class="fa fa-video"></i>
                    Preview Video

                </button>

            `;

            }

            /* OTHER FILE */
            else {

                mediaPreview.innerHTML = `

                <div class="alert alert-secondary p-2 mb-0">

                    ${file.name}

                </div>

            `;

            }



            /* OPEN PREVIEW */

            const previewBtn =
                document.getElementById('open-preview');

            if (previewBtn) {

                previewBtn.addEventListener('click', () => {

                    const modalBody =
                        document.getElementById('previewModalBody');

                    modalBody.innerHTML = '';



                    /* IMAGE */

                    if (file.type.startsWith('image/')) {

                        modalBody.innerHTML = `

                        <img src="${URL.createObjectURL(file)}"
                             class="img-fluid rounded"
                             style="
                                max-height:500px;
                                object-fit:contain;
                             ">

                    `;

                    }

                    /* VIDEO */
                    else if (file.type.startsWith('video/')) {

                        modalBody.innerHTML = `

                        <video controls
                               class="w-100 rounded"
                               style="
                                    max-height:500px;
                               ">

                            <source src="${URL.createObjectURL(file)}">

                        </video>

                    `;

                    }



                    const modal = new bootstrap.Modal(

                        document.getElementById('previewModal')

                    );

                    modal.show();

                });

            }

        });



        /* SEND MESSAGE */

        sendBtn.addEventListener('click', async () => {

            let message = chatInput.value;

            const file = mediaInput.files[0];



            /* EMPTY VALIDATION */

            if (message.trim() === '' && !file) {

                return;

            }



            /* FILE SIZE LIMIT */

            if (file) {

                if (file.size > 5 * 1024 * 1024) {

                    alert('File size must be below 5MB');

                    return;

                }

            }



            /* CREATE CHAT */

            await setDoc(

                doc(db, "support_chats", chatId),

                {
                    customer_id: userId,
                    customer_name: userName,
                    updated_at: new Date()
                },

                {
                    merge: true
                }

            );



            let mediaUrl = null;

            let mediaType = null;



            /* FILE UPLOAD */

            if (file) {

                const fileName =

                    'customer_' +
                    userId +
                    '_' +
                    Date.now() +
                    '_' +
                    file.name;



                const storageRef = ref(

                    storage,
                    'chat_media/' + fileName

                );



                await uploadBytes(storageRef, file);



                mediaUrl =
                    await getDownloadURL(storageRef);

                mediaType =
                    file.type;

            }



            /* SAVE MESSAGE */

            await addDoc(

                collection(
                    db,
                    "support_chats",
                    chatId,
                    "messages"
                ),

                {
                    sender: "customer",
                    sender_id: userId,
                    sender_name: userName,
                    message: message,
                    media_url: mediaUrl,
                    media_type: mediaType,
                    delivered: true,
                    read: false,
                    created_at: new Date()
                }

            );



            /* RESET */

            chatInput.value = '';

            mediaInput.value = '';

            mediaPreview.innerHTML = '';

        });



        /* MARK ADMIN MESSAGES AS READ */
        /*
        | When customer opens chat, mark all admin messages as read.
        | NOTE: Firestore requires a composite index on (sender, read).
        | If missing, browser console will show a link to create it.
        */

        async function markMessagesAsRead() {
            try {
                const q = query(
                    collection(db, "support_chats", chatId, "messages"),
                    where("sender", "==", "admin")
                );
                const snapshot = await getDocs(q);
                snapshot.forEach(async (docSnap) => {
                    const data = docSnap.data();
                    // Only update if read is not already true
                    if (data.read !== true) {
                        await updateDoc(docSnap.ref, {
                            read: true
                        });
                    }
                });
            } catch (e) {
                console.error('markMessagesAsRead error:', e);
            }
        }

        // Mark admin messages as read when customer opens chat
        console.log("chatId is:", chatId); // ← add temporarily
        markMessagesAsRead();



        /* REALTIME CHAT */

        const q = query(

            collection(
                db,
                "support_chats",
                chatId,
                "messages"
            ),

            orderBy("created_at")

        );



        onSnapshot(q, (snapshot) => {
            markMessagesAsRead(); // ← add this line here

            messagesDiv.innerHTML = '';



            snapshot.forEach((docSnap) => {

                const data = docSnap.data();



                /* ALIGNMENT */

                let justifyClass =

                    data.sender === 'customer' ?
                    'justify-content-end' :
                    'justify-content-start';



                let bubbleClass =

                    data.sender === 'customer' ?
                    'bg-light-green text-dark' :
                    'bg-light';



                let mediaHtml = '';



                /* IMAGE */

                if (

                    data.media_type &&
                    data.media_type.startsWith('image/')

                ) {

                    mediaHtml = `

                    <img src="${data.media_url}"

                         onclick="openCustomerImagePreview('${data.media_url}')"

                         class="img-fluid rounded mb-2 shadow-sm"

                         style="
                            max-width:200px;
                            cursor:pointer;
                            transition:0.3s;
                         "

                         onmouseover="this.style.opacity='0.85'"

                         onmouseout="this.style.opacity='1'">

                `;

                }



                /* VIDEO */
                else if (

                    data.media_type &&
                    data.media_type.startsWith('video/')

                ) {

                    mediaHtml = `

                    <video controls
                           class="rounded mb-2"
                           style="
                                max-width:220px;
                                width:100%;
                           ">

                        <source src="${data.media_url}">

                    </video>

                `;

                }



                /* OTHER FILE */
                else if (data.media_url) {

                    mediaHtml = `

                    <a href="${data.media_url}"
                       target="_blank"
                       class="btn btn-sm btn-secondary mb-2">

                        Download File

                    </a>

                `;

                }



                messagesDiv.innerHTML += `

                <div class="d-flex ${justifyClass} mb-3">

                    <div class="p-2 rounded shadow-sm ${bubbleClass}"

                         style="
                            max-width:75%;
                            word-break:break-word;
                         ">

                        ${mediaHtml}

                        ${
                            data.message
                            ? `<div>${data.message}</div>`
                            : ''
                        }

                        ${
                            data.sender === 'customer'
                            ? (data.read === true
                                ? '<div style="text-align:right;margin-top:2px;"><span style="color:#1976D2;font-size:13px;">✓✓</span></div>'
                                : '<div style="text-align:right;margin-top:2px;"><span style="color:#9E9E9E;font-size:13px;">✓</span></div>')
                            : ''
                        }

                    </div>

                </div>

            `;

            });



            /* AUTO SCROLL */

            messagesDiv.scrollTop =
                messagesDiv.scrollHeight;

        });



        /* IMAGE POPUP PREVIEW */

        window.openCustomerImagePreview = function(imageUrl) {

            const modalBody =

                document.getElementById(
                    'customerPreviewModalBody'
                );



            modalBody.innerHTML = `

            <img src="${imageUrl}"

                 class="img-fluid rounded"

                 style="
                    max-height:500px;
                    object-fit:contain;
                 ">

        `;



            const modal = new bootstrap.Modal(

                document.getElementById(
                    'customerPreviewModal'
                )

            );



            modal.show();

        }


        /*
        |----------------------------------------------------------------------
        | ABOUT — load from Firestore and display in chat header
        |----------------------------------------------------------------------
        */

        // userId is already declared above — reuse it
        const aboutTextEl = document.getElementById('chat-about-text');
        const aboutModalEl = document.getElementById('aboutEditModal');
        const aboutTextarea = document.getElementById('about-modal-input');
        const aboutSaveBtn = document.getElementById('about-modal-save');
        const aboutSaveStatus = document.getElementById('about-modal-status');

        let currentAbout = '';

        /* Character counter for modal textarea */
        if (aboutTextarea) {
            aboutTextarea.addEventListener('input', () => {
                const charEl = document.getElementById('about-modal-chars');
                if (charEl) charEl.textContent = aboutTextarea.value.length + ' / 500';
            });
        }

        /* Load about once */
        async function loadAbout() {
            try {
                const snap = await getDoc(doc(db, 'users_profile', userId));
                if (snap.exists() && snap.data().about) {
                    currentAbout = snap.data().about;
                } else {
                    currentAbout = '';
                }
            } catch (e) {
                currentAbout = '';
            }
            aboutTextEl.textContent = currentAbout || 'Add about…';
        }

        loadAbout();

        /* Click on about text → open modal */
        if (aboutTextEl) {
            aboutTextEl.addEventListener('click', () => {
                aboutTextarea.value = currentAbout;
                const charEl = document.getElementById('about-modal-chars');
                if (charEl) charEl.textContent = currentAbout.length + ' / 500';
                aboutSaveStatus.textContent = '';
                const modal = new bootstrap.Modal(aboutModalEl);
                modal.show();
            });
        }

        /* Save about */
        if (aboutSaveBtn) {
            aboutSaveBtn.addEventListener('click', async () => {
                const text = aboutTextarea.value.trim();
                aboutSaveBtn.disabled = true;
                aboutSaveBtn.textContent = 'Saving…';
                aboutSaveStatus.textContent = '';

                try {
                    await setDoc(
                        doc(db, 'users_profile', userId), {
                            about: text
                        }, {
                            merge: true
                        }
                    );
                    currentAbout = text;
                    aboutTextEl.textContent = text || 'Add about…';
                    aboutSaveStatus.textContent = '✓ Saved';
                    aboutSaveStatus.className = 'text-success small';

                    /* Auto-close after short delay */
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(aboutModalEl)?.hide();
                    }, 800);

                } catch (e) {
                    console.error(e);
                    aboutSaveStatus.textContent = 'Failed. Try again.';
                    aboutSaveStatus.className = 'text-danger small';
                } finally {
                    aboutSaveBtn.disabled = false;
                    aboutSaveBtn.textContent = 'Save';
                }
            });
        }
    </script>
    <!-- MEDIA PREVIEW MODAL -->

    <div class="modal fade" id="previewModal" tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">

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

    <!-- CUSTOMER IMAGE PREVIEW MODAL -->

    <!-- CUSTOMER IMAGE PREVIEW MODAL -->

    <div class="modal fade" id="customerPreviewModal" tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">
                        Media Preview
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body text-center" id="customerPreviewModalBody">

                </div>

            </div>

        </div>

    </div>

    <!-- ABOUT EDIT MODAL -->

    <div class="modal fade" id="aboutEditModal" tabindex="-1" aria-labelledby="aboutEditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="aboutEditModalLabel">Update About</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body pt-2">
                    <p class="text-muted small mb-3">
                        Write a short bio. This is visible to support staff in the chat panel.
                    </p>
                    <textarea
                        id="about-modal-input"
                        class="form-control rounded-3"
                        rows="4"
                        maxlength="500"
                        placeholder="Tell us a little about yourself…"></textarea>
                    <div class="d-flex justify-content-between mt-1">
                        <small id="about-modal-status"></small>
                        <small class="text-muted" id="about-modal-chars">0 / 500</small>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" id="about-modal-save" class="btn btn-primary rounded-pill px-4">
                        Save
                    </button>
                </div>

            </div>
        </div>
    </div>

</body>

</html>