<nav x-data="{ open: false }" class="bg-dark border-b border-gray-200">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <div class="flex">

                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('customer.dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden sm:flex space-x-8 sm:ms-10">

                    @if (auth()->user()->role === 'admin')
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                            Dashboard
                        </x-nav-link>

                        <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                            Products
                        </x-nav-link>

                        <x-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')">
                            Categories
                        </x-nav-link>

                        <x-nav-link :href="route('colors.index')" :active="request()->routeIs('colors.*')">
                            Colors
                        </x-nav-link>

                        <x-nav-link :href="route('sizes.index')" :active="request()->routeIs('sizes.*')">
                            Sizes
                        </x-nav-link>

                        <x-nav-link :href="route('taxes.index')" :active="request()->routeIs('taxes.*')">
                            Tax
                        </x-nav-link>

                        <x-nav-link :href="route('discounts.index')" :active="request()->routeIs('discounts.*')">
                            Discount
                        </x-nav-link>

                        <x-nav-link :href="route('admin.orders.index')">
                            Orders
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('customer.dashboard')" :active="request()->routeIs('customer.dashboard')">
                            Dashboard
                        </x-nav-link>

                        <x-nav-link :href="route('products.list')" :active="request()->routeIs('products.list')">
                            Products
                        </x-nav-link>

                        <x-nav-link :href="route('cart.index')" :active="request()->routeIs('cart.*')">
                            Cart
                        </x-nav-link>

                        <x-nav-link :href="route('wishlist.index')" :active="request()->routeIs('wishlist.*')">
                            Wishlist
                        </x-nav-link>

                        <x-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                            My Orders
                        </x-nav-link>
                    @endif

                </div>
            </div>

            <!-- User Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">

                <x-dropdown align="right" width="48">

                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm text-gray-600">
                            {{ Auth::user()->name }}
                        </button>
                    </x-slot>

                    <x-slot name="content">

                            <x-dropdown-link :href="route('profile.edit')">
                                Profile
                            </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();this.closest('form').submit();">
                                Log Out
                            </x-dropdown-link>
                        </form>

                    </x-slot>

                </x-dropdown>

            </div>

            <!-- Mobile Menu Button -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="p-2">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    <!-- Mobile Navigation -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">

        <div class="pt-2 pb-3 space-y-1">

            @if (auth()->user()->role === 'admin')
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                    Dashboard
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('products.index')">
                    Products
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('categories.index')">
                    Categories
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('colors.index')">
                    Colors
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('sizes.index')">
                    Sizes
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('taxes.index')">
                    Tax
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('discounts.index')">
                    Discount
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('customer.dashboard')">
                    Dashboard
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('products.list')">
                    Products
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('cart.index')">
                    Cart
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('wishlist.index')">
                    Wishlist
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('orders.index')">
                    My Orders
                </x-responsive-nav-link>
            @endif

        </div>

    </div>

</nav>
