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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
