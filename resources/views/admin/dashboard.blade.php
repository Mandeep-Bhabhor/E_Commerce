<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Admin Dashboard
        </h2>
    </x-slot>

    <div class="py-8 px-6 max-w-6xl mx-auto">

        {{-- Admin Info --}}
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6 border">
            <h3 class="text-lg font-semibold mb-2">Welcome</h3>
            <p class="text-gray-700">{{ Auth::user()->name }}</p>
            <p class="text-gray-500 text-sm">{{ Auth::user()->email }}</p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

            <div class="bg-white border rounded-lg p-5 shadow-sm">
                <p class="text-sm text-gray-500">Total Orders</p>
                <p class="text-2xl font-bold text-gray-800">0</p>
            </div>

            <div class="bg-white border rounded-lg p-5 shadow-sm">
                <p class="text-sm text-gray-500">Users</p>
                <p class="text-2xl font-bold text-gray-800">0</p>
            </div>

            <div class="bg-white border rounded-lg p-5 shadow-sm">
                <p class="text-sm text-gray-500">Revenue</p>
                <p class="text-2xl font-bold text-gray-800">₹0</p>
            </div>

        </div>

        {{-- Quick Actions --}}
        <div class="bg-white border rounded-lg p-6 shadow-sm">
            <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                <a href="#" class="border rounded-md p-4 text-center hover:bg-gray-50">
                    Manage Products
                </a>

                <a href="#" class="border rounded-md p-4 text-center hover:bg-gray-50">
                    View Orders
                </a>

                <a href="#" class="border rounded-md p-4 text-center hover:bg-gray-50">
                    Users
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="border rounded-md p-4 w-full hover:bg-red-50 text-red-600">
                        Logout
                    </button>
                </form>

            </div>
        </div>

    </div>
</x-app-layout>