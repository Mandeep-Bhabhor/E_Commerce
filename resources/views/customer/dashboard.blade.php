<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-6">

        <!-- Welcome -->
        <div class="bg-white border rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800">
                Welcome, {{ Auth::user()->name }}
            </h3>
            <p class="text-sm text-gray-500">
                {{ Auth::user()->email }}
            </p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

            <div class="bg-white border rounded-lg p-5">
                <p class="text-sm text-gray-500">Orders</p>
                <p class="text-2xl font-bold text-gray-800">0</p>
            </div>

            <div class="bg-white border rounded-lg p-5">
                <p class="text-sm text-gray-500">Wishlist</p>
                <p class="text-2xl font-bold text-gray-800">0</p>
            </div>

            <div class="bg-white border rounded-lg p-5">
                <p class="text-sm text-gray-500">Total Spent</p>
                <p class="text-2xl font-bold text-gray-800">₹0</p>
            </div>

        </div>

        <!-- Quick Actions -->
        <div class="bg-white border rounded-lg p-6">

            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                Quick Actions
            </h3>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                <a href="{{ route('products.index') }}"
                   class="border rounded-md p-4 text-center hover:bg-gray-50">
                    Products
                </a>

                <a href="{{ route('categories.index') }}"
                   class="border rounded-md p-4 text-center hover:bg-gray-50">
                    Categories
                </a>

                <a href="{{ route('sizes.index') }}"
                   class="border rounded-md p-4 text-center hover:bg-gray-50">
                    Sizes
                </a>

                <a href="{{ route('colors.index') }}"
                   class="border rounded-md p-4 text-center hover:bg-gray-50">
                    Colors
                </a>

            </div>

        </div>

    </div>
</x-app-layout>