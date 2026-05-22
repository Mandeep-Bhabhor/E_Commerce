<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Admin Panel') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f8f9fa;
        }

        /* --- Select2 Bootstrap 5 Fixes --- */
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--multiple {
            min-height: 38px !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 0.375rem !important;
            padding: 2px 4px !important;
        }

        /* The blue tags */
        .select2-container--default .select2-selection__choice {
            background-color: #0d6efd !important;
            color: #fff !important;
            border: 1px solid #0a58ca !important;
            border-radius: 4px !important;
            padding: 2px 8px 2px 24px !important;
            /* Space on left for the X */
            position: relative;
            margin-top: 4px !important;
            margin-left: 4px !important;
        }

        /* The close 'X' inside the tag */
        .select2-container--default .select2-selection__choice__remove {
            color: #fff !important;
            position: absolute !important;
            left: 6px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            border: none !important;
            padding: 0 !important;
            background: transparent !important;
        }

        .select2-container--default .select2-selection__choice__remove:hover {
            color: #e9ecef !important;
            background: transparent !important;
        }

        /* The 'Clear All' X on the right side */
        .select2-container--default .select2-selection__clear {
            margin-top: 8px !important;
            margin-right: 10px !important;
        }

        .select2-dropdown {
            z-index: 9999 !important;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold text-primary" href="{{ route('admin.dashboard') }}">
                <i class="fa fa-store me-2"></i>Admin Panel
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a
                            class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active text-primary' : '' }}"
                            href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a
                            class="nav-link {{ request()->routeIs('products*') ? 'active text-primary' : '' }}"
                            href="{{ route('products.index') }}">Products</a></li>
                    <li class="nav-item"><a
                            class="nav-link {{ request()->routeIs('admin.orders*') ? 'active text-primary' : '' }}"
                            href="{{ route('admin.orders.index') }}">Orders</a></li>
                    <li class="nav-item"><a
                            class="nav-link {{ request()->routeIs('sizes*') ? 'active text-primary' : '' }}"
                            href="{{ route('sizes.index') }}">Sizes</a></li>
                    <li class="nav-item"><a
                            class="nav-link {{ request()->routeIs('categories*') ? 'active text-primary' : '' }}"
                            href="{{ route('categories.index') }}">Categories</a></li>
                    <li class="nav-item"><a
                            class="nav-link {{ request()->routeIs('colors*') ? 'active text-primary' : '' }}"
                            href="{{ route('colors.index') }}">Colors</a></li>
                    <li class="nav-item"><a
                            class="nav-link {{ request()->routeIs('taxes*') ? 'active text-primary' : '' }}"
                            href="{{ route('taxes.index') }}">Tax</a></li>
                    <li class="nav-item"><a
                            class="nav-link {{ request()->routeIs('discounts*') ? 'active text-primary' : '' }}"
                            href="{{ route('discounts.index') }}">Discount</a></li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.approve.customers') ? 'active text-primary' : '' }}"
                            href="{{ route('admin.approve.customers') }}">
                            Approve Customers
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('inquiry.index') ? 'active text-primary' : '' }}"
                            href="{{ route('inquiry.index') }}">
                            Inquiries
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="container-fluid px-4 py-4">
        @yield('content')
    </main>

    <footer class="bg-white border-top text-center py-3 text-muted small mt-5">
        © {{ date('Y') }} Admin Panel • Built with Laravel
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
