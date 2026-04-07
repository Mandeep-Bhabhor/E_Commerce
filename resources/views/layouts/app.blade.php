<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Admin Panel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
        
        <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">
            
            <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-20 bg-black bg-opacity-50 lg:hidden" @click="sidebarOpen = false" style="display: none;"></div>

            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-30 w-64 overflow-y-auto transition-transform duration-300 bg-gray-900 text-white lg:translate-x-0 lg:static lg:inset-0 shadow-xl flex flex-col">
                
                <div class="flex items-center justify-center h-16 bg-gray-950 border-b border-gray-800 shrink-0">
                    <div class="flex items-center gap-2">
                        <i class="fa fa-store text-indigo-500 fa-lg"></i>
                        <span class="text-xl font-bold uppercase tracking-wider">Admin Panel</span>
                    </div>
                </div>

                <nav class="flex-1 px-4 py-6 space-y-2">
                    <a href="#" class="flex items-center px-4 py-3 bg-indigo-600 text-white rounded-lg shadow-md">
                        <i class="fa fa-tachometer-alt w-6 text-center"></i>
                        <span class="mx-2 font-medium">Dashboard</span>
                    </a>
                    
                    <a href="#" class="flex items-center px-4 py-3 text-gray-400 hover:bg-gray-800 hover:text-white rounded-lg transition-colors">
                        <i class="fa fa-shopping-bag w-6 text-center"></i>
                        <span class="mx-2 font-medium">Orders</span>
                    </a>

                    <a href="#" class="flex items-center px-4 py-3 text-gray-400 hover:bg-gray-800 hover:text-white rounded-lg transition-colors">
                        <i class="fa fa-box-open w-6 text-center"></i>
                        <span class="mx-2 font-medium">Products</span>
                    </a>

                    <a href="#" class="flex items-center px-4 py-3 text-gray-400 hover:bg-gray-800 hover:text-white rounded-lg transition-colors">
                        <i class="fa fa-users w-6 text-center"></i>
                        <span class="mx-2 font-medium">Customers</span>
                    </a>

                    <a href="#" class="flex items-center px-4 py-3 text-gray-400 hover:bg-gray-800 hover:text-white rounded-lg transition-colors">
                        <i class="fa fa-store-alt w-6 text-center"></i>
                        <span class="mx-2 font-medium">Vendors</span>
                    </a>
                </nav>
            </aside>

            <div class="flex flex-col flex-1 overflow-hidden">
                
                <header class="flex items-center justify-between px-6 py-4 bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 shrink-0">
                    <div class="flex items-center gap-4">
                        
                        <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700 focus:outline-none lg:hidden">
                            <i class="fa fa-bars fa-lg"></i>
                        </button>
                        
                        @isset($header)
                            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight m-0">
                                {{ $header }}
                            </h2>
                        @endisset
                    </div>

                    <div class="flex items-center gap-4">
                        <span class="text-gray-700 dark:text-gray-300 font-medium hidden sm:block">
                            {{ Auth::user()->name ?? 'Administrator' }}
                        </span>
                        
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="text-sm font-semibold text-red-500 hover:text-red-700 transition">
                                <i class="fa fa-sign-out-alt me-1"></i> Logout
                            </button>
                        </form>
                    </div>
                </header>

                <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 dark:bg-gray-900 p-6">
                    {{ $slot }}
                </main>
                
            </div>
        </div>
    </body>
</html>