<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Panel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-slate-950 text-white">

    <div x-data="{ sidebarOpen: false }"
        class="flex h-screen overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950">

        <!-- Mobile Overlay -->
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-20 bg-black/50 lg:hidden"
            @click="sidebarOpen = false" style="display: none;">
        </div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-30 w-72 transform bg-slate-900/95 backdrop-blur-md border-r border-slate-800 transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-0 flex flex-col shadow-2xl">

            <!-- Logo -->
            <div class="flex items-center justify-center h-20 border-b border-slate-800 px-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center shadow-lg">
                        <i class="fa fa-store text-white text-lg"></i>
                    </div>

                    <div>
                        <h1 class="text-xl font-bold tracking-wide text-white">
                            {{ Auth::user()->role === 'admin' ? 'Admin Panel' : 'Customer Panel' }}
                        </h1>
                        <p class="text-xs text-slate-400">
                            Welcome back
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2">

                @if (Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-indigo-600 hover:text-white transition-all duration-200">
                        <i class="fa fa-tachometer-alt w-5"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-indigo-600 hover:text-white transition-all duration-200">
                        <i class="fa fa-shopping-bag w-5"></i>
                        <span>Orders</span>
                    </a>

                    <a href="{{ route('products.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-indigo-600 hover:text-white transition-all duration-200">
                        <i class="fa fa-box-open w-5"></i>
                        <span>Products</span>
                    </a>
                @else
                    <a href="{{ route('customer.dashboard') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-indigo-600 hover:text-white transition-all duration-200">
                        <i class="fa fa-home w-5"></i>
                        <span>Home</span>
                    </a>

                    <a href="{{ route('orders.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-indigo-600 hover:text-white transition-all duration-200">
                        <i class="fa fa-shopping-cart w-5"></i>
                        <span>My Orders</span>
                    </a>

                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-indigo-600 hover:text-white transition-all duration-200">
                        <i class="fa fa-user w-5"></i>
                        <span>Profile</span>
                    </a>
                @endif

            </nav>
        </aside>

        <!-- Main -->
        <div class="flex flex-col flex-1 overflow-hidden lg:ml-0">

            <!-- Header -->
            <header class="sticky top-0 z-10 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800 shadow-sm">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = true" class="lg:hidden text-slate-300 hover:text-white">
                            <i class="fa fa-bars text-xl"></i>
                        </button>

                        @isset($header)
                            <h2 class="text-2xl font-bold text-white">
                                {{ $header }}
                            </h2>
                        @endisset
                    </div>

                    <div class="flex items-center gap-5">
                        <div class="hidden md:flex flex-col text-right">
                            <span class="text-sm text-slate-400">Logged in as</span>
                            <span class="font-semibold text-white">
                                {{ Auth::user()->name }}
                            </span>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white transition-all">
                                <i class="fa fa-sign-out-alt me-2"></i>Logout
                            </button>
                        </form>
                    </div>

                </div>
            </header>

            <!-- Main Content -->
            <main
                class="flex-1 overflow-y-auto px-4 sm:px-6 lg:px-10 py-8 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950">
                <div class="max-w-5xl mx-auto">
                    {{ $slot }}
                </div>
            </main>

        </div>

    </div>

</body>

</html>
