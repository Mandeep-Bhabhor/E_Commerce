<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script src="//unpkg.com/alpinejs" defer></script>
   <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            background: #f8f9fa;
        }

        .navbar-brand {
            font-weight: 600;
        }

        .product-card:hover {
            transform: translateY(-3px);
            transition: .2s ease;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .08);
        }

        footer {
            background: #111;
            color: #ccc;
            padding: 30px 0;
            margin-top: 60px;
        }

        .card-img-fixed {
            height: 220px;
            object-fit: cover;
            width: 100%;
        }
    </style>
</head>

<body>

    {{-- NAVBAR --}}
    <nav class="navbar navbar-expand-lg bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="/">MyShop</a>

            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="nav">

                {{-- LEFT --}}
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('products.list') }}">Products</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('cart.index') }}">Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('wishlist.index') }}">Wishlist</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('orders.index') }}">Orders</a>
                    </li>

                </ul>

                {{-- SEARCH --}}
                {{-- <form class="d-flex me-3" action="/search" method="GET">
                    <input class="form-control me-2" type="search" name="q" placeholder="Search products...">
                    <button class="btn btn-outline-dark">Search</button>
                </form> --}}

                {{-- RIGHT --}}
                <ul class="navbar-nav">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="/profile">
                                <i class="fa fa-user"></i> {{ auth()->user()->name }}
                            </a>
                        </li>

                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="btn btn-sm btn-dark ms-2">Logout</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="btn btn-dark btn-sm me-2" href="{{ route('login') }}">Login</a>
                        </li>

                        <li class="nav-item">
                            <a class="btn btn-outline-dark btn-sm" href="{{ route('register') }}">Register</a>
                        </li>
                    @endauth
                </ul>

            </div>
        </div>
    </nav>

    {{-- MAIN CONTENT --}}
    <div class="container mt-4">
        @yield('content')
    </div>

    {{-- FOOTER --}}
    <footer>
        <div class="container text-center">
            <p class="mb-1">© {{ date('Y') }} MyShop</p>
            <small>Built with Laravel. Survived by caffeine.</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
